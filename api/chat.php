<?php
// api/chat.php
// Evita que warnings/notices de PHP se mezclen con el JSON de respuesta
ini_set('display_errors', '0');
set_time_limit(60);

if (!function_exists('curl_init')) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'La extensión cURL de PHP no está habilitada en este servidor.']);
    exit;
}

require_once __DIR__ . '/../config/api_config.php';
require_once __DIR__ . '/../src/AiConsulService.php'; // Asegúrate de incluir la clase

use App\AiConsulService;

header('Content-Type: application/json');

if (!isset($_SESSION['chat_history'])) {
    echo json_encode(['error' => 'Sesión no iniciada. Por favor configure sus datos primero.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMsg = trim($input['message'] ?? '');

// Si hay un mensaje del usuario, agregarlo al historial
if (!empty($userMsg)) {
    $_SESSION['chat_history'][] = ['role' => 'user', 'content' => $userMsg];
}

// Llamar al servicio de la IA
$aiService = new AiConsulService(OPENAI_API_KEY, OPENAI_MODEL);
$result = $aiService->sendChat($_SESSION['chat_history']);

// Si la respuesta fue exitosa, guardarla en el historial de la sesión
if (isset($result['reply'])) {
    $_SESSION['chat_history'][] = ['role' => 'assistant', 'content' => $result['reply']];
}

echo json_encode($result);