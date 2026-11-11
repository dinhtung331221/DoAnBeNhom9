<?php

return array(
    'name' => 'TableTap',
    'env' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim(env('APP_URL', 'http://localhost/DoAnBeNhom9/DoAnBeNhom9/public'), '/'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Ho_Chi_Minh'),
    'qr_secret' => env('QR_TOKEN_SECRET', 'change-this-secret-before-production'),
    'upload_max_bytes' => (int) env('UPLOAD_MAX_BYTES', 2097152),
);

