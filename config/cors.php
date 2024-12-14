<?php

return [
    'paths' => ['api/*'],
    'allowed_origins' => ['http://localhost:5173', 'http://localhost:5174'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];