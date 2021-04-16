<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Manager;

use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Repository\RepositoryInterface;
use LSB\UtilityBundle\DependencyInjection\BaseExtension as BE;
use LSB\UtilityBundle\Security\VoterSubjectInterface;
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
     * @var
     */
    protected $voterSubjectFqcn;

    /**
     * @var array
     */
    protected array $bundleConfiguration = [];

    /**
     * @var array
     */
    protected array $resourceConfiguration = [];

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
        ?BaseEntityType $form = null,
        array $configuration = [],
        array $resourceConfiguration = []
    ) {
        $this->objectManager = $objectManager;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->form = $form;
        $this->bundleConfiguration = $configuration;
        $this->resourceConfiguration = $resourceConfiguration;
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

    /**
     * @return array
     * @deprecated Use getBundleConfiguration()
     */
    public function getConfiguration(): array
    {
        return $this->getBundleConfiguration();
    }

    /**
     * @return array
     */
    public function getBundleConfiguration(): array
    {
        return $this->bundleConfiguration;
    }

    /**
     * @param array $bundleConfiguration
     * @return $this
     */
    public function setBundleConfiguration(array $bundleConfiguration): self
    {
        $this->bundleConfiguration = $bundleConfiguration;
        return $this;
    }

    /**
     * @param array $resourceConfiguration
     * @return $this
     */
    public function setResourceConfiguration(array $resourceConfiguration): self
    {
        $this->resourceConfiguration = $resourceConfiguration;
        return $this;
    }

    /**
     * @return array
     */
    public function getResourceConfiguration(): array
    {
        return $this->resourceConfiguration;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getResourceEntityClass(): string
    {
        if (isset($this->resourceConfiguration[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_ENTITY])) {
            return (string) $this->resourceConfiguration[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_ENTITY];
        }

        throw new \Exception('Resource: Entity FQCN is not set.');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getResourceVoterSubjectClass(): string
    {
        if (isset($this->resourceConfiguration[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_VOTER_SUBJECT])) {
            return (string) $this->resourceConfiguration[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_VOTER_SUBJECT];
        }

        throw new \Exception('Resource: Voter Subject FQCN is not set.');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getResourceFormClass(): string
    {
        if (isset($this->resourceConfiguration[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_FORM])) {
            return (string) $this->resourceConfiguration[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_FORM];
        }

        throw new \Exception('Resource: Voter Subject FQCN is not set.');
    }

    /**
     * @param mixed ...$args
     * @return VoterSubjectInterface
     * @throws \Exception
     */
    public function getVoterSubject(...$args): VoterSubjectInterface
    {
        $voterSubjectClass = $this->getResourceVoterSubjectClass();
        return new $voterSubjectClass(...$args);
    }

}