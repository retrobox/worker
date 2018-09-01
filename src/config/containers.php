<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;

return [
    \Lefuturiste\RabbitMQConsumer\Client::class => function (\Psr\Container\ContainerInterface $container) {
        return new \Lefuturiste\RabbitMQConsumer\Client(
            new AMQPStreamConnection(
                $container->get('rabbitmq')['host'],
                $container->get('rabbitmq')['port'],
                $container->get('rabbitmq')['username'],
                $container->get('rabbitmq')['password'],
                $container->get('rabbitmq')['virtual_host']
            )
        );
    },
    \Elasticsearch\Client::class => function (\Psr\Container\ContainerInterface $container) {
        return \Elasticsearch\ClientBuilder::create()
            ->setHosts([
                $container->get('elasticsearch')['endpoint']
            ])
            ->build();
    }
];