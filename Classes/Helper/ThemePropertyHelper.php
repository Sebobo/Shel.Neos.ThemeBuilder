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

    public static function getPropertyUnit(string $propertyName, NodeInterface $themeNode): string
    {
        $nodeType = $themeNode->getNodeType();
        return $nodeType->getConfiguration('properties.' . $propertyName . '.ui.inspector.editorOptions.unit') ?: '';
    }
}
