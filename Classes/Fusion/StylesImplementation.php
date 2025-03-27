<?php

declare(strict_types=1);

namespace Shel\Neos\ThemeBuilder\Fusion;

/**
 * This file is part of the Shel.Neos.ThemeBuilder package.
 *
 * (c) 2024 Sebastian Helzle
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Fusion\Exception;
use Neos\Fusion\FusionObjects\AbstractArrayFusionObject;

/**
 * Merges the properties of the Fusion object into a CSS style string.
 * Empty values are ignored.
 */
class StylesImplementation extends AbstractArrayFusionObject
{
    /**
     * @throws Exception
     * @throws InvalidConfigurationException
     * @throws \Neos\Flow\Security\Exception
     * @throws StopActionException
     */
    public function evaluate(): string
    {
        $styles = $this->evaluateNestedProperties();

        return array_reduce(array_keys($styles), static function ($carry, $key) use ($styles) {
            $value = $styles[$key];
            return $value !== null ? $carry . $key . ':' . $value . ';' : $carry;
        }, '');
    }
}
