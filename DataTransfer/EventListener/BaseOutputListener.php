<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DataTransfer\EventListener;

use JMS\Serializer\SerializerInterface;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\Controller\BaseCRUDApiController;
use LSB\UtilityBundle\DataTransfer\DTOService;
use LSB\UtilityBundle\DataTransfer\Helper\CRUD\Route\RouteGeneratorInterface;
use LSB\UtilityBundle\DataTransfer\Model\Output\OutputDTOInterface;
use LSB\UtilityBundle\DataTransfer\Request\RequestAttributes;
use LSB\UtilityBundle\DataTransfer\Resource\ResourceHelper;
use LSB\UtilityBundle\Service\ApiVersionGrabber;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use JMS\Serializer\SerializationContext;

abstract class BaseOutputListener extends BaseListener
{
    const CONTENT_TYPE_DEFAULT = 'json';

    public function __construct(
        protected ManagerContainerInterface $managerContainer,
        protected ValidatorInterface        $validator,
        protected SerializerInterface       $serializer,
        protected ResourceHelper            $resourceHelper,
        protected DTOService                $DTOService,
        protected ApiVersionGrabber         $apiVersionGrabber,
        protected RouteGeneratorInterface   $newResourceGetRouteGenerator
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
        $newResourceUrl = null;
        $statusCode = Response::HTTP_NO_CONTENT;

        $apiVersionNumeric = $this->apiVersionGrabber->getVersion($event->getRequest(), true);

        $requestData = RequestAttributes::getOrCreateRequestData($event->getRequest());

        if (!$requestData->getResource() || $requestData->getResource()->getIsDisabled() === true) {
            return;
        }

        if (!$requestData->getResource()->getIsActionDisabled()) {
            if ($requestData->getResource()->getIsCRUD()) {
                switch ($event->getRequest()->getMethod()) {
                    case Request::METHOD_PATCH:

                        if (!$requestData->isGranted()) {
                            [$result, $statusCode] = $this->prepareNotGrantedResponse();
                            break;
                        }

                        if ($requestData->getInputDTO() && !$requestData->getInputDTO()->isValid()) {
                            $result = $requestData->getInputDTO();
                            $statusCode = Response::HTTP_BAD_REQUEST;
                            break;
                        }

                        if (!$requestData->isObjectFetched()) {
                            [$result, $statusCode] = $this->prepareNotFoundResponse();
                            break;
                        }

                        $requestData->setOutputDTO(
                            $this->processOutputDTO($requestData->getObject(), $requestData->getOutputDTO(), $event->getRequest(), $requestData->getResource())
                        );

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
                            $statusCode = Response::HTTP_BAD_REQUEST;
                            break;
                        }

                        $newResourceUrl = $this->getGetRouteForCrudAction($event->getRequest());
                        $statusCode = $requestData->isObjectCreated() ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;
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
                        $statusCode = Response::HTTP_NO_CONTENT;
                        $result = '';
                        return;
                }
            } else {
                //Non CRUD actions
                switch ($event->getRequest()->getMethod()) {
                    case Request::METHOD_GET:
                    case Request::METHOD_POST:
                    case Request::METHOD_PUT:
                    case Request::METHOD_PATCH:
                    case Request::METHOD_DELETE:
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

                        $result = $requestData->getOutputDTO();
                        $statusCode = Response::HTTP_OK;
                        break;
                    default:
                        $statusCode = Response::HTTP_NO_CONTENT;
                        $result = '';
                        break;
                }
            }
        } else {
            if ($requestData->getResponseContent() && $requestData->getResponseStatusCode() === null) {
                $statusCode = Response::HTTP_OK;
            }
        }

        $context = new SerializationContext();
        $context
            ->setVersion($apiVersionNumeric)
            ->setSerializeNull(true);

        if (count($requestData->getSerializationGroups()) === 0) {
            $groups[] = BaseCRUDApiController::DEFAULT_SERIALIZATION_GROUP;
        } else {
            $groups = $requestData->getSerializationGroups();
        }

        $context->setGroups($groups);

        $result = $requestData->getResponseContent() ?? $result;

        $response = (new Response)
            ->setStatusCode($requestData->getResponseStatusCode() ?? $statusCode);

        if ($result !== null) {
            $response
                ->setContent($this->serializer->serialize($result, $event->getRequest()->getContentType() ?? self::CONTENT_TYPE_DEFAULT, $context));
        }
        if ($newResourceUrl) {
            $response->headers->set('Location', $newResourceUrl);
        }

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
     * @param \LSB\UtilityBundle\DataTransfer\Model\Output\OutputDTOInterface|null $outputDTO
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
        if ($resource->getIsActionDisabled()) {
            return null;
        }

        if (!$outputDTO) {
            if ($resource->getIsCollection()) {
                if (!$resource->getCollectionOutputDTOClass()) {
                    throw new \Exception('Missing output DTO class for collection.');
                }

                $outputDTO = new ($resource->getCollectionOutputDTOClass())();
            } else {
                if (!$resource->getOutputDTOClass()) {
                    throw new \Exception('Missing output DTO class.');
                }

                $outputDTO = new ($resource->getOutputDTOClass())();
            }
        }

        if ($resource->getIsCRUD()) {
            switch ($request->getMethod()) {
                case Request::METHOD_GET:
                case Request::METHOD_PATCH:
                case Request::METHOD_PUT:
                    if ($object) {
                        $outputDTO = $this->DTOService->generateOutputDTO($resource, $object, $outputDTO);
                    }
                    break;
            }
        } else {
            switch ($request->getMethod()) {
                case Request::METHOD_POST:
                case Request::METHOD_GET:
                case Request::METHOD_PATCH:
                case Request::METHOD_PUT:
                case Request::METHOD_DELETE:
                    if ($object) {
                        $outputDTO = $this->DTOService->generateOutputDTO($resource, $object, $outputDTO);
                    }
                    break;
            }
        }


        return $outputDTO;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string|null
     */
    protected function getGetRouteForCrudAction(Request $request): ?string
    {
        return $this->newResourceGetRouteGenerator->getPath($request);
    }
}