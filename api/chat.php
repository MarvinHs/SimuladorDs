<?php
// api/chat.php
// Envía la respuesta del solicitante al cónsul (IA) y devuelve su réplica.
// Evita que warnings/notices de PHP se mezclen con el JSON de respuesta.
ini_set('display_errors', '0');
set_time_limit(150);

require_once __DIR__ . '/../config/api_config.php';
require_once __DIR__ . '/../src/AiConsulService.php';
require_once __DIR__ . '/../src/InterviewPrompt.php';

use App\AiConsulService;
use App\InterviewPrompt;

function str_word_count_utf8(string $text): int
{
    return count(preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY));
}

if (!function_exists('curl_init')) {
    json_response(['error' => 'La extensión cURL de PHP no está habilitada en este servidor.'], 500);
}

$input = require_json_post();

if (!isset($_SESSION['interview']['history'])) {
    json_response(['error' => 'La sesión expiró. Vuelve a enviar tu perfil para iniciar la entrevista.', 'expired' => true], 409);
}

$interview = &$_SESSION['interview'];

if ($interview['finished']) {
    json_response(['error' => 'La entrevista ya terminó. Puedes repetirla o editar tu perfil.', 'finished' => true], 409);
}

$userMsg = trim((string) ($input['message'] ?? ''));
$userMsg = mb_substr(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $userMsg) ?? '', 0, MAX_MESSAGE_LENGTH);

$isOpening = $interview['turns'] === 0 && count($interview['history']) === 2;
if ($userMsg === '' && !$isOpening) {
    json_response(['error' => 'Escribe una respuesta antes de enviar.'], 422);
}

$messages = $interview['history'];
if ($userMsg !== '') {
    $messages[] = ['role' => 'user', 'content' => $userMsg];
}

$turns = $interview['turns'] + ($userMsg !== '' ? 1 : 0);

// Si la respuesta es demasiado larga para el nivel elegido, se le indica al
// cónsul (solo para este turno) que interrumpa, como pasa en la ventanilla.
$maxWords = InterviewPrompt::maxWordsFor($interview['settings'] ?? []);
if ($maxWords > 0 && $userMsg !== '' && str_word_count_utf8($userMsg) > $maxWords) {
    $messages[] = [
        'role'    => 'system',
        'content' => 'Nota: la última respuesta del solicitante fue demasiado larga. Interrúmpelo con firmeza (por ejemplo, "Solo responda lo que le pregunto") antes de tu siguiente pregunta, y tenlo en cuenta en la evaluación.',
    ];
}
if ($turns >= MAX_USER_TURNS) {
    // Nota temporal (no se guarda en el historial) para cerrar la entrevista.
    $messages[] = [
        'role'    => 'system',
        'content' => 'Se alcanzó el límite de preguntas. Emite ahora la decisión final con el formato indicado, empezando con ' . DECISION_MARKER . '.',
    ];
}

// Libera el bloqueo de la sesión mientras esperamos a la IA, para no
// congelar otras peticiones del mismo usuario.
session_write_close();

$ai = new AiConsulService(CURL_CA_BUNDLE);
$result = $ai->sendChat($messages);

if (!isset($result['reply'])) {
    json_response(['error' => $result['error'] ?? 'Error desconocido.'], 502);
}

$reply = $result['reply'];
$finished = mb_strpos($reply, DECISION_MARKER) !== false;

// Algunos modelos olvidan la etiqueta: si el mensaje es claramente la
// decisión final (veredicto al inicio o el informe con la puntuación), se
// normaliza para que la interfaz muestre la tarjeta de decisión.
if (!$finished) {
    $verdictAtStart = preg_match('/^\W*(?:\[?\s*DECISI[OÓ]N\s+FINAL\s*\]?\W*)?(?:VISA\s+)?(APROBADA|RECHAZADA)\b/iu', $reply);
    $hasReport = preg_match('/Puntuaci[oó]n de preparaci[oó]n/iu', $reply)
        && preg_match('/VISA\s+(APROBADA|RECHAZADA)/iu', $reply);
    if ($verdictAtStart || $hasReport) {
        $reply = preg_replace('/^\W*\[?\s*DECISI[OÓ]N\s+FINAL\s*\]?\s*/iu', '', $reply);
        $reply = DECISION_MARKER . ' ' . ltrim($reply);
        $finished = true;
    }
}

// Reabre la sesión para guardar el progreso solo si la llamada tuvo éxito,
// así un error no deja el historial a medias.
session_start();
$interview = &$_SESSION['interview'];
if ($userMsg !== '') {
    $interview['history'][] = ['role' => 'user', 'content' => $userMsg];
}
$interview['history'][] = ['role' => 'assistant', 'content' => $reply];
$interview['turns'] = $turns;
$interview['finished'] = $finished;

$approved = null;
if ($finished && preg_match('/' . preg_quote(DECISION_MARKER, '/') . '\W*(?:VISA\s+)?(APROBADA|RECHAZADA)/iu', $reply, $m)) {
    $approved = mb_strtoupper($m[1]) === 'APROBADA';
}

json_response([
    'reply'    => $reply,
    'finished' => $finished,
    'approved' => $approved,
    'turns'    => $turns,
    'maxTurns' => MAX_USER_TURNS,
    'provider' => $result['provider'] ?? null,
]);
