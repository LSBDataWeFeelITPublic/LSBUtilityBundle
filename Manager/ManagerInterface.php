<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Manager;

use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Repository\RepositoryInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Interface ManagerInterface
 * @package LSB\UtilityBundle\Service
 */
interface ManagerInterface
{
    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager(): ObjectManagerInterface;

    /**
     * @return FactoryInterface
     */
    public function getFactory(): FactoryInterface;

    /**
     * @return RepositoryInterface
     */
    public function getRepository(): RepositoryInterface;

    /**
     * @return BaseEntityType|null
     */
    public function getForm(): ?BaseEntityType;

    /**
     * @param $object
     * @return mixed
     */
    public function persist(object $object);

    /**
     * @param object $object
     * @param bool $throwException
     * @return bool
     */
    public function doPersist(object $object, bool $throwException = true): bool;

    /**
     * @param $object
     * @return mixed
     */
    public function remove(object $object);

    /**
     * @param object $object
     * @param bool $throwException
     * @return bool
     */
    public function doRemove(object $object, bool $throwException = true): bool;

    /**
     * @return mixed
     */
    public function flush();

    /**
     * @return object
     */
    public function createNew(): object;

}