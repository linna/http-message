<?php

/**
 * Linna Http Message.
 *
 * @author Sebastian Rapetti <sebastian.rapetti@tim.it>
 * @copyright (c) 2019, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 */
declare(strict_types=1);

namespace Linna\Http\Message\Traits;

/**
 * Uri trait.
 *
 * Provide help methods for Uri class.
 */
trait UriTrait
{
    /**
     * Create uri string.
     *
     * @param string $scheme
     * @param string $authority
     * @param string $path
     * @param string $query
     * @param string $fragment
     *
     * @return string
     */
    private function createUriString(
        string $scheme,
        string $authority,
        string $path,
        string $query,
        string $fragment
    ): string {
        $uri = $scheme.$authority;

        $finalPath = ($path && $uri && '/' !== \substr($path, 0, 1)) ? '/'.$path : $path;

        return \implode('', [$uri, $finalPath, $query, $fragment]);
    }

    /**
     * Get non standard port.
     *
     * @param int|null  $port
     * @param string    $scheme
     * @param bool      $standardScheme
     * @param array     $supportedSchemes
     *
     * @return int|null
     */
    private function getNonStandardPort(
        int|null $port,
        string $scheme,
        bool $standardScheme,
        array $supportedSchemes
    ): ?int {
        return (!$port && $standardScheme) ? $supportedSchemes[$scheme] : $port;
    }

    /**
     * Get port for standard scheme.
     *
     * @param bool $standardPort
     * @param int  $port
     *
     * @return int|null
     */
    private function getPortForStandardScheme(bool $standardPort, int $port): ?int
    {
        return ($standardPort) ? null : $port;
    }

    /**
     * Check standard port for current scheme.
     *
     * @param string    $scheme
     * @param int|null  $port
     * @param array     $supportedSchemes
     *
     * @return bool
     */
    private function checkStandardPortForCurretScheme(string $scheme, int|null $port, array $supportedSchemes): bool
    {
        return (isset($supportedSchemes[$scheme]) && $port === $supportedSchemes[$scheme]) ? true : false;
    }
}
