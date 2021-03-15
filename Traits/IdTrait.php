<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait IdTrait
 * @package LSB\UtilityBundle\Traits
 */
trait IdTrait
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * Get id
     *
     * @return null|integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}
