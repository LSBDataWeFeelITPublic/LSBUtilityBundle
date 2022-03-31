<?php

namespace App\DTO\DataTransformer;

interface DataInitializerTransformerInterface
{
    public function initialize(string $inputClass, array $context = []);
}