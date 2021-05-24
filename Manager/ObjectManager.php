<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

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
     * Persist
     *
     * @param object $object
     * @return object
     */
    public function refresh(object $object): object
    {
        return $this->em->refresh($object);
    }

    /**
     * Flush
     */
    public function flush(): void
    {
        $this->em->flush();
    }


    /**
     * @param string $fqcn
     * @return ObjectRepository|null
     */
    public function getRepository(string $fqcn): ?ObjectRepository
    {
        return $this->em->getRepository($fqcn);
    }
}