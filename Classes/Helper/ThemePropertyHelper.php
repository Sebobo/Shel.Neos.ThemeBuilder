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
    public const COLOR_EDITOR_NAME = 'Shel.Neos.ColorPicker/ColorPickerEditor';

    public static function getPropertyUnit(string $propertyName, NodeInterface $themeNode): string
    {
        return $themeNode
            ->getNodeType()
            ->getConfiguration('properties.' . $propertyName . '.ui.inspector.editorOptions.unit') ?: '';
    }

    public static function isColorProperty(string $propertyName, $themeNode): bool
    {
        return ($themeNode
            ->getNodeType()
            ->getConfiguration('properties.' . $propertyName . '.ui.inspector.editor') ?? null) === self::COLOR_EDITOR_NAME;
    }
}
