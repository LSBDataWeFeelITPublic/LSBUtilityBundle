<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ObjectManager
 * @package LSB\UtilityBundle\Service
 */
class ObjectManager implements ObjectManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * @var ValidatorInterface
     */
    protected ValidatorInterface $validator;

    /**
     * ObjectManager constructor.
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ) {
        $this->em = $em;
        $this->validator = $validator;
    }

    /**
     * @param object $object
     */
    public function persist(object $object): void
    {
        $this->em->persist($object);
    }

    /**
     * @param object $object
     * @return iterable
     */
    public function validate(object $object): iterable
    {
        return $this->validator->validate($object);
    }

    /**
     * @param object $object
     */
    public function remove(object $object): void
    {
        $this->em->remove($object);
    }

    /**
     * @param object $object
     */
    public function refresh(object $object): void
    {
        $this->em->refresh($object);
    }

    /**
     * @return UnitOfWork
     */
    public function getUnitOfWork(): UnitOfWork
    {
        return $this->em->getUnitOfWork();
    }

    /**
     *
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