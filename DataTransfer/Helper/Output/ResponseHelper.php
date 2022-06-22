<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Output;

use Symfony\Component\HttpFoundation\Response;

class ResponseHelper
{
    public static function prepareDeleteResponse($result = null): array
    {
        $statusCode = Response::HTTP_OK;
        return [$result, $statusCode];
    }

    public static function prepareNoContentResponse(): array
    {
        $statusCode = Response::HTTP_NO_CONTENT;
        return [null, $statusCode];
    }

    public static function prepareOKResponse($result = null): array
    {
        $statusCode = Response::HTTP_OK;
        return [$result, $statusCode];
    }

    public static function prepareNotGrantedResponse($result = null): array
    {
        $result = $result ?? ['error' => 'Access denied.'];
        $statusCode = Response::HTTP_FORBIDDEN;

        return [$result, $statusCode];
    }

    public static function prepareNotFoundResponse($result = null): array
    {
        $result = $result ?? ['error' => 'Object not found.'];
        $statusCode = Response::HTTP_NOT_FOUND;

        return [$result, $statusCode];
    }

    public static function prepareBadRequestResponse($result = null): array
    {
        $result = $result ?? ['error' => 'Bad request.'];
        $statusCode = Response::HTTP_BAD_REQUEST;

        return [$result, $statusCode];
    }

    public static function generateResponse(?string $content, int $statusCode, ?string $newResourceUrl): Response
    {
        $response = (new Response)
            ->setStatusCode($statusCode);

        if ($content !== null) {
            $response
                ->setContent($content);
        }
        if ($newResourceUrl) {
            $response->headers->set('Location', $newResourceUrl);
        }

        return $response;
    }
}