<?php

namespace LSB\UtilityBundle\DataTransfer\DataTransformer;

use LSB\UtilityBundle\Module\ModuleInterface;

interface DataTransformerInterface extends ModuleInterface
{
    const TAG = 'lsb.dto.data_transformer';

    public function transform($data, string $to, array $context = []);

    public function supportsTransformation($data, string $to, array $context = []): bool;
}