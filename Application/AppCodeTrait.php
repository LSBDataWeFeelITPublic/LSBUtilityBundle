<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Application;

/**
 * Trait AppCodeTrait
 * @package LSB\UtilityBundle\Application
 */
trait AppCodeTrait
{
    protected ?string $appCode = null;

    /**
     * @return string|null
     */
    public function getAppCode(bool $fetch = true): ?string
    {
        if ($fetch) {
            $this->fetchAppCode();
        }

        return $this->appCode;
    }

    /**
     * @param string|null $appCode
     * @return $this
     */
    public function setAppCode(?string $appCode): self
    {
        $this->appCode = $appCode;
        return $this;
    }

    /**
     *
     */
    protected function fetchAppCode(): void
    {
        if ($this->getAppCode(false)) {
            return;
        }

        $ownInterfaces = class_implements(static::class);
        foreach ($ownInterfaces as $ownInterface) {
            if (defined("$ownInterface::CODE")) {
                $this->appCode = $ownInterface::CODE;
                break;
            }
        }
    }


}