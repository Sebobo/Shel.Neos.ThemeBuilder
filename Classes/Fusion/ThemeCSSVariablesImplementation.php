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

use Neos\ContentRepository\Core\Feature\Security\Exception\AccessDenied;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindAncestorNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\NodeType\NodeTypeCriteria;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Shel\Neos\ThemeBuilder\Helper\ThemePropertyHelper;

/**
 * Renders all defined palette properties of the given node or its closest parent with
 * nodetype "Shel.Neos.ThemeBuilder:Mixin.PageTheme" as CSS variables
 */
class ThemeCSSVariablesImplementation extends AbstractFusionObject
{

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    /**
     * Renders all defined palette properties merged from the given nodes closest themed node and the
     * theme root node (usually the site node).
     */
    public function evaluate(): string
    {
        $node = $this->getNode();

        if (!$node) {
            return '';
        }

        $contentRepository = $this->contentRepositoryRegistry->get($node->contentRepositoryId);
        try {
            $subgraph = $contentRepository->getContentSubgraph(
                $node->workspaceName,
                $node->dimensionSpacePoint
            );
        } catch (AccessDenied) {
            return '';
        }

        $nodeTypeManager = $contentRepository->getNodeTypeManager();
        $themeNodeType = $nodeTypeManager->getNodeType(ThemePropertyHelper::PAGE_THEME_MIXIN);
        $currentNodeType = $nodeTypeManager->getNodeType($node->nodeTypeName);

        $themedNodeHierarchy = $subgraph->findAncestorNodes(
            $node->aggregateId,
            FindAncestorNodesFilter::create(
                NodeTypeCriteria::fromFilterString(ThemePropertyHelper::PAGE_THEME_MIXIN)
            )
        );

        if ($currentNodeType?->isOfType(ThemePropertyHelper::PAGE_THEME_MIXIN)) {
            $themedNodeHierarchy->prepend($node);
        }

        if (!$themeNodeType || $themedNodeHierarchy->count() === 0) {
            return '';
        }

        return implode(
            ';',
            array_filter(
                array_map(
                    static function (string $propertyName) use ($themedNodeHierarchy, $themeNodeType) {
                        $value = ThemePropertyHelper::getPropertyValueFromHierarchy(
                            $propertyName,
                            $themedNodeHierarchy
                        );
                        if ($value === null) {
                            return null;
                        }
                        return ThemePropertyHelper::convertToCSSVariableDefinition(
                            $propertyName,
                            $value,
                            $themeNodeType
                        );
                    },
                    array_keys($themeNodeType->getProperties())
                )
            )
        );
    }

    protected function getNode(): ?Node
    {
        return $this->fusionValue('node');
    }
}
