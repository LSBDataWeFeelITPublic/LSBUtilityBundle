<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DTO\EventListener;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DTO\DataTransformer\DataTransformerService;
use LSB\UtilityBundle\DTO\DTOService;
use LSB\UtilityBundle\DTO\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\DTO\Request\RequestAttributes;
use LSB\UtilityBundle\DTO\Resource\ResourceHelper;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Security\BaseObjectVoter;
use LSB\UtilityBundle\Serializer\ObjectConstructor\ExistingObjectConstructor;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseInputListener extends BaseListener
{
    public function __construct(
        protected ManagerContainerInterface     $managerContainer,
        protected ValidatorInterface            $validator,
        protected SerializerInterface           $serializer,
        protected ResourceHelper                $resourceHelper,
        protected DataTransformerService        $dataTransformerService,
        protected DTOService                    $DTOService,
        protected AuthorizationCheckerInterface $authorizationChecker
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

        if (!$resource instanceof Resource || $resource->getisDisabled() === true) {
            return null;
        }

        //Depending on the request method, we use a different approach to data processing
        switch ($request->getMethod()) {
            case Request::METHOD_POST:
                if ($this->DTOService->isGranted($resource, $request, BaseObjectVoter::ACTION_POST, null)) {
                    $requestData->setIsGranted(true);
                } else {
                    break;
                }

                $inputDTO = $this->prepareInputDTO($request, $resource);

                if ($inputDTO->isValid()) {
                    $object = $this->DTOService->createNewFromDTO($resource, $inputDTO, $request);
                    $requestData->setObject($object);
                }

                $requestData->setInputDTO($inputDTO);
                break;
            case Request::METHOD_PATCH:
            case Request::METHOD_PUT:
                $inputDTO = $this->prepareInputDTO($request, $resource);

                if ($this->DTOService->isGranted($resource, $request, BaseObjectVoter::ACTION_POST, $inputDTO->getEntity())) {
                    $requestData->setIsGranted(true);
                } else {
                    break;
                }

                if ($inputDTO->isValid()) {

                    $object = $this->DTOService->updateFromDTO($resource, $inputDTO, $request, $this->DTOService->getAppCode($request));

                    if (!$requestData->getObject()) {
                        $requestData->setObject($object);
                    }
                }
                $requestData->setInputDTO($inputDTO);
                break;
            case Request::METHOD_DELETE:
                if (!$requestData->getObject()) {
                    $object = $this->prepareObject($resource, $request);
                    $requestData->setObject($object);
                }

                if ($requestData->getObject() && $this->DTOService->isGranted($resource, $request, BaseObjectVoter::ACTION_DELETE, $requestData->getObject())) {
                    $requestData->setIsGranted(true);
                } else {
                    break;
                }

                $this->DTOService->remove($resource, $requestData->getObject());

                break;
            case Request::METHOD_GET:
                //Collection
                if ($requestData->getResource()->getIsCollection()) {

                    if (!$this->DTOService->isGranted($resource, $request, BaseObjectVoter::ACTION_CGET, $requestData->getObject())) {
                        $requestData->setIsGranted(false);
                        break;
                    }

                    $collection = $this->DTOService->paginateCollection($resource, $request);
                    $requestData->setIsGranted($this->DTOService->checkCollection($resource, $request, $collection, BaseObjectVoter::ACTION_GET));
                    $requestData->setObject($collection);
                } else {
                    // Single object
                    if (!$requestData->getObject()) {
                        $object = $this->prepareObject($resource, $request);
                        $requestData->setObject($object);
                    }

                    if ($requestData->getObject() && $this->DTOService->isGranted($resource, $request, BaseObjectVoter::ACTION_GET, $requestData->getObject())) {
                        $requestData->setIsGranted(true);
                    } else {
                        break;
                    }
                }
                break;
        }

        RequestAttributes::updateRequestData($request, $requestData);
    }

    /**
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return object|null
     * @throws \ReflectionException
     */
    protected function prepareObject(Resource $resource, Request $request): ?object
    {
        return $this->DTOService->getObjectByRequestId($resource, RequestAttributes::getRequestIdentifier($request));
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
                $dto = $this->DTOService->prepareInputDTO(
                    $resource,
                    $requestIdentifier,
                    $dto,
                    $request->getMethod() === Request::METHOD_PATCH,
                    $request->getMethod() === Request::METHOD_PUT
                );
                $context->setAttribute(ExistingObjectConstructor::ATTRIBUTE_TARGET, $dto);
                break;
        }

        $dto = $this->serializer->deserialize(
            $request->getContent(),
            $resource->getInputCreateDTOClass(),
            $request->getFormat($request->headers->get('Content-Type')),
            $context
        );

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
}