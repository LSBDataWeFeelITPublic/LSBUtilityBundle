<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\CRUD\Route;

use Symfony\Component\HttpFoundation\Request;

interface RouteGeneratorInterface
{
    public function getName(): string;

    public function getPath(Request $request, ?int $referencePath = null): ?string;
}