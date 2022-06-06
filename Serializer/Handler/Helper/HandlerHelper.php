<?php

namespace LSB\UtilityBundle\Serializer\Handler\Helper;

use JMS\Serializer\DeserializationContext;

class HandlerHelper
{
    /**
     * @param \JMS\Serializer\DeserializationContext $context
     * @return string
     */
    public static function getCurrentPath(DeserializationContext $context): string
    {
        $currentPathString = null;

        foreach ($context->getCurrentPath() as $key) {
            if ($currentPathString) {
                $currentPathString .= '.';
            }

            $currentPathString .= $key;
        }

        return (string)$currentPathString;
    }
}