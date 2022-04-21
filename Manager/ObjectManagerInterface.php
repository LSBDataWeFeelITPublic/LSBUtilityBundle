<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ObjectRepository;
use LSB\UtilityBundle\Repository\RepositoryInterface;

/**
 * Class ObjectManager
 * @package LSB\UtilityBundle\Service
 */
interface ObjectManagerInterface
{
    /**
     * Persist
     *
     * @param object $object
     */
    public function persist(object $object): void;

    /**
     * Remove
     *
     * @param object $object
     */
    public function remove(object $object): void;

    /**
     * @param object $object
     * @return iterable
     */
    public function validate(object $object): iterable;

    /**
     * Flush
     */
    public function flush(): void;

    /**
     * Refresh
     *
     * @param object $object
     */
    public function refresh(object $object): void;

    /**
     * @return UnitOfWork
     */
    public function getUnitOfWork(): UnitOfWork;

    /**
     * Get repository by FQCN
     *
     * @param string $fqcn
     * @return ObjectRepository|null
     */
    public function getRepository(string $fqcn): ?ObjectRepository;

    /**
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface;
}