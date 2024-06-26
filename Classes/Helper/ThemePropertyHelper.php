<?php

declare(strict_types=1);

namespace Shel\Neos\ThemeBuilder\Helper;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class ThemePropertyHelper
{
    public const PAGE_THEME_MIXIN = 'Shel.Neos.ThemeBuilder:Mixin.PageTheme';
    public const VARIABLE_NAME_EXPRESSION = '/(?<!^)([A-Z\d][a-z]|(?<=[a-z])[A-Z\d])/';
    public const COLOR_PICKER_PRESET = 'themeBuilder.colorPicker';

    public static function getPropertyUnit(string $propertyName, NodeInterface $themeNode): string
    {
        return $themeNode
            ->getNodeType()
            ->getConfiguration('properties.' . $propertyName . '.ui.inspector.editorOptions.unit') ?: '';
    }

    public static function isColorProperty(string $propertyName, array $propertiesConfiguration): bool
    {
        return $propertiesConfiguration[$propertyName]['options']['preset'] === self::COLOR_PICKER_PRESET;
    }

    public static function convertToCSSVariableName(string $propertyName): string
    {
        return strtolower(
            preg_replace(self::VARIABLE_NAME_EXPRESSION, '-$1', $propertyName)
        );
    }

    public static function convertToCSSVariableDefinition(
        string $propertyName,
        mixed $value,
        NodeInterface $closestNodeWithTheme
    ): string {
        $cssVariableName = self::convertToCSSVariableName($propertyName);
        $propertyUnit = self::getPropertyUnit($propertyName, $closestNodeWithTheme);
        return '--' . $cssVariableName . ':' . $value . $propertyUnit;
    }
}
