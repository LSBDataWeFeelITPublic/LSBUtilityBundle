<?php

namespace LSB\UtilityBundle\DTO\DataTransformer;

interface DataInitializerTransformerInterface extends DataTransformerInterface
{
    const CONTEXT_OBJECT_TO_POPULATE = 'object_to_populate';
    const CONTEXT_OBJECT_MANAGER = 'object_manager';
    const CONTEXT_INPUT_DTO = 'input_dto';
    const CONTEXT_OUTPUT_DTO = 'output_dto';

    public function initialize(string $inputClass, array $context = []);
}