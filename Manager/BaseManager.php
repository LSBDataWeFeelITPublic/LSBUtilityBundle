<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Manager;

use App\DTO\DataTransformer\Input\EntityConverter;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use LSB\NotificationBundle\Entity\Notification;
use LSB\UtilityBundle\Application\AppCodeTrait;
use LSB\UtilityBundle\DTO\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface;
use LSB\UtilityBundle\DTO\Request\RequestAttributes;
use LSB\UtilityBundle\DTO\Request\RequestIdentifier;
use LSB\UtilityBundle\Exception\ObjectManager\ValidationException;
use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Interfaces\UuidInterface;
use LSB\UtilityBundle\Repository\RepositoryInterface;
use LSB\UtilityBundle\DependencyInjection\BaseExtension as BE;
use LSB\UtilityBundle\Security\VoterSubjectInterface;
use ReflectionClass;
use Symfony\Component\Form\AbstractType;
use LSB\UtilityBundle\Exception\ObjectManager\DoRemoveException;
use LSB\UtilityBundle\Exception\ObjectManager\DoPersistException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * Class BaseManager
 * @package LSB\UtilityBundle\Service
 */
abstract class BaseManager implements ManagerInterface
{
    use AppCodeTrait;

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
     * @param object $object
     * @return iterable
     */
    public function validate(object $object): iterable
    {
        return $this->getObjectManager()->validate($object);
    }

    /**
     * @throws ValidationException
     */
    protected function checkValidation(object $object): void
    {
        $errors = $this->validate($object);
        $errorList = array(...$errors);

        if (count($errorList)) {
            throw new ValidationException($errors);
        }
    }

    /**
     * @param object $object
     * @throws \Exception
     */
    public function update(object $object)
    {
        $this->persist($object);
        $this->checkValidation($object);
        $this->flush();
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
            return (string)$this->resourceConfiguration[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_ENTITY];
        }

        throw new \Exception('Resource: Entity FQCN is not set.');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getResourceEntityInterface(): string
    {
        if (isset($this->resourceConfiguration[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_INTERFACE])) {
            return (string)$this->resourceConfiguration[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_INTERFACE];
        }

        throw new \Exception('Resource: Entity FQCN is not set.');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getResourceVoterSubjectClass(): string
    {
        if ($this->getAppCode()
            && isset($this->resourceConfiguration[BE::CONFIG_KEY_CONTEXT][$this->getAppCode()][BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_VOTER_SUBJECT])
            && $this->resourceConfiguration[BE::CONFIG_KEY_CONTEXT][$this->getAppCode()][BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_VOTER_SUBJECT]) {
            return (string)$this->resourceConfiguration[BE::CONFIG_KEY_CONTEXT][$this->getAppCode()][BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_VOTER_SUBJECT];
        } elseif (isset($this->resourceConfiguration[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_VOTER_SUBJECT]) && $this->resourceConfiguration[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_VOTER_SUBJECT]) {
            return (string)($this->resourceConfiguration[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_VOTER_SUBJECT]);
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
            return (string)$this->resourceConfiguration[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_FORM];
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

    /**
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function getById(int $id)
    {
        return $this->getRepository()->findOneBy(['id' => $id]);
    }

    /**
     * @param string $uuid
     * @return mixed
     */
    public function getByUuid(string $uuid)
    {
        try {
            Assert::uuid($uuid);
            return $this->getRepository()->findOneBy(['uuid' => $uuid]);
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * Do przeniesienia do Base
     *
     * @param $inputDTO
     * @return object
     * @throws \Exception
     */
    public function createNewFromDTO($inputDTO): object
    {
        if (!$inputDTO->isValid()) {
            throw new \Exception('DTO is invalid. Unable to create new object.');
        }

        $object = $this->factory->createNew();

        $entityConverter = new EntityConverter();
        $entityConverter->populateEntityWithDTO($inputDTO, $object);

        $this->getObjectManager()->persist($object);
        $this->getObjectManager()->flush();

        return $object;
    }

    /**
     * Do przeniesienia do Base
     *
     * @param $inputDTO
     * @return object
     * @throws \Exception
     */
    public function updateFromDTO($inputDTO): object
    {
        if (!$inputDTO->isValid()) {
            throw new \Exception('DTO is invalid. Unable to create new object.');
        }

        $object = $inputDTO->getEntity();

        $entityConverter = new EntityConverter();

        $entityConverter->populateEntityWithDTO($inputDTO, $object);
        //Persist is required
        $this->getObjectManager()->persist($object);
        $this->getObjectManager()->flush();

        return $object;
    }

    /**
     * @param \LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface $outputDTO
     * @param object $entity
     * @return \LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface
     */
    public function generateOutputDTO(OutputDTOInterface $outputDTO, object $entity): OutputDTOInterface
    {
        $entityConverter = new EntityConverter();
        $entityConverter->populateDtoWithEntity($entity, $outputDTO);

        return $outputDTO;
    }

    /**
     * At this stage, we populate the empty DTO object with data from the entity, create the default content of the DTO object, on which we will later overlay the data from the user
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function prepareInputDTO(
        RequestIdentifier $requestIdentifier,
        InputDTOInterface $requestDTO,
        bool $populate = true,
        bool $createNewObject = false
    ): InputDTOInterface {
        $idName = $requestIdentifier->getIdentifierName();
        $id = $requestIdentifier->getValue();
        $entityClass = $this->getResourceEntityClass();

        $reflectionClass = new ReflectionClass($entityClass);

        if ($reflectionClass->implementsInterface(UuidInterface::class)
            && $idName === RequestAttributes::IDENTIFIER_ATTRIBUTE_UUID
            && !is_numeric($id)
        ) {
            $object = $this->getByUuid($id);
        } else {
            $object = $this->getById($id);
        }

        //Verify object class
        if (!$createNewObject && !$object instanceof $entityClass) {
            throw new \Exception('Object does not exist.');
        } elseif ($createNewObject && !$object instanceof $entityClass) {
            $object = $this->factory->createNew();
        }

        if ($populate) {
            $entityConverter = new EntityConverter();
            $entityConverter->populateDtoWithEntity($object, $requestDTO);
        }

        $requestDTO->setEntity($object);

        return $requestDTO;
    }

    /**
     * @param object $object
     * @param \LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface $requestDTO
     * @param bool $populate
     * @return \LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface
     */
    public function prepareOutputDTO(
        object $object,
        OutputDTOInterface $requestDTO,
        bool $populate = true
    ): OutputDTOInterface {

        if ($populate) {
            $entityConverter = new EntityConverter();
            $entityConverter->populateDtoWithEntity($object, $requestDTO);
        }

        //The entity will be used later.
        $requestDTO->setEntity($object);

        return $requestDTO;
    }
}