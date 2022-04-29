<?php

namespace LSB\UtilityBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ApiVersionGrabber
{
    const HEADER_VERSION = 'version';
    const QUERY_VERSION = self::HEADER_VERSION;

    const VERSION_PUBLIC = 'public'; //production version, ver equivalent == 1.0 (master version)
    const VERSION_BETA = 'beta'; //non production version, ver equivalent > 1.0 (newer version)
    const VERSION_DEFAULT = self::VERSION_PUBLIC;

    const VERSION_PUBLIC_NUMERIC = '1.0';
    const VERSION_BETA_NUMERIC = '2.0';

    public function __construct(protected RequestStack $requestStack)
    {
    }

    public function getVersion(?Request $request = null, bool $returnNumeric = false): string
    {
        if (!$request) {
            $request = $this->requestStack->getCurrentRequest();
        }

        //Default version
        if (!$request) {
            return $returnNumeric ? self::getNumericVersion(self::VERSION_DEFAULT) : self::VERSION_DEFAULT;
        }

        $fetchedVersion = null;

        if ($request->headers->has(self::HEADER_VERSION)) {
            $fetchedVersion = $request->headers->get(self::HEADER_VERSION);
        } elseif ($request->query->has(self::QUERY_VERSION)) {
            $fetchedVersion = $request->query->get(self::QUERY_VERSION);
        }

        $fetchedVersion =  match ($fetchedVersion) {
            self::VERSION_PUBLIC, self::VERSION_BETA => $fetchedVersion,
            default => self::VERSION_DEFAULT,
        };

        return $returnNumeric ? self::getNumericVersion($fetchedVersion) : $fetchedVersion;
    }

    /**
     * @param string $version
     * @return string
     */
    public static function getNumericVersion(string $version): string
    {
        return match ($version) {
            self::VERSION_PUBLIC => self::VERSION_PUBLIC_NUMERIC,
            self::VERSION_BETA => self::VERSION_BETA_NUMERIC,
            default => self::getNumericVersion(self::VERSION_DEFAULT),
        };
    }
}