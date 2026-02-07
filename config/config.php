<?php

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

/**
 * @param DefinitionConfigurator<'array'> $configurator
 */
return static function (DefinitionConfigurator $configurator): void {
    $append = function (string $vendor): ArrayNodeDefinition {
        /** @var ArrayNodeDefinition */
        return require __DIR__.'/vendor/'.$vendor.'.php';
    };

    $configurator
        ->rootNode()
            ->children()
                ->append($append('gemini'))
                ->append($append('openai'))
            ->end()
        ->end();
};
