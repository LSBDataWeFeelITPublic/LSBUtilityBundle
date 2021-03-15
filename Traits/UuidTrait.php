<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Trait UuidTrait
 * @package LSB\UtilityBundle\Traits
 */
trait UuidTrait
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(name="uuid", type="uuid", unique=true, nullable=false, columnDefinition="uuid DEFAULT uuid_generate_v4()")
     */
    protected $uuid = null;

    /**
     * @param bool $force
     * @return string
     * @throws \Exception
     */
    public function generateUuid(bool $force = false): string
    {
        if ($this->uuid === null || $force) {
            $this->uuid = Uuid::v4();
        }

        return (string) $this->uuid;
    }

    /**
     * Get id
     *
     * @return null|integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set uuid
     *
     * @param $uuid
     *
     * @return $this
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid(): string
    {
        return (string)$this->uuid;
    }

//    /**
//     * @return Uuid|null
//     */
//    public function getUuidObject(): ?Uuid
//    {
//        return $this->uuid;
//    }
}