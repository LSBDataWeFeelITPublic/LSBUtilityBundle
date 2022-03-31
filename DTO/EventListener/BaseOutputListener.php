<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DTO\EventListener;

use JMS\Serializer\SerializerInterface;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\Controller\BaseCRUDApiController;
use LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface;
use LSB\UtilityBundle\DTO\Request\RequestAttributes;
use LSB\UtilityBundle\DTO\Resource\ResourceHelper;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use JMS\Serializer\SerializationContext;

abstract class BaseOutputListener
{
    public function __construct(
        protected ManagerContainerInterface $managerContainer,
        protected ValidatorInterface        $validator,
        protected SerializerInterface       $serializer,
        protected ResourceHelper            $resourceHelper
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

        $resourceHelper = $this->resourceHelper;
        // chyba zbędne
        //$resource = $resourceHelper($event->getRequest());
        $requestData = RequestAttributes::getOrderCreateRequestData($event->getRequest());

        if (!$requestData->getResource()) {
            //Brak danych resource request
            return;
        }

        $requestData->setOutputDTO(
            $this->processOutputDTO($requestData->getEntity(), $requestData->getOutputDTO(), $event->getRequest(), $requestData->getResource())
        );

        switch ($event->getRequest()->getMethod()) {
            case Request::METHOD_PATCH:
                if ($requestData->getInputDTO() && !$requestData->getInputDTO()->isValid()) {
                    $result = $requestData->getInputDTO();
                    break;
                }

                // Czy tu nie powinno być getOutputDTO() ?
                $result = $requestData->getOutputDTO();
                $statusCode = Response::HTTP_OK;
                break;
            case Request::METHOD_POST:
            case Request::METHOD_PUT:
                if ($requestData->getInputDTO() && !$requestData->getInputDTO()->isValid()) {
                    $result = $requestData->getInputDTO();
                }
                break;
            case Request::METHOD_GET:
                $result = $requestData->getOutputDTO();
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

    /**
     * @param object|null $entity
     * @param \LSB\UtilityBundle\DTO\Model\Output\OutputDTOInterface|null $outputDTO
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \LSB\UtilityBundle\Attribute\Resource $resource
     * @return object|null
     * @throws \Exception
     */
    protected function processOutputDTO(
        ?object             $entity,
        ?OutputDTOInterface $outputDTO,
        Request             $request,
        Resource            $resource
    ): ?object {
        $processedEntity = null;
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());

        if (!$outputDTO) {
            $outputDTO = new ($resource->getOutputDTOClass())();
        }

        if (!$manager instanceof ManagerInterface) {
            //Temporarily exception will be thrown if manager will be not set
            throw new \Exception();
        }

        switch ($request->getMethod()) {
            case Request::METHOD_GET:
            case Request::METHOD_PATCH:
                $outputDTO = $manager->generateOutputDTO($outputDTO, $entity);
                break;
        }

        return $outputDTO;
    }
}