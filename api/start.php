<?php
// api/start.php
// Recibe el perfil del formulario, lo valida y prepara una nueva entrevista.
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/api_config.php';
require_once __DIR__ . '/../src/InterviewPrompt.php';

use App\InterviewPrompt;

$input = require_json_post();
$rawProfile = $input['perfil'] ?? null;

if (!is_array($rawProfile)) {
    json_response(['success' => false, 'error' => 'No se recibieron los datos del formulario.'], 400);
}

[$profile, $missing] = InterviewPrompt::sanitizeProfile($rawProfile);

if ($missing) {
    $labels = array_map([InterviewPrompt::class, 'labelFor'], $missing);
    json_response([
        'success' => false,
        'error'   => 'Faltan campos obligatorios: ' . implode(', ', $labels) . '.',
        'missing' => $missing,
    ], 422);
}

if (isset($profile['fecha_viaje'], $profile['fecha_regreso'])
    && $profile['fecha_regreso'] < $profile['fecha_viaje']) {
    json_response(['success' => false, 'error' => 'La fecha de regreso no puede ser anterior a la de salida.'], 422);
}

$settings = InterviewPrompt::sanitizeSettings($input['ajustes'] ?? []);

session_regenerate_id(true);
$_SESSION['interview'] = [
    'history'  => [
        ['role' => 'system', 'content' => InterviewPrompt::systemPrompt($profile, date('Y-m-d'), $settings)],
        ['role' => 'user', 'content' => '(El solicitante se acerca a la ventanilla y entrega su pasaporte.)'],
    ],
    'turns'    => 0,
    'finished' => false,
    'settings' => $settings,
];

json_response(['success' => true, 'maxTurns' => MAX_USER_TURNS]);
