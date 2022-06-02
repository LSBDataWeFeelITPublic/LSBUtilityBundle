<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\CRUD\Route;

use Symfony\Component\Routing\RouterInterface;

abstract class BaseRouteGenerator implements RouteGeneratorInterface
{
    public function __construct(protected RouterInterface $router){}

    /**
     * @return string
     */
    public function getName(): string
    {
        return static::class;
    }
}