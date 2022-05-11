<?php

namespace LSB\UtilityBundle\DataTransfer\APIRequest;

use LSB\UtilityBundle\DataTransfer\Request\RequestData;
use Symfony\Component\HttpFoundation\Request;

interface APIRequestInterface
{
    public function getRequest(): Request;

    public function getRequestData(): ?RequestData;

}