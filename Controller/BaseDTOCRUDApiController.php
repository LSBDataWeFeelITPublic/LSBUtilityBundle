<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Controller;

use Knp\Component\Pager\PaginatorInterface;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Security\BaseObjectVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseDTOCRUDApiController extends BaseApiController
{

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
        $object = $this->manager->getRepository()->findOneBy(['uuid' => $uuid]);

        if ($object && !$object instanceof $this->entityFqcn || !$object && $throwException) {
            throw $this->createNotFoundException("$this->entityFqcn with UUID: $uuid was not found.");
        }

        return $object;
    }
}
