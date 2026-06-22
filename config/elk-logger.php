<?php

return [
    'channel_name' => env('ELK_LOGGER_CHANNEL', 'elasticsearch'),

    'hosts' => array_filter(array_map('trim', explode(',', (string) env('ELASTICSEARCH_HOST', '')))),

    'username' => env('ELASTICSEARCH_USER'),
    'password' => env('ELASTICSEARCH_PASSWORD'),

    'index_prefix' => env('ELASTICSEARCH_INDEX_PREFIX', 'log'),

    'index_date_format' => env('ELASTICSEARCH_INDEX_DATE_FORMAT', 'Y_m_d'),

    'level' => env('ELASTICSEARCH_LOG_LEVEL', 'info'),

    'ignore_errors' => (bool) env('ELASTICSEARCH_IGNORE_ERRORS', true),

    'app_name' => env('APP_NAME', 'app'),
    'environment' => env('APP_ENV', 'production'),

    'request_logging' => [
        'duration_header' => env('ELK_LOGGER_DURATION_HEADER', 'Duration'),
        'message' => env('ELK_LOGGER_REQUEST_MESSAGE', 'Incoming Request'),
    ],
];
