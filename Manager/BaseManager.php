<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Manager;

use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Repository\RepositoryInterface;
use Symfony\Component\Form\AbstractType;
use LSB\UtilityBundle\Exception\ObjectManager\DoRemoveException;
use LSB\UtilityBundle\Exception\ObjectManager\DoPersistException;

/**
 * Class BaseManager
 * @package LSB\UtilityBundle\Service
 */
abstract class BaseManager implements ManagerInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var AbstractType|null
     */
    protected $form;

    /**
     * BaseManager constructor.
     * @param ObjectManagerInterface $objectManager
     * @param FactoryInterface $factory
     * @param RepositoryInterface|null $repository
     * @param BaseEntityType|null $form
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        FactoryInterface $factory,
        ?RepositoryInterface $repository = null,
        ?BaseEntityType $form = null
    ) {
        $this->objectManager = $objectManager;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->form = $form;
    }

    /**
     * @inheritDoc
     */
    public function getObjectManager(): ObjectManagerInterface
    {
        return $this->objectManager;
    }

    /**
     * @return FactoryInterface
     */
    public function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    /**
     * @return RepositoryInterface
     * @throws \Exception
     */
    public function getRepository(): RepositoryInterface
    {
        if (!$this->repository instanceof RepositoryInterface) {
            throw new \Exception('Missing repository service');
        }

        return $this->repository;
    }

    /**
     * @return BaseEntityType|null
     */
    public function getForm(): ?BaseEntityType
    {
        return $this->form;
    }

    /**
     * @inheritDoc
     */
    public function remove(object $object)
    {
        $this->getObjectManager()->remove($object);
    }

    /**
     * @inheritDoc
     */
    public function persist(object $object)
    {

        if ($object instanceof TranslatableInterface) {
            $object->mergeNewTranslations();
        }

        $this->getObjectManager()->persist($object);
    }

    /**
     * @inheritDoc
     */
    public function flush()
    {
        $this->getObjectManager()->flush();
    }

    /**
     * @inheritDoc
     */
    public function createNew(): object
    {
        return $this->factory->createNew();
    }

    /**
     * @inheritDoc
     */
    public function doPersist(object $object, bool $throwException = true): bool
    {

        try {
            $this->persist($object);
            $this->flush();
            return true;
        } catch(\Exception $e) {
            if ($throwException) {
                throw new DoPersistException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function doRemove(object $object, bool $throwException = true): bool
    {
        try {
            $this->remove($object);
            $this->flush();
            return true;
        } catch(\Exception $e) {
            if ($throwException) {
                throw new DoRemoveException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }

        return false;
    }


}