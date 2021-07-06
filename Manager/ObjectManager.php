<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
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
     * Persist
     *
     * @param $object
     */
    public function persist(object $object): void
    {
        $this->em->persist($object);
    }

    /**
     * @param object $object
     * @return array
     */
    public function validate(object $object): iterable
    {
        return $this->validator->validate($object);
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