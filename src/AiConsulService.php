<?php
// src/AiConsulService.php

namespace App;

class AiConsulService {
    private $apiKey;
    private $model;

    public function __construct($apiKey, $model = 'gpt-4o-mini') {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function sendChat(array $messages) {
        $url = 'https://api.groq.com/openai/v1/chat/completions';

        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.7
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . 'Bearer ' . $this->apiKey
        ]);
        
        // Evitar problemas de certificados SSL en local con XAMPP
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Tiempo máximo de espera (30 segundos)
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['error' => 'Error de cURL: ' . $curlError];
        }

        $decoded = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $decoded['error']['message'] ?? 'Error desconocido en la API de OpenAI (Código ' . $httpCode . ')';
            return ['error' => $errorMsg];
        }

        if (isset($decoded['choices'][0]['message']['content'])) {
            return ['reply' => $decoded['choices'][0]['message']['content']];
        }

        return ['error' => 'Respuesta inesperada de la IA.'];
    }
}