<?php

namespace App\DTO\DataTransformer;

interface DataTransformerInterface
{
    public function transform($object, string $to, array $context = []);

    public function supportsTransformation($data, string $to, array $context = []): bool;
}