<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DTO\DataTransformer;

use Doctrine\Common\Collections\Collection;
use LSB\UtilityBundle\Attribute\ConvertToObject;
use LSB\UtilityBundle\Attribute\DTOPropertyConfig;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DTO\DTOService;
use LSB\UtilityBundle\DTO\Model\BaseDTO;
use LSB\UtilityBundle\DTO\Model\DTOInterface;
use LSB\UtilityBundle\DTO\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\DTO\Model\ObjectHolder;
use LSB\UtilityBundle\Manager\ManagerInterface;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

class EntityConverter
{

}