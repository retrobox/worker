<?php

use App\Controllers\OrderController;
use App\Controllers\TestController;

$client->addHandler('order.payed', [OrderController::class, 'payed']);
$client->addHandler('order.shipped', [OrderController::class, 'shipped']);
$client->addHandler('test.email', [TestController::class, 'testEmail']);