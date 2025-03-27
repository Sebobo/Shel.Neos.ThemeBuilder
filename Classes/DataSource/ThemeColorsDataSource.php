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

use Neos\ContentRepository\Core\Feature\Security\Exception\AccessDenied;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindAncestorNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\NodeType\NodeTypeCriteria;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Shel\Neos\ThemeBuilder\Helper\ThemePropertyHelper;

/**
 * Provides a list of all defined colors with previews defined in the theme nodetype mixin
 */
class ThemeColorsDataSource extends AbstractDataSource
{
    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    /**
     * @var string
     */
    protected static $identifier = 'shel-neos-themebuilder-theme-colors';

    /**
     * Render each palette property of the nodetype "Shel.Neos.ThemeBuilder:Mixin.PageTheme" as selectable color/value with a preview
     *
     * @param array{filterUnsetProperties?: bool} $arguments
     * @return array<array{label: string, value: string, group: string, preview: string}>
     */
    public function getData(
        Node $node = null,
        array $arguments = []
    ): array {
        if (!$node) {
            return [];
        }

        $filterUnsetProperties = ($arguments['filterUnsetProperties'] ?? true) !== 'false';

        $contentRepository = $this->contentRepositoryRegistry->get($node->contentRepositoryId);
        try {
            $subgraph = $contentRepository->getContentSubgraph(
                $node->workspaceName,
                $node->dimensionSpacePoint
            );
        } catch (AccessDenied) {
            return [];
        }

        $nodeTypeManager = $contentRepository->getNodeTypeManager();
        $themeNodeType = $nodeTypeManager->getNodeType(ThemePropertyHelper::PAGE_THEME_MIXIN);

        $themedAncestorNodes = $subgraph->findAncestorNodes(
            $node->aggregateId,
            FindAncestorNodesFilter::create(
                NodeTypeCriteria::fromFilterString(ThemePropertyHelper::PAGE_THEME_MIXIN)
            )
        );
        $themedRootNode = $themedAncestorNodes->first() ?? $node;
        $closestThemedNode = $themedAncestorNodes->count() > 1 ? $themedAncestorNodes->last() : $node;

        if (!$themeNodeType || !$closestThemedNode) {
            return [];
        }

        $themeProperties = $themeNodeType->getProperties();
        $groups = $themeNodeType->getConfiguration('ui.inspector.groups');

        return array_filter(
            array_map(
                function (string $propertyName) use (
                    $closestThemedNode,
                    $themedRootNode,
                    $themeProperties,
                    $groups,
                    $filterUnsetProperties
                ) {
                    // Hide non-color properties
                    if (!ThemePropertyHelper::isColorProperty(
                        $propertyName,
                        $themeProperties
                    )) {
                        return null;
                    }
                    // Hide empty properties
                    $value = $this->getPropertyValue(
                        $propertyName,
                        $closestThemedNode,
                        $themedRootNode
                    );
                    if (!$value && $filterUnsetProperties) {
                        return null;
                    }

                    // Return color property of closest themed node
                    return $this->createThemeColorOption(
                        $propertyName,
                        $this->getPropertyLabel($propertyName, $themeProperties),
                        $this->getGroupLabel($propertyName, $themeProperties, $groups),
                        $this->getPropertyValue(
                            $propertyName,
                            $closestThemedNode,
                            $themedRootNode
                        )
                    );
                },
                array_keys($themeProperties)
            )
        );
    }

    protected function getPropertyValue(
        string $propertyName,
        Node $paletteNode,
        Node $siteNode
    ): string {
        return $paletteNode->getProperty($propertyName) ?: $siteNode->getProperty($propertyName) ?: '';
    }

    /**
     * @return array{label: string, value: string, group: string, preview: string}
     */
    protected function createThemeColorOption(
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
            'preview' => $this->getColorPreview($propertyValue)
        ];
    }

    protected function getColorPreview(?string $color): string
    {
        if ($color) {
            // TODO: Render SVG preview only for colors
            return 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="' . $color . '" d="M0 96C0 60.7 28.7 32 64 32H384c35.3 0 64 28.7 64 64V416c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V96z"/></svg>';
        }
        return 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M504 256c0 137-111 248-248 248S8 393 8 256C8 119.1 119 8 256 8s248 111.1 248 248zM262.7 90c-54.5 0-89.3 23-116.5 63.8-3.5 5.3-2.4 12.4 2.7 16.3l34.7 26.3c5.2 3.9 12.6 3 16.7-2.1 17.9-22.7 30.1-35.8 57.3-35.8 20.4 0 45.7 13.1 45.7 33 0 15-12.4 22.7-32.5 34C247.1 238.5 216 254.9 216 296v4c0 6.6 5.4 12 12 12h56c6.6 0 12-5.4 12-12v-1.3c0-28.5 83.2-29.6 83.2-106.7 0-58-60.2-102-116.5-102zM256 338c-25.4 0-46 20.6-46 46 0 25.4 20.6 46 46 46s46-20.6 46-46c0-25.4-20.6-46-46-46z"/></svg>';
    }

    protected function getGroupLabel(string $propertyName, array $themeProperties, array $groups): string
    {
        $group = $themeProperties[$propertyName]['ui']['inspector']['group'] ?? null;
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
