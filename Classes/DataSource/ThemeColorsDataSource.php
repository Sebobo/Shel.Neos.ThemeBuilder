<?php

declare(strict_types=1);

namespace Shel\Neos\ThemeBuilder\DataSource;

/**
 * This file is part of the Shel.Neos.ThemeBuilder package.
 *
 * (c) 2024 Sebastian Helzle
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Exception\NodeException;
use Neos\ContentRepository\Exception\NodeTypeNotFoundException;
use Neos\Eel\Exception;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Shel\Neos\ThemeBuilder\Helper\ThemePropertyHelper;

/**
 * Provides a list of all defined colors with previews defined in the theme nodetype mixin
 */
class ThemeColorsDataSource extends AbstractDataSource
{

    /**
     * @var string
     */
    protected static $identifier = 'shel-neos-themebuilder-theme-colors';

    #[Flow\Inject]
    protected NodeTypeManager $nodeTypeManager;

    /**
     * Render each palette property of the nodetype "Shel.Neos.ThemeBuilder:Mixin.PageTheme" as selectable color/value with a preview
     *
     * @throws Exception|NodeException|NodeTypeNotFoundException
     */
    public function getData(
        NodeInterface $node = null,
        array $arguments = []
    ): array {
        if (!$node) {
            return [];
        }

        /** @var ContentContext $context */
        $context = $node->getContext();
        $siteNode = $context->getCurrentSiteNode();
        $closestNodeWithPalette = (new FlowQuery([$node]))->closest('[instanceof ' . ThemePropertyHelper::PAGE_THEME_MIXIN . ']')->get(0);

        $paletteNodeType = $this->nodeTypeManager->getNodeType(ThemePropertyHelper::PAGE_THEME_MIXIN);
        $paletteProperties = $paletteNodeType->getProperties();
        $groups = $paletteNodeType->getConfiguration('ui.inspector.groups');

        // TODO: Filter properties to only return colors

        return array_map(
            function (string $propertyName) use ($closestNodeWithPalette, $siteNode, $paletteProperties, $groups) {
                return $this->createPaletteColor(
                    $propertyName,
                    $this->getPropertyLabel($propertyName, $paletteProperties),
                    $this->getGroupLabel($propertyName, $paletteProperties, $groups),
                    $this->getPropertyValue(
                        $propertyName,
                        $closestNodeWithPalette,
                        $siteNode
                    )
                );
            },
            array_keys($paletteProperties)
        );
    }

    protected function getPropertyValue(
        string $propertyName,
        NodeInterface $paletteNode,
        NodeInterface $siteNode
    ): string {
        return $paletteNode->getProperty($propertyName) ?: $siteNode->getProperty($propertyName) ?: '';
    }

    protected function createPaletteColor(
        string $propertyName,
        string $label,
        string $group,
        string $propertyValue
    ): array {
        $value = strtolower(preg_replace(ThemePropertyHelper::VARIABLE_NAME_EXPRESSION, '-$1', $propertyName));
        return [
            'label' => $label,
            'value' => 'var(--' . $value . ')',
            'group' => $group,
            // TODO: Render SVG preview only for colors
            'preview' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="' . $propertyValue . '" d="M0 96C0 60.7 28.7 32 64 32H384c35.3 0 64 28.7 64 64V416c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V96z"/></svg>'
        ];
    }

    protected function getGroupLabel(string $propertyName, array $paletteProperties, array $groups): string
    {
        $group = $paletteProperties[$propertyName]['ui']['inspector']['group'] ?? null;
        if ($group) {
            return $groups[$group]['label'] ?? 'Andere';
        }
        return 'Andere';
    }

    protected function getPropertyLabel(int|string $propertyName, array $paletteProperties): string
    {
        return $paletteProperties[$propertyName]['ui']['label'] ?? $propertyName;
    }
}
