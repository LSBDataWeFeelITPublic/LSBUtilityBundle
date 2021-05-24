<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Manager;

use LSB\UtilityBundle\Application\ApplicationContextInterface;
use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Repository\RepositoryInterface;
use LSB\UtilityBundle\Security\VoterSubjectInterface;

/**
 * Interface ManagerInterface
 * @package LSB\UtilityBundle\Service
 */
interface ManagerInterface extends ApplicationContextInterface
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

    /**
     * @return array
     */
    public function getBundleConfiguration(): array;

    /**
     * @param array $configuration
     * @return $this
     */
    public function setBundleConfiguration(array $bundleConfiguration): self;

    /**
     * @param array $resourceConfiguration
     * @return $this
     */
    public function setResourceConfiguration(array $resourceConfiguration): self;

    /**
     * @return array
     */
    public function getResourceConfiguration(): array;

    /**
     * @return string
     */
    public function getResourceEntityClass(): string;

    /**
     * @return string
     */
    public function getResourceVoterSubjectClass(): string;

    /**
     * @return string
     */
    public function getResourceFormClass(): string;

    /**
     * @param ...$args
     * @return VoterSubjectInterface
     */
    public function getVoterSubject(...$args): VoterSubjectInterface;

    /**
     * @param int $id
     * @return mixed
     */
    public function getById(int $id);

    /**
     * @param string $uuid
     * @return null
     */
    public function getByUuid(string $uuid);
}