<?php

/**
 * Linna PSR7
 *
 * @author Sebastian Rapetti <sebastian.rapetti@alice.it>
 * @copyright (c) 2017, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 *
 */

namespace Linna\Psr7;

/**
 * Uri trait.
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
            ) : string
    {
        $uri = $scheme.$authority;
        
        $uri .= ('/' !== substr($path, 0, 1) && $uri !== '' && $path !== '') ? '/'.$path : $path;

        $uri .= $query.$fragment;
        
        return $uri;
    }
    
    /**
     * Get non standard port.
     * 
     * @param int $port
     * @param string $scheme
     * @param string $standardScheme
     * @param array $supportedSchemes
     * 
     * @return int
     */
    private function getNonStandardPort(
            int $port, 
            string $scheme, 
            string $standardScheme, 
            array $supportedSchemes
            ) : int
    {
        return (!$port && $standardScheme) ? $supportedSchemes[$scheme] : $port;
    }
    
    /**
     * Get port for standard scheme.
     * 
     * @param int $standardPort
     * @param int $port
     * 
     * @return int
     */
    private function getPortForStandardScheme(int $standardPort, int $port) : int
    {
        return ($standardPort) ? 0 : $port;
    }
    
    /**
     * Check standard port for current scheme.
     * 
     * @param string $scheme
     * @param int $port
     * @param array $supportedSchemes
     * 
     * @return bool
     */
    private function checkStandardPortForCurretScheme(string $scheme, int $port, array $supportedSchemes) : bool
    {
        return (isset($supportedSchemes[$scheme]) && $port === $supportedSchemes[$scheme]) ? true : false;
    }
}
