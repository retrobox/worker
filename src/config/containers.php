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
    FtpClient\FtpClient::class => function (\Psr\Container\ContainerInterface $container) {
        $client = new FtpClient\FtpClient();
        $client->connect($container->get('ftp')['host'], $container->get('ftp')['ssl'], $container->get('ftp')['port']);
        $client->login($container->get('ftp')['username'], $container->get('ftp')['password']);
        $client->pasv(true);
        return $client;
    },
    \PHPMailer\PHPMailer\PHPMailer::class => function (\Psr\Container\ContainerInterface $container) {
        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mailer->isSMTP();
        $mailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mailer->CharSet = 'UTF-8';
        $mailer->SMTPDebug = $container->get('smtp')['debug'];
        $mailer->Host = $container->get('smtp')['host'];
        $mailer->Port = $container->get('smtp')['port'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $container->get('smtp')['username'];
        $mailer->Password = $container->get('smtp')['password'];
        $mailer->SMTPSecure = $container->get('smtp')['secure']; //tls
        $mailer->setFrom('contact@retrobox.tech', 'Retrobox');
        return $mailer;
    },
    Symfony\Component\Translation\Translator::class => function () {
        $translator = new \Symfony\Component\Translation\Translator('fr_FR');
        $translator->setFallbackLocales(['fr_FR']);
        $translator->addLoader('json', new \Symfony\Component\Translation\Loader\JsonFileLoader());
        $translator->addResource('json', 'src/locales/fr.json', 'fr_FR');
        $translator->addResource('json', 'src/locales/en.json', 'en_EN');
        return $translator;
    },
    Twig_Environment::class => function (\Symfony\Component\Translation\Translator $translator) {
        $loader = new Twig_Loader_Filesystem('./src/templates');
        $environment = new Twig_Environment($loader, []);
        $environment->addExtension(new \Symfony\Bridge\Twig\Extension\TranslationExtension($translator));
        return $environment;
    }
    /*\Elasticsearch\Client::class => function (\Psr\Container\ContainerInterface $container) {
        return \Elasticsearch\ClientBuilder::create()
            ->setHosts([
                $container->get('elasticsearch')['endpoint']
            ])
            ->build();
    }*/
];