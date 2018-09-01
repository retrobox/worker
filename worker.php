<?php

require 'vendor/autoload.php';

//dotenv
(new \Dotenv\Dotenv(__DIR__))->load();

$container = new DI\Container();

$builder = new DI\ContainerBuilder();
$builder->addDefinitions(include './src/config/config.php');
$builder->addDefinitions(include './src/config/containers.php');
$container = $builder->build();

$client = $container->get(\Lefuturiste\RabbitMQConsumer\Client::class);

$client->setRootValue($container);

include 'src/listeners.php';

$client->listen();