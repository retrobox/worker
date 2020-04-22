<?php
$client->addHandler('order.payed', [\App\Controllers\OrderController::class, 'payed']);
$client->addHandler('order.shipped', [\App\Controllers\OrderController::class, 'shipped']);
$client->addHandler('test.email', [\App\Controllers\TestController::class, 'testEmail']);