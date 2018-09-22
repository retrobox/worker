<?php
namespace App\Controllers;

use Psr\Container\ContainerInterface;

class OrderController {
    public function payed($body, ContainerInterface $container) {
        echo ">> The order {$body['id']} was payed";
        //send a email
        //send a discord webhook
    }
}