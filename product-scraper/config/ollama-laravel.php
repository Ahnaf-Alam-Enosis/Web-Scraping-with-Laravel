<?php

// Config for Cloudstudio/Ollama

return [
    'model' => env('OLLAMA_MODEL', 'llama3'),
    'url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),
    'default_prompt' => env('OLLAMA_DEFAULT_PROMPT', 'Hello, how can I assist you today?'),
    'connection' => [
        'timeout' => (int)env('OLLAMA_CONNECTION_TIMEOUT', 300),
        'verify_ssl' => env('OLLAMA_VERIFY_SSL', true),
    ],
    'temperature' => env('OLLAMA_TEMPERATURE', 0.8),
    'auth' => [
        'type' => env('OLLAMA_AUTH_TYPE', null), // 'bearer' or 'basic' or null
        'token' => env('OLLAMA_AUTH_TOKEN', null),
        'username' => env('OLLAMA_AUTH_USERNAME', null),
        'password' => env('OLLAMA_AUTH_PASSWORD', null),
    ],
    'headers' => [],
];
