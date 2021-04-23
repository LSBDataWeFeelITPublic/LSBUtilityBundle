<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use LSB\UtilityBundle\Application\AppCodeTrait;
use LSB\UtilityBundle\Application\ApplicationContextInterface;

/**
 * Class BaseRepository
 * @package LSB\UtilityBundle\Repository
 */
abstract class BaseRepository extends ServiceEntityRepository implements ApplicationContextInterface
{
    use AppCodeTrait;
}
