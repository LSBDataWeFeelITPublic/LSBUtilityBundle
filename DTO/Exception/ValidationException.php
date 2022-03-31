<?php

namespace LSB\UtilityBundle\DTO\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class ValidationException extends BadRequestHttpException
{
    /**
     * @var array
     */
    protected array $errors = [];

    /**
     * @param string|null $message
     * @param Throwable|null $previous
     * @param int $code
     * @param array $headers
     * @param array $errors
     */
    public function __construct(?string $message = '', \Throwable $previous = null, int $code = 0, array $headers = [], array $errors = [])
    {
        parent::__construct($message, $previous, $code, $headers);
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }


}