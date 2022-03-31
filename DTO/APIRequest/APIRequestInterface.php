<?php

namespace LSB\UtilityBundle\DTO\APIRequest;

use LSB\UtilityBundle\DTO\Request\RequestData;
use Symfony\Component\HttpFoundation\Request;

interface APIRequestInterface
{
    public function getRequest(): Request;

    public function getRequestData(): ?RequestData;

}