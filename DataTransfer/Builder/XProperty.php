<?php

namespace LSB\UtilityBundle\DataTransfer\Builder;

use OpenApi\Attributes\AdditionalProperties;
use OpenApi\Attributes\Discriminator;
use OpenApi\Attributes\ExternalDocumentation;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Xml;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class XProperty extends \OpenApi\Attributes\Property
{
    public function __construct(
        XBuilder                       $builder,
        ?string                        $property = null,
        object|string|null             $ref = null,
        ?string                        $schema = null,
        ?string                        $title = null,
        ?string                        $description = null,
        ?array                         $required = null,
        ?array                         $properties = null,
        ?string                        $type = null,
        ?string                        $format = null,
        ?Items                         $items = null,
        ?string                        $collectionFormat = null,
                                       $default = Generator::UNDEFINED,
                                       $maximum = null,
        ?bool                          $exclusiveMaximum = null,
                                       $minimum = null,
        ?bool                          $exclusiveMinimum = null,
        ?int                           $maxLength = null,
        ?int                           $minLength = null,
        ?int                           $maxItems = null,
        ?int                           $minItems = null,
        ?bool                          $uniqueItems = null,
        ?string                        $pattern = null,
        ?array                         $enum = null,
        ?Discriminator                 $discriminator = null,
        ?bool                          $readOnly = null,
        ?bool                          $writeOnly = null,
        ?Xml                           $xml = null,
        ?ExternalDocumentation         $externalDocs = null,
                                       $example = Generator::UNDEFINED,
        ?bool                          $nullable = null,
        ?bool                          $deprecated = null,
        ?array                         $allOf = null,
        ?array                         $anyOf = null,
        ?array                         $oneOf = null,
        AdditionalProperties|bool|null $additionalProperties = null,
        ?array                         $x = null,
        ?array                         $attachables = null
    ) {
        $x = $builder->toArray();

        parent::__construct(
            $property,
            $ref,
            $schema,
            $title,
            $description,
            $required,
            $properties,
            $type,
            $format,
            $items,
            $collectionFormat,
            $default,
            $maximum,
            $exclusiveMaximum,
            $minimum,
            $exclusiveMinimum,
            $maxLength,
            $minLength,
            $maxItems,
            $minItems,
            $uniqueItems,
            $pattern,
            $enum,
            $discriminator,
            $readOnly,
            $writeOnly,
            $xml,
            $externalDocs,
            $example,
            $nullable,
            $deprecated,
            $allOf,
            $anyOf,
            $oneOf,
            $additionalProperties,
            $x,
            $attachables
        );
    }
}