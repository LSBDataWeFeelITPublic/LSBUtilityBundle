<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Controller;

use Knp\Component\Pager\PaginatorInterface;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Security\BaseObjectVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

/**
 * FORM based solution
 *
 * Class BaseCRUDApiController
 * @package LSB\UtilityBundle\Controller
 */
abstract class BaseCRUDApiController extends BaseApiController
{

    const CONTROLLER_NAMESPACE = 'Controller';

    const ACTION_GET = 'get';

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $manager;

    protected string $entityFqcn;

    /**
     * BaseCRUDApiController constructor.
     * @param ManagerInterface $manager
     */
    public function __construct(ManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->entityFqcn = $manager->getResourceEntityClass();
    }

    /**
     * @return string
     */
    protected function generateRoutingGet(): string
    {
        $entityFqcn = $this->manager->getResourceEntityClass();
        $controllerFqcn = static::class;
        $controllerPart = mb_strtolower(static::CONTROLLER_NAMESPACE);

        $routingParts = explode('\\', $controllerFqcn);
        foreach ($routingParts as $key => $part) {
            $part = mb_strtolower($part);

            if (strpos($part, $controllerPart) !== false) {
                unset($routingParts[$key]);
                continue;
            }

            $routingParts[$key] = $part;
        }

        $entityParts = explode('\\', $entityFqcn);
        $routingParts[] = mb_strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", end($entityParts)));
        $routingParts[] = mb_strtolower(static::ACTION_GET);

        return implode('_', $routingParts);
    }

    /**
     * @param string $uuid
     * @param bool $throwException
     * @return object|null
     */
    protected function getObject(string $uuid, bool $throwException): ?object
    {
        try {
            Assert::uuid($uuid);
        } catch (\Exception $e) {
            throw $this->createNotFoundException("$this->entityFqcn with UUID: $uuid was not found.");
        }

        $object = $this->manager->getRepository()->findOneBy(['uuid' => $uuid]);

        if ($object && !$object instanceof $this->entityFqcn || !$object && $throwException) {
            throw $this->createNotFoundException("$this->entityFqcn with UUID: $uuid was not found.");
        }

        return $object;
    }

    /**
     * @param string $uuid
     * @return Response
     */
    public function getAction(string $uuid): Response
    {
        $object = $this->getObject($uuid, true);

        $this->denyAccessUnlessGranted(BaseObjectVoter::ACTION_GET, $this->manager->getVoterSubject($object, $this->getAppCode()));
        return $this->serializeResponse($object);
    }

    /**
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function cgetAction(PaginatorInterface $paginator, Request $request): Response
    {
        $this->denyAccessUnlessGranted(BaseObjectVoter::ACTION_CGET, $this->manager->getVoterSubject(null, $this->getAppCode()));
        $result = $this->paginate($paginator, $this->manager->getRepository(), $request);
        $this->checkCollection($result, BaseObjectVoter::ACTION_GET, $this->manager->getResourceVoterSubjectClass());
        return $this->serializeResponse($result);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function postAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(BaseObjectVoter::ACTION_POST, $this->manager->getVoterSubject(null, $this->getAppCode()));
        $data = $this->handleEntityRequest($request, $this->manager);
        return $this->serializePostActionResponse($data, $this->generateRoutingGet());
    }

    /**
     * @param string|null $uuid
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function putAction(?string $uuid, Request $request): Response
    {
        $object = $this->getObject($uuid, false);

        $this->denyAccessUnlessGranted(BaseObjectVoter::ACTION_POST, $this->manager->getVoterSubject($object, $this->getAppCode()));
        return $this->serializeResponse($this->handleEntityRequest($request, $this->manager, $object), Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $uuid
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function patchAction(string $uuid, Request $request): Response
    {
        $object = $this->getObject($uuid, true);

        $this->denyAccessUnlessGranted(BaseObjectVoter::ACTION_PATCH, $this->manager->getVoterSubject($object, $this->getAppCode()));
        return $this->serializeResponse($this->handleEntityRequest($request, $this->manager, $object), Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $uuid
     */
    public function deleteAction(string $uuid): void
    {
        $object = $this->getObject($uuid, true);
        $this->denyAccessUnlessGranted(BaseObjectVoter::ACTION_DELETE, $this->manager->getVoterSubject($object, $this->getAppCode()));
        $this->manager->doRemove($object);
    }
}
