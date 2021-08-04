<?php
declare(strict_types=1);

namespace LSB\UtilBundle\Service;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use LSB\UserBundle\Entity\User;

/**
 * Class TokenGenerator
 * @package LSB\UtilBundle\Service
 */
class TokenGenerator
{
    /**
     * @param int $l
     * @return string
     */
    public static function generateReportCode(int $l = 10): string
    {
        $str = "";

        for ($x = 0; $x < $l; $x++) {
            $str .= substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 1);
        }

        return $str;
    }
}
