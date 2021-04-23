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
     * @return string|null
     */
    public function getAppCode(): ?string;
}