<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Calculator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use LSB\UtilityBundle\Interfaces\TotalCalculatorInterface;
use LSB\UtilityBundle\Interfaces\TotalCalculatorRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class BaseTotalCalculator
 * @package LSB\UtilityBundle\Calculator
 */
abstract class BaseTotalCalculator implements TotalCalculatorInterface
{

    public const NAME = 'default';

    protected const SUPPORTED_CLASS = 'abstractClass';

    protected const SUPPORTED_POSITION_CLASS = 'abstractPositionClass';

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var array
     */
    protected $calculationData;

    /**
     * @var array
     */
    protected $attributes = [];

//    /**
//     * @var TotalCalculatorManager
//     */
//    protected $totalCalculatorManager;

    /**
     * BaseTotalCalculator constructor.
     * @param EntityManagerInterface $em
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
    }

//    /**
//     * @param TotalCalculatorManager $totalCalculatorManager
//     */
//    public function setTotalCalculatorManager(TotalCalculatorManager $totalCalculatorManager): void
//    {
//        $this->totalCalculatorManager = $totalCalculatorManager;
//    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * @return mixed|string
     */
    public function getSupportedClass()
    {
        return static::SUPPORTED_CLASS;
    }

    /**
     * @return mixed|string
     */
    public function getSupportedPositionClass()
    {
        return static::SUPPORTED_POSITION_CLASS;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     * @throws \Exception
     */
    public function getSupportedClassRepository()
    {
        if (!$this->getSupportedClass()) {
            throw new \Exception('Missing supported class. Please set supported class in calculator class.');
        }

        $repositoryClass = $this->em->getRepository($this->getSupportedClass());

        return $repositoryClass;
    }

    /**
     * @return EntityRepository
     * @throws \Exception
     */
    public function getSupportedPositionClassRepository(): EntityRepository
    {
        if (!$this->getSupportedPositionClass()) {
            throw new \Exception('Missing position class. Please set supported position class in calculator class.');
        }

        $repositoryClass = $this->em->getRepository($this->getSupportedPositionClass());

        if (!$repositoryClass instanceof TotalCalculatorRepositoryInterface) {
            throw new \Exception('Repository class does not support fetching positions');
        }

        return $repositoryClass;
    }

    /**
     * @param array $attributes
     * @return mixed|void
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getSupportedClass().' '.$this->getName();
    }

}
