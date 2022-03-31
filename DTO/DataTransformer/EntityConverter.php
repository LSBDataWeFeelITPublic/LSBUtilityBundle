<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DTO\DataTransformer;

use Doctrine\Common\Collections\Collection;
use LSB\UtilityBundle\DTO\Model\BaseDTO;
use LSB\UtilityBundle\DTO\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\Interfaces\IdInterface;
use LSB\UtilityBundle\Interfaces\UuidInterface;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use ReflectionProperty;

class EntityConverter
{
    public static array $excludedProps = ['errors'];

    /**
     * @param InputDTOInterface $requestDTO
     * @param object $targetObject
     */
    public function populateEntityWithDTO(InputDTOInterface $requestDTO, object $targetObject)
    {
        $propertiesFilter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;

        $reflectionDTO = new ReflectionClass($requestDTO);
        $props = $reflectionDTO->getProperties($propertiesFilter);

        foreach ($props as $prop) {
            if (array_search($prop->getName(), self::$excludedProps) !== false) {
                continue;
            }

            $DTOobjectGetters = $this->generateGetterNames($prop->getName());

            foreach ($DTOobjectGetters as $objectGetter) {
                if (!method_exists($requestDTO, $objectGetter)) {
                    continue;
                }

                //Sprawdzmy istnienie settera
                $setterMethod = $this->generateSetterName($prop->getName());

                if (method_exists($targetObject, $setterMethod)) {
                    $targetObject->$setterMethod($requestDTO->$objectGetter());
                }

                break;
            }
        }
    }

    /**
     * Support for DTO autofilling for DTO and Entity coexisting properties
     *
     * @param object $targetObject
     * @param \LSB\UtilityBundle\DTO\Model\BaseDTO $requestDTO
     */
    public function populateDtoWithEntity(object $targetObject, BaseDTO $requestDTO)
    {
        $propertiesFilter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;

        $reflectionDTO = new ReflectionClass($requestDTO);
        $props = $reflectionDTO->getProperties($propertiesFilter);

        foreach ($props as $prop) {
            if (array_search($prop->getName(), self::$excludedProps) !== false) {
                continue;
            }

            $targetObjectGetters = $this->generateGetterNames($prop->getName());

            foreach ($targetObjectGetters as $targetObjectGetter) {
                if (!method_exists($targetObject, $targetObjectGetter)) {
                    continue;
                }

                //Sprawdźmy istnienie settera
                $setterMethod = $this->generateSetterName($prop->getName());

                if (method_exists($requestDTO, $setterMethod)) {
                    $value = $targetObject->$targetObjectGetter();

                    if (is_object($value)) {
                        if ($value instanceof Uuid) {
                            $value = (string)$value;
                        } elseif ($value instanceof UuidInterface) {
                            $value = (string)$value->getUuid();
                        } elseif ($value instanceof IdInterface) {
                            $value = (int)$value->getId();
                        } elseif ($value instanceof Collection) {
                            $flatValue = [];

                            foreach ($value as $valum) {
                                $flatValue = $valum instanceof UuidInterface ? $valum->getUuid() : $valum->getId();
                            }

                            $value = $flatValue;
                        }
                    }

                    $requestDTO->$setterMethod($value);
                }

                break 1;
            }
        }
    }

    /**
     * @param string $propertyName
     * @return string
     */
    protected function generateSetterName(string $propertyName): string
    {
        return 'set' . $this->classify($propertyName);
    }

    /**
     * @param string $propertyName
     * @return array
     */
    protected function generateGetterNames(string $propertyName): array
    {
        $prefixes = ['get', 'is'];

        $setterNames = [];

        foreach ($prefixes as $prefix) {
            $setterNames[] = $prefix . $this->classify($propertyName);
        }

        return $setterNames;
    }

    /**
     * @param string $word
     * @return string
     */
    public function camelize(string $word): string
    {
        return lcfirst($this->classify($word));
    }

    /**
     * @param string $word
     * @return string
     */
    public function classify(string $word): string
    {
        return str_replace([' ', '_', '-'], '', ucwords($word, ' _-'));
    }
}