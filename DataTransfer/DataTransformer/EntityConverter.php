<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DataTransfer\DataTransformer;

use Doctrine\Common\Collections\Collection;
use LSB\UtilityBundle\Attribute\ConvertToObject;
use LSB\UtilityBundle\Attribute\DTOPropertyConfig;
use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DataTransfer\DTOService;
use LSB\UtilityBundle\DataTransfer\Model\BaseDTO;
use LSB\UtilityBundle\DataTransfer\Model\DTOInterface;
use LSB\UtilityBundle\DataTransfer\Model\Input\InputDTOInterface;
use LSB\UtilityBundle\DataTransfer\Model\ObjectHolder;
use LSB\UtilityBundle\Manager\ManagerInterface;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

/**
 * @deprecated
 */
class EntityConverter
{

}