<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Controller;

use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use LSB\UtilityBundle\Application\AppCodeTrait;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Repository\PaginationInterface as RepositoryPaginationInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Context\Context;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Form\Form;

/**
 * Class BaseApiController
 * @package LSB\UtilityBundle\Controller
 */
abstract class BaseApiController extends AbstractFOSRestController
{
    use AppCodeTrait;

    const DEFAULT_PAGE = 1;
    const DEFAULT_LIMIT = 10;

    const REQUEST_QUERY_PARAMETER_PAGE = 'page';
    const REQUEST_QUERY_PARAMETER_LIMIT = 'limit';

    const DEFAULT_SERIALIZATION_GROUP = 'Api';

    /**
     * @param string|null $uuid
     */
    public function validateUuid(?string $uuid): void
    {
        Assert::notNull($uuid, "UUID is required");
        Assert::uuid($uuid, "String must much UUID format.");
    }

    /**
     * @param string $objectClassName
     * @param null $uuid
     * @return HttpException
     */
    protected function createNotFoundHttpException(string $objectClassName, $uuid = null): HttpException
    {
        return new HttpException(404, "{$objectClassName} was not found." . ($uuid ? "UUID: {$uuid}" : ""));
    }

    /**
     * @param string $message
     * @return HttpException
     */
    protected function createBadRequestHttpException(string $message): HttpException
    {
        return new HttpException(400, $message);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @return FormInterface
     */
    protected function decodeSubmitFormRequest(Request $request, FormInterface $form): FormInterface
    {
        $data = json_decode($request->getContent(), true);
        $clearMissing = $request->getMethod() != Request::METHOD_PATCH;
        $form->submit($data, $clearMissing);

        return $form;
    }

    /**
     * @param ManagerInterface $manager
     * @param object|null $object
     * @return FormInterface
     * @throws Exception
     */
    protected function createEntityForm(ManagerInterface $manager, ?object $object = null): FormInterface
    {
        $requiredFqcn = $manager->getFactory()->getClassName();

        if (!$object) {
            $object = $manager->getFactory()->createNew();
        } elseif (!$object instanceof $requiredFqcn) {
            throw new Exception("Object is no instance of {$requiredFqcn}");
        }

        return $this->createForm(get_class($manager->getForm()), $object);
    }

    /**
     * @param Request $request
     * @param ManagerInterface $manager
     * @param object|null $object
     * @return object|FormInterface|null
     * @throws Exception
     */
    protected function handleEntityRequest(Request $request, ManagerInterface $manager, ?object $object = null)
    {
        $form = $this->createEntityForm($manager, $object);
        $this->decodeSubmitFormRequest($request, $form);

        return $this->storeEntityData($manager, $form);
    }

    /**
     * @param ManagerInterface $manager
     * @param FormInterface $form
     * @return FormInterface|object|null
     */
    protected function storeEntityData(ManagerInterface $manager, FormInterface $form)
    {
        if (!$form->isSubmitted()) {
            $this->createBadRequestHttpException("Missing entity form");
        }

        if ($form->isValid()) {
            $manager->doPersist($form->getData());
            return $form->getData();
        }

        return $form;
    }

    /**
     * @param PaginatorInterface $paginator
     * @param RepositoryPaginationInterface $repository
     * @param Request $request
     * @param string $qbAlias
     * @return PaginationInterface
     */
    protected function paginate(
        PaginatorInterface $paginator,
        RepositoryPaginationInterface $repository,
        Request $request,
        string $qbAlias = RepositoryPaginationInterface::DEFAULT_ALIAS
    ): PaginationInterface {
        return $paginator->paginate(
            $repository->getPaginationQueryBuilder(),
            $request->query->getInt(static::REQUEST_QUERY_PARAMETER_PAGE, self::DEFAULT_PAGE),
            $request->query->getInt(static::REQUEST_QUERY_PARAMETER_LIMIT, self::DEFAULT_LIMIT)
        );
    }

    /**
     * @param Form $form
     * @return array
     */
    protected function getErrorMessages(Form $form):array
    {
        $errors = array();

        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $form[$child->getName()]->getErrors();
                }
            }
        }
        return $errors;
    }

    /**
     * @param $data
     * @param int $responseStatus
     * @param array $groups
     * @param string|null $location
     * @param bool $serializeNull
     * @param bool $noIndex
     * @return Response
     */
    public function serializeResponse(
        $data,
        int $responseStatus = Response::HTTP_OK,
        array $groups = [],
        ?string $location = null,
        bool $serializeNull = true,
        bool $noIndex = true
    ): Response {

        $view = (new View)
            ->setStatusCode($responseStatus)
            ->setData($data);

        if ($noIndex) {
            $view
                ->setHeader('X-Robots-Tag', 'noindex');
        }

        $context = new Context();
        $context
            ->setVersion('1.0')
            ->setSerializeNull($serializeNull);

        if (count($groups) === 0) {
            $context->addGroup(static::DEFAULT_SERIALIZATION_GROUP);
        } else {
            $context->addGroups($groups);
        }

        $view->setContext($context);

        if ($location !== null) {
            $view->setLocation($location);
        }

        return $this->handleView($view);
    }


    /**
     * @param $data
     * @param string $route
     * @param array $groups
     * @return Response
     * @throws Exception
     */
    public function serializePostActionResponse($data, string $route, array $groups = []): Response
    {
        if ($data instanceof FormInterface) {
            return $this->serializeResponse($data, Response::HTTP_OK, $groups);
        } elseif ($data && $data->getUuid()) {
            return $this->serializeResponse(null, Response::HTTP_NO_CONTENT, $groups, $this->generateUrl($route, ['uuid' => $data->getUuid()]));
        }

        throw new Exception('Not allowed to serialize.');
    }

    /**
     * @param Form $form
     * @return array
     */
    protected function getFormErrors(Form $form)
    {
        $errors = array();

        // Global
        foreach ($form->getErrors() as $error) {
            $errors[$form->getName()][] = $error->getMessage();
        }

        // Fields
        foreach ($form as $child /** @var Form $child */) {
            if (!$child->isValid()) {
                foreach ($child->getErrors() as $error) {
                    $errors[$child->getName()][] = $error->getMessage();
                }
            }
        }

        return $errors;
    }

    /**
     * @param array $collection
     * @param string $actionName
     * @return array
     */
    protected function filterCollection(array $collection, string $actionName): array
    {
        return array_filter($collection, function (object $user) use ($actionName) {
            return $this->isGranted($actionName, $user);
        });
    }

    /**
     * @param array $collection
     * @param string $actionName
     * @return array
     */
    protected function checkCollection(iterable $collection, string $actionName, ?string $voterSubjectClass): void
    {
        foreach($collection as $item) {
            $this->denyAccessUnlessGranted($actionName, $voterSubjectClass ? new $voterSubjectClass($item, $this->getAppCode()) : $item);
        }
    }
}
