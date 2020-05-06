<?php

use App\ApiClient;
use Lefuturiste\Jobatator\Client;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return [
    Client::class => function(ContainerInterface $container) {
        $client = new Client(
            $container->get('jobatator')['host'],
            $container->get('jobatator')['port'],
            $container->get('jobatator')['username'],
            $container->get('jobatator')['password'],
            $container->get('jobatator')['group']
        );
        $client->createConnexion();
        return $client;
    },
    ApiClient::class => function (ContainerInterface $container) {
        return new ApiClient($container->get('api')['endpoint'], $container->get('api')['key']);
    },
    Ftp::class => function (ContainerInterface $container) {
        $client = new Ftp();
        //, $container->get('ftp')['ssl'], $container->get('ftp')['port']
        $client->connect($container->get('ftp')['host']);
        $client->login($container->get('ftp')['username'], $container->get('ftp')['password']);
        //$client->pasv(true);
        return $client;
    },
    PHPMailer::class => function (ContainerInterface $container) {
        $mailer = new PHPMailer(true);
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
        $mailer->setFrom('no-reply@mg.retrobox.tech', 'RetroBox');
        return $mailer;
    },
    Symfony\Component\Translation\Translator::class => function () {
        $translator = new Translator('fr_FR');
        $translator->setFallbackLocales(['fr_FR']);
        $translator->addLoader('json', new JsonFileLoader());
        $translator->addResource('json', 'src/locales/fr.json', 'fr_FR');
        $translator->addResource('json', 'src/locales/en.json', 'en_EN');
        return $translator;
    },
    Environment::class => function (Translator $translator) {
        $loader = new FilesystemLoader('./src/templates');
        $environment = new Environment($loader, []);
        $environment->addExtension(new TranslationExtension($translator));
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