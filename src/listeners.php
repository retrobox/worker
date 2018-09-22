<?php
$client->addListener('order.payed', [\App\Controllers\OrderController::class, 'payed']);