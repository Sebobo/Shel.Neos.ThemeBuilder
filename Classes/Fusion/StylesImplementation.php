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

use Neos\Fusion\FusionObjects\AbstractArrayFusionObject;

class StylesImplementation extends AbstractArrayFusionObject
{
    public function evaluate(): string
    {
        $styles = $this->evaluateNestedProperties();

        return array_reduce(array_keys($styles), function ($carry, $key) use ($styles) {
            $value = $styles[$key];
            return $value ? $carry . $key . ':' . $value . ';' : $carry;
        }, '');
    }
}
