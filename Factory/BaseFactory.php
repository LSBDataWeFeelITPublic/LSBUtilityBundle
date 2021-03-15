<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Factory;

/**
 * Class Factory
 * @package LSB\UtilityBundle\Factory
 */
abstract class BaseFactory implements FactoryInterface
{
    /**
     * @var string FQCN
     */
    protected $className;

    /**
     * @inheritDoc
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * @inheritDoc
     */
    public function createNew(): object
    {
        return new $this->className();
    }

    /**
     * @inheritDoc
     */
    public function getClassName(): string
    {
        return $this->className;
    }

}