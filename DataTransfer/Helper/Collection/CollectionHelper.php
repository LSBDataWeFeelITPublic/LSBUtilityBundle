<?php

namespace LSB\UtilityBundle\DataTransfer\Helper\Collection;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\Controller\BaseApiController;
use LSB\UtilityBundle\DataTransfer\Helper\App\AppHelper;
use LSB\UtilityBundle\Repository\PaginationInterface as RepositoryPaginationInterface;
use LSB\UtilityBundle\Service\ManagerContainer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CollectionHelper
{
    public function __construct(
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected PaginatorInterface            $paginator,
        protected ManagerContainer              $managerContainer,
        protected AppHelper                     $appHelper
    ) {

    }

    public function checkCollection(Resource $resource, Request $request, iterable $collection, string $actionName): bool
    {
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        $appCode = $this->appHelper->getAppCode($request);

        foreach ($collection as $item) {
            $isGranted = $this->authorizationChecker->isGranted($actionName, $manager->getVoterSubject($item, $appCode));
            if (!$isGranted) {
                return false;
            }
        }

        return true;
    }

    public function paginateCollection(Resource $resource, Request $request): PaginationInterface
    {
        $manager = $this->managerContainer->getByManagerClass($resource->getManagerClass());
        return $this->paginate($this->paginator, $manager->getRepository(), $request);
    }

    protected function paginate(
        PaginatorInterface            $paginator,
        RepositoryPaginationInterface $repository,
        Request                       $request,
        string                        $qbAlias = RepositoryPaginationInterface::DEFAULT_ALIAS
    ): PaginationInterface {
        return $paginator->paginate(
            $repository->getPaginationQueryBuilder(),
            $request->query->getInt(BaseApiController::REQUEST_QUERY_PARAMETER_PAGE, BaseApiController::DEFAULT_PAGE),
            $request->query->getInt(BaseApiController::REQUEST_QUERY_PARAMETER_LIMIT, BaseApiController::DEFAULT_LIMIT)
        );
    }
}