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
    \App\ApiClient::class => function (\Psr\Container\ContainerInterface $container) {
        return new \App\ApiClient($container->get('api')['endpoint'], $container->get('api')['key']);
    },
    Twig_Environment::class => function () {
        $loader = new Twig_Loader_Filesystem('./src/templates');
        return new Twig_Environment($loader, []);
    },
    FtpClient\FtpClient::class => function (\Psr\Container\ContainerInterface $container) {
        $client = new FtpClient\FtpClient();
        $client->connect($container->get('ftp')['host'], $container->get('ftp')['ssl'], $container->get('ftp')['port']);
        $client->login($container->get('ftp')['username'], $container->get('ftp')['password']);
        $client->pasv(true);
        return $client;
    }
    /*\Elasticsearch\Client::class => function (\Psr\Container\ContainerInterface $container) {
        return \Elasticsearch\ClientBuilder::create()
            ->setHosts([
                $container->get('elasticsearch')['endpoint']
            ])
            ->build();
    }*/
];