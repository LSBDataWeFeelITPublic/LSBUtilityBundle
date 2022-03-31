<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DTO\APIRequest;

use LSB\UtilityBundle\DTO\Request\RequestData;
use Symfony\Component\HttpFoundation\Request;

class APIRequest implements APIRequestInterface
{
    public function __construct(
        protected Request      $request,
        protected ?RequestData $requestData = null
    ) {
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return RequestData|null
     */
    public function getRequestData(): ?RequestData
    {
        return $this->requestData;
    }
}