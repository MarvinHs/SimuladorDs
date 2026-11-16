<?php
// config/api_config.php
// Configuración central. NUNCA escribas claves en este archivo: usa variables
// de entorno o el archivo .env (que está en .gitignore).

/**
 * Carga un archivo .env sencillo (CLAVE=valor) si existe.
 * Útil en XAMPP/WAMP, donde no hay Docker que inyecte las variables.
 */
function app_load_env(string $path): void
{
    if (!is_readable($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

app_load_env(__DIR__ . '/../.env');

function app_env(string $key, string $default = ''): string
{
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
}

// Las claves de IA (GEMINI_API_KEY, GROQ_API_KEY, ANTHROPIC_API_KEY,
// OPENAI_API_KEY) y AI_PROVIDER se leen en src/AiConsulService.php.

// Ruta opcional a un bundle de certificados (p. ej. XAMPP sin cacert.pem).
define('CURL_CA_BUNDLE', app_env('CURL_CA_BUNDLE'));

// Límites de la entrevista
define('MAX_USER_TURNS', 10);      // a partir de aquí se fuerza la decisión
define('MAX_MESSAGE_LENGTH', 1000); // caracteres por respuesta del solicitante
define('DECISION_MARKER', '[DECISIÓN FINAL]');

// Sesión con cookies más seguras
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Respuesta JSON y fin de la ejecución. */
function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Solo acepta POST con cuerpo JSON; devuelve el cuerpo decodificado. */
function require_json_post(): array
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        json_response(['error' => 'Método no permitido.'], 405);
    }
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        json_response(['error' => 'Solicitud inválida.'], 400);
    }
    return $input;
}
