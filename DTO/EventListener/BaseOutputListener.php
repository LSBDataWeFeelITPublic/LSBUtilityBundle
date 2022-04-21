<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DTO\EventListener;

use JMS\Serializer\SerializerInterface;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\Controller\BaseCRUDApiController;
use LSB\UtilityBundle\DTO\DTOService;
use LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface;
use LSB\UtilityBundle\DTO\Request\RequestAttributes;
use LSB\UtilityBundle\DTO\Request\RequestData;
use LSB\UtilityBundle\DTO\Resource\ResourceHelper;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use JMS\Serializer\SerializationContext;

abstract class BaseOutputListener extends BaseListener
{
    public function __construct(
        protected ManagerContainerInterface $managerContainer,
        protected ValidatorInterface        $validator,
        protected SerializerInterface       $serializer,
        protected ResourceHelper            $resourceHelper,
        protected DTOService                $DTOService
    ) {
    }

    /**
     * Priority of this listener should be less than fos rest listener priority, otherwise it won't work.
     *
     * @param ViewEvent $event
     * @throws \Exception
     */
    public function onKernelView(ViewEvent $event)
    {

        if (!$event->getRequest()) {
            return;
        }

        $result = null;
        $statusCode = Response::HTTP_BAD_REQUEST;

        $requestData = RequestAttributes::getOrderCreateRequestData($event->getRequest());

        if (!$requestData->getResource() || $requestData->getResource()->getIsDisabled() === true) {
            return;
        }




        switch ($event->getRequest()->getMethod()) {
            case Request::METHOD_PATCH:
                if (!$requestData->isObjectFetched()) {
                    [$result, $statusCode] = $this->prepareNotFoundResponse();
                    break;
                }

                if (!$requestData->isGranted()) {
                    [$result, $statusCode] = $this->prepareNotGrantedResponse();
                    break;
                }

                if ($requestData->getInputDTO() && !$requestData->getInputDTO()->isValid()) {
                    $result = $requestData->getInputDTO();
                    break;
                }

                $requestData->setOutputDTO(
                    $this->processOutputDTO($requestData->getObject(), $requestData->getOutputDTO(), $event->getRequest(), $requestData->getResource())
                );

                // Czy tu nie powinno być getOutputDTO() ?
                $result = $requestData->getOutputDTO();
                $statusCode = Response::HTTP_OK;
                break;
            case Request::METHOD_POST:
            case Request::METHOD_PUT:
                if (!$requestData->isGranted()) {
                    [$result, $statusCode] = $this->prepareNotGrantedResponse();
                    break;
                }

                if ($requestData->getInputDTO() && !$requestData->getInputDTO()->isValid()) {
                    $result = $requestData->getInputDTO();
                    break;
                }
                $statusCode = Response::HTTP_NO_CONTENT;
                break;
            case Request::METHOD_GET:
                if ($requestData->getResource()->getIsCollection()) {
                    if (!$requestData->isGranted()) {
                        [$result, $statusCode] = $this->prepareNotGrantedResponse();
                        break;
                    }

                    $requestData->setOutputDTO(
                        $this->processOutputDTO($requestData->getObject(), $requestData->getOutputDTO(), $event->getRequest(), $requestData->getResource())
                    );
                } else {
                    if (!$requestData->isGranted()) {
                        [$result, $statusCode] = $this->prepareNotGrantedResponse();
                        break;
                    }

                    if (!$requestData->isObjectFetched()) {
                        [$result, $statusCode] = $this->prepareNotFoundResponse();
                        break;
                    }

                    $requestData->setOutputDTO(
                        $this->processOutputDTO($requestData->getObject(), $requestData->getOutputDTO(), $event->getRequest(), $requestData->getResource())
                    );
                }


                if ($requestData->getOutputDTO() && $requestData->getOutputDTO()->isValid()) {
                    $result = $requestData->getOutputDTO();
                    $statusCode = Response::HTTP_OK;
                    break;
                }

                break;
            case Request::METHOD_DELETE:
                if (!$requestData->isObjectFetched()) {
                    [$result, $statusCode] = $this->prepareNotFoundResponse();
                    break;
                }

                if (!$requestData->isGranted()) {
                    [$result, $statusCode] = $this->prepareNotGrantedResponse();
                    break;
                }

                $statusCode = Response::HTTP_OK;
                break;
            default:
                return;
        }


        $context = new SerializationContext();
        $context
            ->setVersion('1.0')
            ->setSerializeNull(true);

        if (count($requestData->getSerializationGroups()) === 0) {
            $groups[] = BaseCRUDApiController::DEFAULT_SERIALIZATION_GROUP;
        }

        $context->setGroups($groups);

        $response = (new Response)
            ->setContent($this->serializer->serialize($result, $event->getRequest()->getContentType(), $context))
            ->setStatusCode($statusCode);

        $event->setResponse($response);
    }

    protected function prepareNotGrantedResponse(): array
    {
        $result = ['error' => 'Access denied.'];
        $statusCode = Response::HTTP_FORBIDDEN;

        return [$result, $statusCode];
    }

    protected function prepareNotFoundResponse(): array
    {
        $result = ['error' => 'Object not found.'];
        $statusCode = Response::HTTP_NOT_FOUND;

        return [$result, $statusCode];
    }

    /**
     * @param object|null $object
     * @param \LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface|null $outputDTO
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @return object|null
     * @throws \Exception
     */
    protected function processOutputDTO(
        ?object             $object,
        ?OutputDTOInterface $outputDTO,
        Request             $request,
        Resource            $resource
    ): ?object {
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());

        if (!$outputDTO) {
            if ($resource->getIsCollection()) {
                $outputDTO = new ($resource->getCollectionOutputDTOClass())();
            } else {
                $outputDTO = new ($resource->getOutputDTOClass())();
            }
        }

        if (!$manager instanceof ManagerInterface) {
            //Temporarily exception will be thrown if manager will be not set
            throw new \Exception();
        }

        switch ($request->getMethod()) {
            case Request::METHOD_GET:
            case Request::METHOD_PATCH:
            case Request::METHOD_PUT:
                if ($object) {
                    $outputDTO = $this->DTOService->generateOutputDTO($resource, $outputDTO, $object);
                }

                break;
        }

        return $outputDTO;
    }
}