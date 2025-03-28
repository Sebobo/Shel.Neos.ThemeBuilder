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

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\Exception\NodeException;
use Neos\ContentRepository\Exception\NodeTypeNotFoundException;
use Neos\Eel\Exception;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Neos\Domain\Service\ContentContext;
use Shel\Neos\ThemeBuilder\DataSource\ThemeColorsDataSource;
use Shel\Neos\ThemeBuilder\Helper\ThemePropertyHelper;

/**
 * Renders all defined palette properties of the given node or its closest parent with
 * nodetype "Shel.Neos.ThemeBuilder:Mixin.PageTheme" as CSS variables
 */
class ThemeCSSVariablesImplementation extends AbstractFusionObject
{
    #[Flow\Inject]
    protected NodeTypeManager $nodeTypeManager;

    /**
     * @throws Exception|NodeTypeNotFoundException|NodeException
     */
    public function evaluate(): string
    {
        $node = $this->getNode();

        if (!$node) {
            return '';
        }

        /** @var ContentContext $context */
        $context = $node->getContext();
        $siteNode = $context->getCurrentSiteNode();
        $closestNodeWithTheme = (new FlowQuery([$node]))
            ->closest('[instanceof ' . ThemePropertyHelper::PAGE_THEME_MIXIN . ']')->get(0);

        $themeNodeType = $this->nodeTypeManager->getNodeType(ThemePropertyHelper::PAGE_THEME_MIXIN);

        return implode(
            ';',
            array_filter(
                array_map(
                    static function (string $propertyName) use ($closestNodeWithTheme, $siteNode) {
                        $value = $closestNodeWithTheme->getProperty($propertyName) ?? $siteNode->getProperty(
                            $propertyName
                        );
                        if ($value === '' || $value === null) {
                            return null;
                        }
                        return ThemePropertyHelper::convertToCSSVariableDefinition($propertyName, $value, $closestNodeWithTheme);
                    },
                    array_keys($themeNodeType->getProperties())
                )
            )
        );
    }

    protected function getNode(): ?NodeInterface
    {
        return $this->fusionValue('node');
    }
}
