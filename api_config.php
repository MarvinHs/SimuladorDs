<?php
// config/api_config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de Groq (Gratis y Ultrarrápido)
// En Docker se toma de la variable de entorno GROQ_API_KEY (archivo .env)
define('OPENAI_API_KEY', getenv('GROQ_API_KEY') ?: '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'); 
define('OPENAI_MODEL', 'openai/gpt-oss-120b'); 

define('UPLOAD_DIR', __DIR__ . '/../uploads/');


