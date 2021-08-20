<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Interfaces;

/**
 * Interface UuidInterface
 * @package LSB\UtilityBundle\Interfaces
 */
interface UuidInterface extends IdInterface, \Stringable
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

    /**
     * @param bool $force
     * @return string
     */
    public function generateUuid(bool $force = false): string;
}