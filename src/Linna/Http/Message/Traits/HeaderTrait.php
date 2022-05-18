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

trait HeaderTrait
{
    /**
     * Arrange header as required from PSR7 internal.
     *
     * @param array $headers
     *
     * @return array
     */
    private function parseHeaders(array $headers): array
    {
        $final = [];

        foreach ($headers as $name => $value) {
            if (\is_array($value)) {
                $final[$name] = $value;
                continue;
            }

            $value = \explode(',', $value);
            $final[$name] = \array_map('trim', $value);
        }

        return $final;
    }
}
