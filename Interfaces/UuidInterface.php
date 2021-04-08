<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Interfaces;

/**
 * Interface UuidInterface
 * @package LSB\UtilityBundle\Interfaces
 */
interface UuidInterface extends IdInterface
{
    /**
     * @param $uuid
     * @return mixed
     */
    public function setUuid($uuid);

    /**
     * @return string
     */
    public function getUuid(): string;
}