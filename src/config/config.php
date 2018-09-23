<?php
return [
    'rabbitmq' => [
        'host' => getenv('RABBITMQ_HOST'),
        'port' => getenv('RABBITMQ_PORT'),
        'username' => getenv('RABBITMQ_USERNAME'),
        'password' => getenv('RABBITMQ_PASSWORD'),
        'virtual_host' => getenv('RABBITMQ_VIRTUAL_HOST')
    ],
    'elasticsearch' => [
        'endpoint' => getenv('ELASTICSEARCH_ENDPOINT'),
        'index' => getenv('ELASTICSEARCH_INDEX')
    ],
    'discord_webhooks' => [
        'order' => getenv('DISCORD_WEBHOOK_ORDER')
    ],
    'api' => [
        'endpoint' => getenv('API_ENDPOINT'),
        'key' => getenv('API_KEY')
    ],
    'ftp' => [
        'host' => getenv('FTP_HOST'),
        'port' => getenv('FTP_PORT'),
        'username' => getenv('FTP_USERNAME'),
        'password' => getenv('FTP_PASSWORD'),
        'ssl' => (getenv('FTP_SSL') ? true : false),
        'directory' => getenv('FTP_DIRECTORY')
    ],
    'data_endpoint' => getenv('DATA_ENDPOINT'),
    'smtp' => [
        'host' => getenv('SMTP_HOST'),
        'port' => getenv('SMTP_PORT'),
        'username' => getenv('SMTP_USERNAME'),
        'password' => getenv('SMTP_PASSWORD'),
        'secure' => getenv('SMTP_SECURE')
    ]
];