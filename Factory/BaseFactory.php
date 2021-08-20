<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Factory;

use LSB\UtilityBundle\Application\AppCodeTrait;
use LSB\UtilityBundle\Interfaces\UuidInterface;

/**
 * Class Factory
 * @package LSB\UtilityBundle\Factory
 */
abstract class BaseFactory implements FactoryInterface
{
    use AppCodeTrait;

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
        $object = new $this->className();

        if ($object instanceof UuidInterface) {
            $object->generateUuid();
        }

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function getClassName(): string
    {
        return $this->className;
    }

}