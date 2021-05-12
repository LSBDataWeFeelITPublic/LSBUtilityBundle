<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Application;

/**
 * Interface ApplicationContextInterface
 * @package App\Application
 */
interface ApplicationContextInterface
{
    /**
     * @param bool $fetch
     * @return string|null
     */
    public function getAppCode(bool $fetch = true): ?string;
}