<?php

namespace App\Controllers;

use App\ApiClient;
use DiscordWebhooks\Client;
use DiscordWebhooks\Embed;
use Exception;
use Ftp;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Carbon\Carbon;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class OrderController
{
    /**
     * @param $body
     * @param ContainerInterface $container
     * @return bool
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws Exception
     */
    public static function payed($body, ContainerInterface $container)
    {
        echo ">> The order {$body['id']} was payed \n";

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
                            pivot {
                                shop_item_custom_option_storage
                                shop_item_custom_option_color
                            }
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
        if ($response->getStatusCode() != 200){
            throw new Exception(
                "ERR: Non 200 response code from the API, while trying to fetch order: " . $response->getBody(),
                $response->getStatusCode());
        }
        $order = $response->getParsedBody(1)['data']['getOneShopOrder'];

        /**
         * GENERATE Html invoice
         */
        $twig = $container->get(Environment::class);
        $translator = $container->get(Translator::class);
        $translator->setLocale($order['user']['last_locale']);
        $itemsParsed = array_map(function ($item) {
            return [
                'name' => $item['title'],
                'price' => $item['price'],
            ];
        }, $order['items']);
        $templateVariables = [
            'id' => strtoupper($order['id']),
            'created_at' => ((new Carbon($order['created_at']))->locale('fr')->isoFormat('dddd DD MMMM YYYY')),
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
        
        /*
        // Uncomment to just see the invoice
        echo "\n tmp/" . $fileName;
        // DEBUG
        exit(0);
        */
        
        echo "   Uploading to " . $container->get('ftp')['directory'] . '/' . $fileName . " \n";

        /**
         * Upload invoice on ftp
         */
        $ftp = new Ftp();
        $ftp->connect($container->get('ftp')['host']);
        $ftp->login($container->get('ftp')['username'], $container->get('ftp')['password']);
        $ftp->put(
            $container->get('ftp')['directory'] . '/' . $fileName,
            'tmp/' . $fileName,
            FTP_BINARY
        );
        $ftp->close();
        
        echo "   Upload complete \n";

        $templateVariables['bill_url'] = $container->get('data_endpoint') . "/" . $fileName;

        //edit the link on the api
        $response = $apiClient->graphQL([
            'query' => "mutation (\$order: ShopOrderUpdateInput) {
                updateShopOrder(order: \$order)
            }",
            'variables' => [
                'order' => [
                    'id' => $order['id'],
                    'bill_url' => $templateVariables['bill_url']
                ]
            ]
        ]);
        if ($response->getStatusCode() != 200){
            throw new Exception(
                "ERR: Non 200 response code from the API, while trying to update bill_url: " . $response->getBody(),
                $response->getStatusCode());
        }

        /**
         * Send a email to the customer
         */
        $mail = $container->get(PHPMailer::class);
        $mail->addAddress($order['user']['last_email'], "{$order['user']['first_name']} {$order['user']['last_name']}");
        $mail->isHTML(true);
        $mail->Subject = $translator->trans('order-payed.title');
        $mail->Body = $twig->render('email/order-payed.twig', $templateVariables);
        $mail->AltBody = $twig->render('email/order-payed-row.twig', $templateVariables);

        $mail->send();

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

        return true;
    }

    /**
     * @param $body
     * @param ContainerInterface $container
     * @return bool
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public static function shipped($body, ContainerInterface $container)
    {
        //get the order by using the api
        echo ">> The order {$body['id']} was shipped";

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
         * Send a email to the customer
         */
        $templateVariables = [
            'id' => $order['id'],
            'first_name' => $order['user']['first_name'],
            'last_name' => $order['user']['last_name'],
            'address_first_line' => $order['user']['address_first_line'],
            'address_second_line' => $order['user']['address_second_line'],
            'address_postal_code' => $order['user']['address_postal_code'],
            'address_city' => $order['user']['address_city'],
            'address_country' => $order['user']['address_country'],
            'total' => $order['total_price'],
            'sub_total' => $order['sub_total_price'],
            'total_shipping_price' => $order['total_shipping_price']
        ];
        $twig = $container->get(Environment::class);
        $mail = $container->get(PHPMailer::class);
        $mail->addAddress($order['user']['last_email'], "{$order['user']['first_name']} {$order['user']['last_name']}");
        $mail->isHTML(true);
        $mail->Subject = $container->get(Translator::class)->trans('order-shipped.title');
        $mail->Body = $twig->render('email/order-shipped.twig', $templateVariables);
        $mail->AltBody = $twig->render('email/order-shipped-row.twig', $templateVariables);

        $mail->send();

        return true;
    }
}