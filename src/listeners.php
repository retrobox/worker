<?php
$client->addListener('order.payed', [\App\Controllers\OrderController::class, 'payed']);
$client->addListener('order.shipped', [\App\Controllers\OrderController::class, 'shipped']);