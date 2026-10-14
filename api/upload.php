<?php
// api/upload.php
require_once __DIR__ . '/../config/api_config.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $ds160Content = trim($input['ds160_text'] ?? '');

    if (empty($ds160Content)) {
        throw new Exception('No se recibieron los datos del formulario.');
    }

    $systemPrompt = "Eres un Oficial Consular de los Estados Unidos en la Embajada de Santo Domingo evaluando una solicitud B1/B2.\n\n"
        . "REGLAS:\n"
        . "1. Haz SOLO UNA PREGUNTA A LA VEZ.\n"
        . "2. NUNCA menciones que leíste la DS-160 ni des pistas de los datos del usuario.\n"
        . "3. Inicia saludando y con una pregunta abierta (ej. '¿A qué se dedica?' o '¿Motivo de su viaje?').\n"
        . "4. Haz entre 4 y 6 preguntas evaluando coherencia, empleo, ingresos e historial de viajes.\n"
        . "5. Al finalizar, emite la decisión iniciando con '[DECISIÓN FINAL]' (VISA APROBADA o VISA RECHAZADA) con un análisis detallado.\n\n"
        . "DATOS DS-160 DEL SOLICITANTE:\n" . $ds160Content;

    $_SESSION['chat_history'] = [
        ['role' => 'system', 'content' => $systemPrompt]
    ];

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}