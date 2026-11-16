<?php
// api/status.php
// Indica qué proveedores de IA están configurados (nunca expone las claves).
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/api_config.php';
require_once __DIR__ . '/../src/AiConsulService.php';

use App\AiConsulService;

$chain = AiConsulService::configuredChain();

json_response([
    'ready'     => count($chain) > 0,
    'providers' => array_map(fn ($p) => ['name' => $p['name'], 'model' => $p['model']], $chain),
]);
