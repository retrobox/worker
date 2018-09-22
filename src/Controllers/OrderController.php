<?php

namespace App\Controllers;

use App\ApiClient;
use DiscordWebhooks\Client;
use DiscordWebhooks\Embed;
use FtpClient\FtpClient;
use Psr\Container\ContainerInterface;
use Twig_Environment;

class OrderController
{
    public function payed($body, ContainerInterface $container)
    {
        echo ">> The order {$body['id']} was payed";

        //get the order via API
        $response = $container->get(ApiClient::class)
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
                            last_username,
                            first_name,
                            last_name,
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
        //send a email

        /**
         * GENERATE Html invoice
         */
        $twig = $container->get(Twig_Environment::class);
        $template = $twig->load('invoice.twig');
        $itemsParsed = array_map(function ($item) {
            return [
                'name' => $item['title'],
                'price' => $item['price'],
            ];
        }, $order['items']);
        $html = $template->render([
            'id' => $order['id'],
            'created_at' => $order['created_at'],
            'first_name' => $order['user']['first_name'],
            'last_name' => $order['user']['last_name'],
            'address_first_line' => $order['user']['address_first_line'],
            'address_second_line' => $order['user']['address_second_line'],
            'address_postal_code' => $order['user']['address_postal_code'],
            'address_city' => $order['user']['address_city'],
            'way' => ucfirst($order['way']),
            'items' => array_merge($itemsParsed, [
                ['name' => 'Sous total/Sub total', 'price' => $order['sub_total_price']],
                ['name' => 'Shipping/Livraison', 'price' => $order['total_shipping_price']]
            ]),
            'total' => $order['total_price']
        ]);
        $fileName = 'invoice-' . $order['id'] . '.html';
        file_put_contents('tmp/' . $fileName, $html);

        /**
         * Upload invoice on ftp
         */
        ($container->get(FtpClient::class)
            ->put(
                $container->get('ftp')['directory'] . '/' . $fileName,
                'tmp/' . $fileName,
                FTP_BINARY
            )
        );

        /**
         * Send a email to the customer
         */

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