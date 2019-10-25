<?php

namespace App\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;

class TestController
{
    public function testEmail($body, ContainerInterface $container)
    {
        $mail = $container->get(PHPMailer::class);
        try {
            $mail->addAddress('spamfree@matthieubessat.fr', "Matthieu Bessat");
            $mail->isHTML(true);
            $mail->Subject = "Email de test";
            $mail->Body = "Ceci est un email de test";
            $mail->AltBody = "LELEL";

            $mail->send();
        } catch (\Exception $e) {
            echo 'PHPMailer: Message could not be sent. Mailer Error: ', $e->getMessage();
        }
    }
}