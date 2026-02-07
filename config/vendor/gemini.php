<?php

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

return new ArrayNodeDefinition('gemini')
    ->canBeEnabled()
    ->children()
        ->stringNode('http_client')
            ->cannotBeEmpty()
            ->defaultValue('http_client')
        ->end()
        ->stringNode('serializer')
            ->cannotBeEmpty()
            ->defaultValue('serializer')
        ->end()
        ->stringNode('api_key')
            ->isRequired()
            ->cannotBeEmpty()
        ->end()
    ->end();
