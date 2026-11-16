<?php
// src/AiConsulService.php
// Cliente mínimo para la API de Groq (compatible con OpenAI).

namespace App;

class AiConsulService
{
    private string $apiKey;
    private string $model;
    private string $apiUrl;
    private string $caBundle;

    public function __construct(string $apiKey, string $model, string $apiUrl, string $caBundle = '')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->apiUrl = $apiUrl;
        $this->caBundle = $caBundle;
    }

    /** @return array{reply?: string, error?: string} */
    public function sendChat(array $messages): array
    {
        if ($this->apiKey === '') {
            return ['error' => 'Falta configurar GROQ_API_KEY en el servidor (archivo .env).'];
        }

        $payload = [
            'model'                 => $this->model,
            'messages'              => $messages,
            'temperature'           => 0.6,
            'max_completion_tokens' => 1500,
        ];
        // Los modelos gpt-oss razonan antes de responder; con esfuerzo bajo
        // responden más rápido, algo importante para una conversación por voz.
        if (strpos($this->model, 'gpt-oss') !== false) {
            $payload['reasoning_effort'] = 'low';
        }

        // Un reintento ante límites de uso (429) o errores temporales (5xx).
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            [$status, $body, $curlError] = $this->post($payload);

            if ($body === false) {
                if ($attempt === 2) {
                    return ['error' => 'No se pudo conectar con el servicio de IA: ' . $curlError];
                }
                continue;
            }

            $decoded = json_decode($body, true);

            if ($status === 200) {
                $content = trim((string) ($decoded['choices'][0]['message']['content'] ?? ''));
                if ($content !== '') {
                    return ['reply' => $content];
                }
                return ['error' => 'La IA devolvió una respuesta vacía. Intenta de nuevo.'];
            }

            if (($status === 429 || $status >= 500) && $attempt === 1) {
                sleep(2);
                continue;
            }

            error_log("Groq API HTTP {$status}: " . substr($body, 0, 500));
            if ($status === 401) {
                return ['error' => 'La API key de Groq no es válida o fue revocada.'];
            }
            if ($status === 429) {
                return ['error' => 'Demasiadas solicitudes al servicio de IA. Espera unos segundos e intenta de nuevo.'];
            }
            return ['error' => $decoded['error']['message'] ?? "Error del servicio de IA (código {$status})."];
        }

        return ['error' => 'Error desconocido del servicio de IA.'];
    }

    /** @return array{0:int,1:string|false,2:string} */
    private function post(array $payload): array
    {
        $ch = curl_init($this->apiUrl);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 45,
            // La verificación SSL queda ACTIVADA. Si en XAMPP falla, define
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
