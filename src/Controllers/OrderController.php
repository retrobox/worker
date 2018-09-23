<?php

namespace App\Controllers;

use App\ApiClient;
use DiscordWebhooks\Client;
use DiscordWebhooks\Embed;
use FtpClient\FtpClient;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Symfony\Component\Translation\Translator;
use Twig_Environment;

class OrderController
{
    public function payed($body, ContainerInterface $container)
    {
        echo ">> The order {$body['id']} was payed";

        $apiClient = $container->get(ApiClient::class);
        //get the order via API
        $response = $apiClient
            ->graphQL([
                'query' => "query (\$id: String) {
                    getOneShopOrder(id: \$id) {
                        id
                        total_shipping_price
                        total_price
                        sub_total_price
                        way
                        created_at
                        items {
                            id
                            title
                            price
                        }
                        user {
                            id
                            last_avatar
                            last_username
                            first_name
                            last_name
                            last_locale
                            last_email
                            address_first_line
                            address_second_line
                            address_city
                            address_postal_code
                            address_country
                        }
                    }
                }",
                'variables' => [
                    'id' => $body['id']
                ]
            ]);
        $order = $response->getParsedBody(1)['data']['getOneShopOrder'];

        /**
         * GENERATE Html invoice
         */
        $twig = $container->get(Twig_Environment::class);
        $translator = $container->get(Translator::class);
        $translator->setLocale($order['user']['last_locale']);
        $itemsParsed = array_map(function ($item) {
            return [
                'name' => $item['title'],
                'price' => $item['price'],
            ];
        }, $order['items']);
        $templateVariables = [
            'id' => $order['id'],
            'created_at' => $order['created_at'],
            'first_name' => $order['user']['first_name'],
            'last_name' => $order['user']['last_name'],
            'address_first_line' => $order['user']['address_first_line'],
            'address_second_line' => $order['user']['address_second_line'],
            'address_postal_code' => $order['user']['address_postal_code'],
            'address_city' => $order['user']['address_city'],
            'address_country' => $order['user']['address_country'],
            'way' => ucfirst($order['way']),
            'items' => $itemsParsed,
            'total' => $order['total_price'],
            'sub_total' => $order['sub_total_price'],
            'total_shipping_price' => $order['total_shipping_price'],
            "bill_url" => ""
        ];
        $html = $twig->render('invoice.twig', $templateVariables);
        $fileName = 'invoice-' . $order['id'] . '.html';
        file_put_contents('tmp/' . $fileName, $html);

        /**
         * Upload invoice on ftp
         */
        $container->get(FtpClient::class)
            ->put(
                $container->get('ftp')['directory'] . '/' . $fileName,
                'tmp/' . $fileName,
                FTP_BINARY
            );
        $templateVariables['bill_url'] = $container->get('data_endpoint') . "/" . $fileName;

        //edit the link on the api
        $apiClient->graphQL([
            'query' => "mutation (\$order: OrderUpdateInput) {
                updateOrder(order: \$order)
            }",
            'variables' => [
                'order' => [
                    'id' => $order['id'],
                    'bill_url' => $templateVariables['bill_url']
                ]
            ]
        ]);

        /**
         * Send a email to the customer
         */
        $mail = $container->get(PHPMailer::class);
        try {
            $mail->addAddress($order['user']['last_email'], "{$order['user']['first_name']} {$order['user']['last_name']}");
            $mail->isHTML(true);
            $mail->Subject = $translator->trans('order-payed.title');
            $mail->Body = $twig->render('email/order-payed.twig', $templateVariables);
            $mail->AltBody = $twig->render('email/order-payed-row.twig', $templateVariables);

            $mail->send();
        } catch (\Exception $e) {
            echo 'PHPMailer: Message could not be sent. Mailer Error: ', $e->getMessage();
        }

        /**
         * Send a discord webhook
         */
        $color = 0;
        if ($order['way'] === 'paypal') {
            $color = 2600544;
        }
        if ($order['way'] === 'stripe') {
            $color = 1482885;
        }

        $embed = new Embed();
        $embed
            ->title("New order!")
            ->author($order['user']['last_username'], '', $order['user']['last_avatar'])
            ->field("Id", $order['id'])
            ->field("Way", $order['way'])
            ->field("Numbers of items", count($order['items']))
            ->field("Shipping price", "€ " . $order['total_shipping_price'])
            ->field("Sub total", "€ " . $order['sub_total_price'])
            ->field("Total", "€ " . $order['sub_total_price'])
            ->color($color);

        (new Client($container->get('discord_webhooks')['order']))->embed($embed)->send();
    }
}