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
    ]
];