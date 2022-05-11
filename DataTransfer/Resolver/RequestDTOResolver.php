<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DataTransfer\Resolver;

use JMS\Serializer\SerializerInterface;
use LSB\UtilityBundle\Attribute\Deserialize;
use LSB\UtilityBundle\DataTransfer\Model\Input\InputDTOInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestDTOResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        protected ValidatorInterface  $validator,
        protected SerializerInterface $serializer
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        try {
            $reflection = new \ReflectionClass($argument->getType());
            if ($reflection->implementsInterface(InputDTOInterface::class)) {
                return true;
            }
        } catch (\Exception $e) {

        }

        return false;
    }

    /**
     * @throws \ReflectionException
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $deserializationType = Deserialize::TYPE_MANUAL;
        $throwException = false;

        $reflectionClass = new ReflectionClass($argument->getType());
        $attributes = $reflectionClass->getAttributes(Deserialize::class);

        /**
         * @var Deserialize $attribute
         */
        foreach ($attributes as $attribute) {
            if ($attribute->getName() !== Deserialize::class) {
                continue;
            }

            $attribute = $attribute->newInstance();
            $deserializationType = $attribute->getType();
            $throwException = $attribute->isThrowException();
            break;
        }


        /**
         * @var InputDTOInterface $dto
         */
        if ($deserializationType == Deserialize::TYPE_MANUAL) {
            //1 approach
            $class = $argument->getType();
            $dto = new $class($request);
        } else {
            //2 approach
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                $argument->getType(),
                $request->getFormat($request->headers->get('Content-Type'))
            );

        }

        /**
         * @var ConstraintViolationList $error
         */
        $errors = $this->validator->validate($dto);

        /**
         * @var ConstraintViolation $error
         */
        foreach ($errors as $error) {
            $dto->addError($error->getPropertyPath(), $error->getMessage());
        }

        if ($throwException && count($errors) > 0) {
            throw new BadRequestHttpException();
        }

        yield $dto;
    }
}