<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DTO\Resolver;

use JMS\Serializer\SerializerInterface;
use LSB\UtilityBundle\DTO\APIRequest\APIRequest;
use LSB\UtilityBundle\DTO\APIRequest\APIRequestInterface;
use LSB\UtilityBundle\DTO\Request\RequestAttributes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class APIRequestResolver implements ArgumentValueResolverInterface
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
            if ($reflection->implementsInterface(APIRequestInterface::class)) {
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
        //Jedynie po to, żeby usystematyzować to co ulokowane zostało w atrybutach requesta
        //Przepisanie do nowego obiektu APIRequest

        yield new APIRequest(
            $request,
            RequestAttributes::getOrCreateRequestData($request)
        );
    }
}