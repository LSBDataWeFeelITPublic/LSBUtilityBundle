<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Form\DataTransformer;

use LSB\UtilityBundle\Exception\ObjectManager\NotExistsException;
use LSB\UtilityBundle\Manager\ObjectManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Webmozart\Assert\Assert;

/**
 * Class EntityLazyTransformer
 * @package LSB\UtilityBundle\Form
 */
class EntityLazyTransformer implements DataTransformerInterface
{
    const PROPERTY_UUID = 'uuid';

    /**
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $om;

    /**
     * @var string
     */
    protected string $class;

    /**
     * @var string
     */
    protected string $property;

    /** @var bool */
    protected bool $validate;

    /** @var bool */
    protected bool $required = true;

    /**
     * @param ObjectManagerInterface $om
     * @param string $class
     * @param string $field
     * @param bool $validateUuid
     * @param bool $required
     */
    public function __construct(
        ObjectManagerInterface $om,
        string $class,
        string $field = self::PROPERTY_UUID,
        bool $validateUuid = false,
        bool $required = true
    ) {
        $this->om = $om;
        $this->class = $class;
        $this->property = $field;
        $this->validate = $validateUuid;
        $this->required = $required;
    }

    /**
     * @param $value
     * @return object|null
     * @throws NotExistsException
     */
    protected function getObject($value): ?object
    {
        if ($this->validate && $this->property === self::PROPERTY_UUID) {
            Assert::uuid($value);
        }


        $object = $this->om->getRepository($this->class)->findOneBy([$this->property => (string)$value]);

        if ($this->required && !$object instanceof $this->class) {
            throw new NotExistsException($value);
        }

        return $this->om->getRepository($this->class)->findOneBy([$this->property => (string)$value]);
    }

    /**
     * @param mixed $value
     * @return mixed|object|null
     * @throws NotExistsException
     */
    public function reverseTransform($value)
    {
        $result = null;

        if ($value && strlen($value) > 0) {
            $obj = $this->getObject($value);

            if (is_object($obj)) {
                $result = $obj;
            }
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function transform($value)
    {
        return $value;
    }
}
