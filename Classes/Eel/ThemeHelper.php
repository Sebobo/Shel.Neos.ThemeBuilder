<?php

declare(strict_types=1);

namespace Shel\Neos\ThemeBuilder\Eel;

/**
 * This file is part of the Shel.Neos.ThemeBuilder package.
 *
 * (c) 2025 Sebastian Helzle
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Eel\ProtectedContextAwareInterface;

class ThemeHelper implements ProtectedContextAwareInterface
{

    public function formatValue(mixed $value, string $unit = ''): string
    {
        return $value ? $value . $unit : '';
    }

    public function formatPixels(mixed $value): string
    {
        return $this->formatValue($value, 'px');
    }

    public function formatPercentage(mixed $value): string
    {
        return $this->formatValue($value, '%');
    }

    public function formatDegrees(mixed $value): string
    {
        return $this->formatValue($value, 'deg');
    }

    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
