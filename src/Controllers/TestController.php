<?php

namespace App\Controllers;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TestController
{
    /**
     * @param $payload
     * @param ContainerInterface $container
     * @return bool
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public static function testEmail($payload, ContainerInterface $container)
    {
        $mail = $container->get(PHPMailer::class);
        $mail->addAddress($payload['email'], isset($payload['name']) ? $payload['name'] : "John Doe");
        $mail->isHTML(true);
        $mail->Subject = "Test email, I repeat this is a test";
        $mail->Body = $container->get(Environment::class)
            ->render('email/test.twig', ['payload' => json_encode($payload)]);
        $mail->AltBody = "You must see the email in HTML";
        $mail->send();

        return true;
    }
}