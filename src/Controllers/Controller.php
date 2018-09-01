<?php
namespace App\Controllers;

use Psr\Container\ContainerInterface;

class Controller {
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}