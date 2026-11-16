<?php
// src/AiConsulService.php
// Cliente de IA con varios proveedores y respaldo automático.
//
// Proveedores soportados:
//   gemini    → Google Gemini (recomendado: gratis, muy buen español, rápido)
//   groq      → Groq (gratis, el más rápido)
//   anthropic → Claude (de pago, la mejor calidad conversacional)
//   openai    → OpenAI (de pago)
//
// Si el proveedor principal falla (caído, sin cuota, clave inválida), se
// prueba automáticamente el siguiente que tenga clave configurada.

namespace App;

class AiConsulService
{
    /** Configuración por defecto de cada proveedor. */
    public const PROVIDERS = [
        'gemini' => [
            'name'      => 'Google Gemini',
            'key_env'   => 'GEMINI_API_KEY',
            'model_env' => 'GEMINI_MODEL',
            // Lista separada por comas: si un modelo está saturado se usa el siguiente
            'model'     => 'gemini-3.5-flash,gemini-flash-latest,gemini-3.5-flash-lite',
            'url'       => 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions',
            'format'    => 'openai',
        ],
        'groq' => [
            'name'      => 'Groq',
            'key_env'   => 'GROQ_API_KEY',
            'model_env' => 'GROQ_MODEL',
            'model'     => 'openai/gpt-oss-120b',
            'url'       => 'https://api.groq.com/openai/v1/chat/completions',
            'format'    => 'openai',
        ],
        'anthropic' => [
            'name'      => 'Claude (Anthropic)',
            'key_env'   => 'ANTHROPIC_API_KEY',
            'model_env' => 'ANTHROPIC_MODEL',
            'model'     => 'claude-haiku-5-5',
            'url'       => 'https://api.anthropic.com/v1/messages',
            'format'    => 'anthropic',
        ],
        'openai' => [
            'name'      => 'OpenAI',
            'key_env'   => 'OPENAI_API_KEY',
            'model_env' => 'OPENAI_MODEL',
            'model'     => 'gpt-5-mini',
            'url'       => 'https://api.openai.com/v1/chat/completions',
            'format'    => 'openai',
        ],
    ];

    private string $caBundle;
    /** @var array<int, array> */
    private array $chain;

    public function __construct(string $caBundle = '')
    {
        $this->caBundle = $caBundle;
        $this->chain = self::configuredChain();
    }

    /**
     * Proveedores con clave configurada, empezando por AI_PROVIDER (si se definió).
     * @return array<int, array>
     */
    public static function configuredChain(): array
    {
        $preferred = strtolower(app_env('AI_PROVIDER'));
        $order = array_keys(self::PROVIDERS);
        if ($preferred !== '' && isset(self::PROVIDERS[$preferred])) {
            $order = array_merge([$preferred], array_diff($order, [$preferred]));
        }

        $chain = [];
        foreach ($order as $id) {
            $cfg = self::PROVIDERS[$id];
            $key = app_env($cfg['key_env']);
            if ($key === '') {
                continue;
            }
            $cfg['id'] = $id;
            $cfg['key'] = $key;
            // Permite apuntar a otra URL compatible (proxy, servidor de pruebas…)
            $cfg['url'] = app_env(strtoupper($id) . '_API_URL', $cfg['url']);
            // Cada modelo de la lista es un eslabón más de la cadena de respaldo
            $models = array_filter(array_map('trim', explode(',', app_env($cfg['model_env'], $cfg['model']))));
            foreach ($models as $model) {
                $cfg['model'] = $model;
                $cfg['slot'] = $id . '_' . preg_replace('/[^a-z0-9.-]+/i', '_', $model);
                $chain[] = $cfg;
            }
        }
        return $chain;
    }

    /** @return array{reply?: string, error?: string, provider?: string} */
    public function sendChat(array $messages): array
    {
        if (!$this->chain) {
            return ['error' => 'No hay ninguna API de IA configurada. Define GEMINI_API_KEY, GROQ_API_KEY, ANTHROPIC_API_KEY u OPENAI_API_KEY en el archivo .env.'];
        }

        // Los proveedores que fallaron hace poco pasan al final de la fila,
        // para no hacer esperar al usuario en cada mensaje.
        $healthy = array_values(array_filter($this->chain, fn ($p) => !self::inCooldown($p['slot'])));
        $cooling = array_values(array_filter($this->chain, fn ($p) => self::inCooldown($p['slot'])));
        $chain = array_merge($healthy, $cooling);

        $lastError = 'Error desconocido del servicio de IA.';
        $total = count($chain);
        foreach ($chain as $i => $provider) {
            // Solo se reintenta el mismo modelo si es la última opción;
            // si hay alternativas, es más rápido pasar a la siguiente.
            $result = $this->callWithRetry($provider, $messages, $i === $total - 1);
            if (isset($result['reply'])) {
                self::setCooldown($provider['slot'], 0);
                return $result + ['provider' => $provider['name'] . ' (' . $provider['model'] . ')'];
            }
            $lastError = $result['error'];
            self::setCooldown($provider['slot'], self::COOLDOWN_SECONDS);
            error_log("[IA] {$provider['name']} / {$provider['model']} falló: {$lastError}");
        }
        return ['error' => $lastError];
    }

    private const COOLDOWN_SECONDS = 120;

    private static function cooldownFile(string $id): string
    {
        return sys_get_temp_dir() . '/simuladords_cooldown_' . $id;
    }

    private static function inCooldown(string $id): bool
    {
        $file = self::cooldownFile($id);
        return is_file($file) && (int) @file_get_contents($file) > time();
    }

    private static function setCooldown(string $id, int $seconds): void
    {
        $file = self::cooldownFile($id);
        if ($seconds <= 0) {
            if (is_file($file)) {
                @unlink($file);
            }
            return;
        }
        @file_put_contents($file, (string) (time() + $seconds), LOCK_EX);
    }

    private function callWithRetry(array $provider, array $messages, bool $allowRetry = true): array
    {
        $maxAttempts = $allowRetry ? 2 : 1;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            [$status, $body, $curlError] = $provider['format'] === 'anthropic'
                ? $this->post($provider['url'], $this->anthropicPayload($provider, $messages), [
                    'x-api-key: ' . $provider['key'],
                    'anthropic-version: 2023-06-01',
                ])
                : $this->post($provider['url'], $this->openAiPayload($provider, $messages), [
                    'Authorization: Bearer ' . $provider['key'],
                ]);

            if ($body === false) {
                if ($attempt === $maxAttempts) {
                    return ['error' => "No se pudo conectar con {$provider['name']}: {$curlError}"];
                }
                continue;
            }

            $decoded = json_decode($body, true);

            if ($status === 200) {
                $content = $provider['format'] === 'anthropic'
                    ? $this->anthropicText($decoded)
                    : trim((string) ($decoded['choices'][0]['message']['content'] ?? ''));
                return $content !== ''
                    ? ['reply' => $content]
                    : ['error' => "{$provider['name']} devolvió una respuesta vacía."];
            }

            // Reintento único ante saturación o errores temporales
            if (($status === 429 || $status === 529 || $status >= 500) && $attempt < $maxAttempts) {
                sleep(2);
                continue;
            }

            error_log("[IA] {$provider['name']} HTTP {$status}: " . substr((string) $body, 0, 500));
            if ($status === 401 || $status === 403) {
                return ['error' => "La API key de {$provider['name']} no es válida o fue revocada."];
            }
            if ($status === 429) {
                return ['error' => "{$provider['name']} está recibiendo demasiadas solicitudes. Espera unos segundos e intenta de nuevo."];
            }
            $msg = $decoded['error']['message'] ?? ($decoded[0]['error']['message'] ?? null);
            return ['error' => $msg ?: "Error de {$provider['name']} (código {$status})."];
        }
        return ['error' => "Error desconocido de {$provider['name']}."];
    }

    private function openAiPayload(array $provider, array $messages): array
    {
        $payload = [
            'model'    => $provider['model'],
            'messages' => $messages,
        ];
        $model = $provider['model'];

        if ($provider['id'] === 'gemini') {
            // Los Gemini recientes ya no aceptan temperature/top_p.
            // Razonamiento mínimo: respuestas más rápidas para la conversación.
            $payload['reasoning_effort'] = app_env('GEMINI_REASONING', 'minimal');
            $payload['max_tokens'] = 2000;
        } elseif ($provider['id'] === 'openai') {
            $payload['max_completion_tokens'] = 2000;
            if (preg_match('/^(gpt-5|o\d)/', $model)) {
                $payload['reasoning_effort'] = 'low';
            } else {
                $payload['temperature'] = 0.6;
            }
        } else { // groq
            $payload['max_completion_tokens'] = 1500;
            if (strpos($model, 'gpt-oss') !== false) {
                $payload['reasoning_effort'] = 'low';
            }
            $payload['temperature'] = 0.6;
        }
        return $payload;
    }

    /** Convierte el formato OpenAI (roles system/user/assistant) al de Claude. */
    private function anthropicPayload(array $provider, array $messages): array
    {
        $system = [];
        $converted = [];
        foreach ($messages as $m) {
            if ($m['role'] === 'system') {
                $system[] = $m['content'];
                continue;
            }
            $last = count($converted) - 1;
            if ($last >= 0 && $converted[$last]['role'] === $m['role']) {
                // Claude prefiere turnos alternos: se unen los consecutivos
                $converted[$last]['content'] .= "\n\n" . $m['content'];
            } else {
                $converted[] = ['role' => $m['role'], 'content' => $m['content']];
            }
        }
        if (!$converted || $converted[0]['role'] !== 'user') {
            array_unshift($converted, ['role' => 'user', 'content' => '(Inicio de la entrevista)']);
        }
        return [
            'model'      => $provider['model'],
            'system'     => implode("\n\n", $system),
            'messages'   => $converted,
            'max_tokens' => 1500,
        ];
    }

    private function anthropicText(?array $decoded): string
    {
        $text = '';
        foreach ($decoded['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text .= $block['text'];
            }
        }
        return trim($text);
    }

    /** @return array{0:int,1:string|false,2:string} */
    private function post(string $url, array $payload, array $headers): array
    {
        $ch = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => array_merge(['Content-Type: application/json'], $headers),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
            // Verificación SSL ACTIVADA. Si en XAMPP falla, define
            // CURL_CA_BUNDLE en .env apuntando a un cacert.pem.
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        if ($this->caBundle !== '') {
            $options[CURLOPT_CAINFO] = $this->caBundle;
        }
        curl_setopt_array($ch, $options);

        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [$status, $body, $error];
    }
}
