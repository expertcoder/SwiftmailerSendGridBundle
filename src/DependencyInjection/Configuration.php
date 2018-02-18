<?php

namespace ExpertCoder\Swiftmailer\SendGridBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('expert_coder_swiftmailer_send_grid');

        $children = $rootNode->isRequired()->fixXmlConfig('category')->children();
        $this->configureApiKey($children);
        $this->configureCategories($children);
        $children->end();

        return $treeBuilder;
    }

    private function configureCategories(NodeBuilder $nodeBuilder)
    {
        // Symfony 3.3+
        if (method_exists($nodeBuilder, 'scalarPrototype')) {
            $nodeBuilder
                ->arrayNode('categories')
                    ->scalarPrototype()->end()
                ->end()
            ;
        } else {
            $nodeBuilder
                ->arrayNode('categories')
                    ->prototype('scalar')->end()
                ->end()
            ;
        }
    }

    private function configureApiKey(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->scalarNode('api_key')
                ->isRequired()
            ->end()
        ;
    }
}
