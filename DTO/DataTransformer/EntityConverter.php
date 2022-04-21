<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DTO\DataTransformer;

use Doctrine\Common\Collections\Collection;
use LSB\UtilityBundle\Attribute\ConvertToObject;
use LSB\UtilityBundle\Attribute\DTOPropertyConfig;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DTO\DTOService;
use LSB\UtilityBundle\DTO\Model\BaseDTO;
use LSB\UtilityBundle\DTO\Model\DTOInterface;
use LSB\UtilityBundle\DTO\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\DTO\Model\ObjectHolder;
use LSB\UtilityBundle\Interfaces\IdInterface;
use LSB\UtilityBundle\Interfaces\UuidInterface;
use LSB\UtilityBundle\Manager\ManagerInterface;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

class EntityConverter
{
    public static array $excludedProps = ['errors'];

    /**
     * @param InputDTOInterface $dto
     * @param object $targetObject
     * @param bool $convertIdsIntoEntities
     * @param \LSB\UtilityBundle\DTO\DTOService|null $DTOService
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string|null $appCode
     * @throws \Exception
     */
    public function populateEntityWithDTO(
        InputDTOInterface $dto,
        object $targetObject,
        bool $convertIdsIntoEntities = false,
        ?DTOService $DTOService = null,
        Request $request,
        ?string $appCode = null
    ) {
        $propertiesFilter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;

        $reflectionDTO = new ReflectionClass($dto);
        $reflectionProperties = $reflectionDTO->getProperties($propertiesFilter);

        foreach ($reflectionProperties as $reflectionProperty) {
            if (array_search($reflectionProperty->getName(), self::$excludedProps) !== false) {
                continue;
            }

            $DTOObjectGetters = [];
            $setterMethod = null;

            $DTOPropertyConfig = self::getDTOPropertyConfig($reflectionProperty);

            $DTOObjectGetters = [];

            if ($DTOPropertyConfig) {
                if ($DTOPropertyConfig->getDTOGetter()) {
                    $DTOObjectGetters = [$DTOPropertyConfig->getDTOGetter()];
                }

                if ($DTOPropertyConfig->getObjectSetter()) {
                    $setterMethod = $DTOPropertyConfig->getObjectSetter();
                }
            }

            if (count($DTOObjectGetters) === 0) {
                $DTOObjectGetters = $this->generateGetterNames($reflectionProperty->getName());
            }

            $setterMethod = ($setterMethod ?? null) ?: $this->generateSetterName($reflectionProperty->getName());


            foreach ($DTOObjectGetters as $DTOObjectGetter) {
                if (!method_exists($dto, $DTOObjectGetter)) {
                    continue;
                }


                if (method_exists($targetObject, $setterMethod)) {
                    $dtoValue = $dto->$DTOObjectGetter();
                    if ($this->hasConvertToObjectAttribute($reflectionProperty)) {
                        $objectHolder = $this->convertValueToObjectHolder($DTOService, $reflectionProperty, $dto, $DTOObjectGetter, $request);

                        if ($objectHolder instanceof ObjectHolder && $objectHolder->getObject()) {
                            $targetObject->$setterMethod($objectHolder->getObject());
                        } else {
                            $targetObject->$setterMethod(null);
                        }
                    } else {
                        $targetObject->$setterMethod($dtoValue);
                    }



                }

                break;
            }
        }
    }

    /**
     * Support for DTO autofilling for DTO and Entity coexisting properties
     * Used by InputDTO and OutputDTO objects
     *
     * @param object $targetObject
     * @param \LSB\UtilityBundle\DTO\Model\BaseDTO $requestDTO
     * @param \LSB\UtilityBundle\DTO\DTOService|null $DTOService
     * @param \LSB\UtilityBundle\Attribute\Resource|null $resource
     * @throws \Exception
     */
    public function populateDtoWithEntity(object $targetObject, BaseDTO $requestDTO, ?DTOService $DTOService = null, ?Resource $resource = null)
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
                    //dump("Istnieje setter w DTO". $prop->getName());
                    $value = $targetObject->$targetObjectGetter();
                    // Flattening mechanism, disabled
//                    if (is_object($value)) {
//                        if ($value instanceof Uuid) {
//                            $value = (string)$value;
//                        } elseif ($value instanceof UuidInterface) {
//                            $value = (string)$value->getUuid();
//                        } elseif ($value instanceof IdInterface) {
//                            $value = (int)$value->getId();
//                        } elseif ($value instanceof Collection) {
//                            $flatValue = [];
//
//                            foreach ($value as $valum) {
//                                $flatValue = $valum instanceof UuidInterface ? $valum->getUuid() : $valum->getId();
//                            }
//
//                            $value = $flatValue;
//                        }
//                    }

                    if (!is_object($value) && !is_iterable($value)) {
                        $requestDTO->$setterMethod($value);
                    } elseif (is_object($value)) {
                        $valueClass = $DTOService->getRealClass($value);
                        $itemResource = $DTOService->getResourceByEntity($valueClass, true);

                        if (!$itemResource instanceof Resource) {
                            throw new \Exception('Missing Resource attribute for class ' . $valueClass);
                        }


                        $valueDTO = $DTOService->generateOutputDTO($itemResource, null, $value);
                        $objectHolder = new ObjectHolder();

                        $requestDTO->$setterMethod($valueDTO);
                    } elseif (is_iterable($value)) {
                        $array = [];

                        foreach ($value as $item) {
                            $itemResource = $DTOService->getResourceByEntity($valueClass = $DTOService->getRealClass($item), true);
                            $itemDTO = $DTOService->generateOutputDTO($itemResource, null, $item);
                            $array[] = $itemDTO;
                        }

                        $requestDTO->$setterMethod($array);
                    }

                }

                break 1;
            }
        }
    }

    /**
     * @param string $propertyName
     * @return string
     */
    public static function generateSetterName(string $propertyName): string
    {
        return 'set' . self::classify($propertyName);
    }

    /**
     * @param string $propertyName
     * @return array
     */
    public static function generateGetterNames(string $propertyName): array
    {
        $prefixes = ['get', 'is'];

        $setterNames = [];

        foreach ($prefixes as $prefix) {
            $setterNames[] = $prefix . self::classify($propertyName);
        }

        return $setterNames;
    }

    /**
     * @param string $word
     * @return string
     */
    public static function camelize(string $word): string
    {
        return lcfirst(self::classify($word));
    }

    /**
     * @param string $word
     * @return string
     */
    public static function classify(string $word): string
    {
        return str_replace([' ', '_', '-'], '', ucwords($word, ' _-'));
    }

    /**
     * @param \ReflectionProperty $reflectionProperty
     * @return bool
     */
    protected function hasConvertToObjectAttribute(ReflectionProperty $reflectionProperty): bool
    {
        $attributes = $reflectionProperty->getAttributes(ConvertToObject::class);

        if (count($attributes) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param \LSB\UtilityBundle\DTO\DTOService $DTOService
     * @param \ReflectionProperty $reflectionProperty
     * @param \LSB\UtilityBundle\DTO\Model\DTOInterface $dto
     * @param string $getterName
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string|null $appCode
     * @return \LSB\UtilityBundle\DTO\Model\ObjectHolder|null
     * @throws \Exception
     */
    public function convertValueToObjectHolder(DTOService $DTOService, ReflectionProperty $reflectionProperty, DTOInterface $dto, string $getterName, Request $request, ?string $appCode = null): ?ObjectHolder
    {
            $attributes = $reflectionProperty->getAttributes(ConvertToObject::class);

            if (count($attributes) === 0) {
                return null;
            }

            //Other ConverToObject should be ignored
            /** @var ConvertToObject $convertToObjectAttribute */
            $convertToObjectAttribute = $attributes[0]->newInstance();

            if ($convertToObjectAttribute->getManagerClass()) {
                $manager = $DTOService->getManagerContainer()->getByManagerClass($convertToObjectAttribute->getManagerClass());

                if (!$manager instanceof ManagerInterface) {
                    return null;
                }

                switch($convertToObjectAttribute->getKey()) {
                    case ConvertToObject::KEY_ID:
                        $id = (int) $dto->$getterName();
                        $object = $manager->getById($id);
                        break;
                    case ConvertToObject::KEY_UUID:
                        $id = (string) $dto->$getterName();
                        try {
                            Assert::uuid($id);
                        } catch (\Exception $e) {
                            return null;
                        }

                        $object = $manager->getByUuid($id);
                        break;
                    default:
                        $id = null;
                        $object = null;
                }

                if ($id && $object) {
                    //Optional type check
                    if ($convertToObjectAttribute->getObjectClass() && !$object instanceof  ($convertToObjectAttribute->getObjectClass())) {
                        throw new \Exception(sprintf("Wrong type. Expected %s got %s", $convertToObjectAttribute->getObjectClass(), get_class($object)));
                    }

                    //Optional security check
                    if ($convertToObjectAttribute->getVoterAction()) {
                        if (!$DTOService->isSubjectGranted($convertToObjectAttribute->getManagerClass(), $request, $convertToObjectAttribute->getVoterAction(), $object)) {
                            throw new \Exception(sprintf("Access denied. Class: %s, object: %s", $convertToObjectAttribute->getObjectClass(), $id));
                        }
                    }

                    return new ObjectHolder($id, $object);
                } elseif ($id && !$object && $convertToObjectAttribute->isThrowNotFoundException()) {
                    throw new \Exception(sprintf("Property: %s; Object %s has not been found. ", $reflectionProperty->getName(), $id));
                }

            }

        return null;
    }

    /**
     * @param \ReflectionProperty $reflectionProperty
     * @return \LSB\UtilityBundle\Attribute\DTOPropertyConfig|null
     */
    public function getDTOPropertyConfig(ReflectionProperty $reflectionProperty): ?DTOPropertyConfig
    {
        $attributes = $reflectionProperty->getAttributes(DTOPropertyConfig::class);

        if (count($attributes) === 0) {
            return null;
        }

        /**
         * @var DTOPropertyConfig $attribute
         */
        $attribute = $attributes[0]->newInstance();
        return $attribute instanceof DTOPropertyConfig ? $attribute : null;
    }
}