<?php
header('Content-Type: application/json');

echo json_encode([
    'ok' => true,
    'metodo' => $_SERVER['REQUEST_METHOD']
]);