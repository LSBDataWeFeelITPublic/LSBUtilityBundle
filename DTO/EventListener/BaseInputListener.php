<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DTO\EventListener;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DTO\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\DTO\Request\RequestAttributes;
use LSB\UtilityBundle\DTO\Resource\ResourceHelper;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Serializer\ObjectConstructor\ExistingObjectConstructor;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseInputListener
{
    public function __construct(
        protected ManagerContainerInterface $managerContainer,
        protected ValidatorInterface        $validator,
        protected SerializerInterface       $serializer,
        protected ResourceHelper            $resourceHelper
    ) {
    }

    /**
     * @param RequestEvent $requestEvent
     * @return void|null
     * @throws \Exception
     */
    public function onKernelRequest(RequestEvent $requestEvent)
    {
        //Request object
        $request = $requestEvent->getRequest();

        //in the beginning we create RequestData object
        $requestData = RequestAttributes::getOrderCreateRequestData($request);
        $resource = $this->resourceHelper->fetchResource($request);
        $requestData->setResource($resource);

        if (!$resource instanceof Resource) {
            return null;
        }

        //Depending on the request method, we use a different approach to data processing
        switch ($request->getMethod()) {
            case Request::METHOD_POST:
                $inputDTO = $this->prepareInputDTO($request, $resource);

                if ($inputDTO->isValid()) {
                    $entity = $this->processInputDTO($inputDTO, $request, $resource);
                    $requestData->setEntity($entity);
                }

                $requestData->setInputDTO($inputDTO);

                RequestAttributes::updateRequestData($request, $requestData);
                break;
            case Request::METHOD_PATCH:
            case Request::METHOD_PUT:
                $inputDTO = $this->prepareInputDTO($request, $resource);

                if ($inputDTO->isValid()) {
                    $entity = $this->processInputDTO($inputDTO, $request, $resource);

                    if (!$requestData->getEntity()) {
                        $requestData->setEntity($entity);
                    }
                }
                $requestData->setInputDTO($inputDTO);

                RequestAttributes::updateRequestData($request, $requestData);
                break;
        }
    }

    protected function prepareInputDTO(Request $request, Resource $resource): ?InputDTOInterface
    {
        $dto = null;
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        $context = DeserializationContext::create();

        if (!$manager instanceof ManagerInterface) {
            //Temporarily exception will be thrown if manager will be not set
            throw new \Exception();
        }

        switch ($request->getMethod()) {
            case Request::METHOD_PATCH:
            case Request::METHOD_PUT:
                //We need to fetch entity with given UUID using class manager
                $dto = new ($resource->getInputUpdateDTOClass())();
                $requestIdentifier = RequestAttributes::getRequestIdentifier($request);
                $manager->prepareInputDTO(
                    $requestIdentifier,
                    $dto,
                    $request->getMethod() === Request::METHOD_PATCH,
                    $request->getMethod() === Request::METHOD_PUT
                );
                $context->setAttribute(ExistingObjectConstructor::ATTRIBUTE_TARGET, $dto);

                break;
        }


        //Mechanizm nakładania danych realnie wysłanych przez użytkownika na puste DTO lub zasilone danymi encji (tylko typ PATCH)
        if ($resource->getDeserializationType() === Resource::TYPE_DESERIALIZE) {
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                $resource->getInputCreateDTOClass(),
                $request->getFormat($request->headers->get('Content-Type')),
                $context
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

        return $dto;
    }

    /**
     * @param InputDTOInterface $inputDTO
     * @param Request $request
     * @param Resource $resource
     * @return object|null
     * @throws \Exception
     */
    protected function processInputDTO(InputDTOInterface $inputDTO, Request $request, Resource $resource): ?object
    {
        $processedEntity = null;
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());

        if (!$manager instanceof ManagerInterface) {
            //Temporarily exception will be thrown if manager will be not set
            throw new \Exception();
        }

        switch ($request->getMethod()) {
            case Request::METHOD_PATCH:
            case Request::METHOD_PUT:
                $processedEntity = $manager->updateFromDTO($inputDTO);
                break;
            case Request::METHOD_POST:
                $processedEntity = $manager->createNewFromDTO($inputDTO);
                break;
        }

        return $processedEntity;
    }
}