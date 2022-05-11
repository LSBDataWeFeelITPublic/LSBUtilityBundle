<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DataTransfer\Request;

use Symfony\Component\HttpFoundation\Request;

class RequestAttributes
{
    const REQUEST_DATA = 'requestData';

    const DTO = 'dto';
    const IS_VALID = 'isValid';
    const VALIDATION_ERRORS = 'validationErrors';

    const IDENTIFIER_ATTRIBUTE_UUID = 'uuid';
    const IDENTIFIER_ATTRIBUTE_ID = 'id';

    /**
     * Predefined object identifiers. Proper priority is required.
     * key 0 - the highest priority
     * key 100+ - the lowest priority
     *
     * @var array|string[]
     */
    protected static array $identifierAttributes = [
        self::IDENTIFIER_ATTRIBUTE_UUID,
        self::IDENTIFIER_ATTRIBUTE_ID
    ];

    /**
     * @param Request $request
     * @return RequestIdentifier|null
     */
    public static function getRequestIdentifier(Request $request): ?RequestIdentifier
    {
        /**
         * @var string $identifierAttributeName
         */
        foreach (self::$identifierAttributes as $identifierAttributeName)
        {
            if (!$request->attributes->has(mb_strtolower($identifierAttributeName))) {
                continue;
            }

            $value = $request->attributes->get($identifierAttributeName);
            return new RequestIdentifier($identifierAttributeName, $value, gettype($value));
        }

        return null;
    }


    /**
     * @param Request $request
     * @return RequestData
     */
    public static function getOrCreateRequestData(Request $request): RequestData
    {
        $requestData = null;

        if ($request->attributes->has(RequestAttributes::REQUEST_DATA)) {
            $requestData = $request->attributes->get(RequestAttributes::REQUEST_DATA);
        }

        if (!$requestData instanceof RequestData) {
            $requestData = new RequestData();
        }

        return $requestData;
    }

    /**
     * @param Request $request
     * @param RequestData $requestData
     */
    public static function updateRequestData(Request $request, RequestData $requestData): void
    {
        $request->attributes->set(self::REQUEST_DATA, $requestData);
    }
}