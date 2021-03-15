<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ObjectManager
 * @package LSB\UtilityBundle\Service
 */
class ObjectManager implements ObjectManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * ObjectManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Persist
     *
     * @param $object
     */
    public function persist(object $object): void
    {
        $this->em->persist($object);
    }

    /**
     * Remove
     *
     * @param $object
     */
    public function remove(object $object): void
    {
        $this->em->remove($object);
    }

    /**
     * Flush
     */
    public function flush(): void
    {
        $this->em->flush();
    }
}