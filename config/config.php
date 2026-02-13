<?php

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

/**
 * @param DefinitionConfigurator<'array'> $configurator
 */
$configurator = static function (DefinitionConfigurator $configurator): void {
    $append = function (string $vendor): ArrayNodeDefinition {
        return require __DIR__.'/vendor/'.$vendor.'.php'; // @phpstan-ignore-line
    };

    $configurator
        ->rootNode()
            ->children()
                ->append($append('claude'))
                ->append($append('gemini'))
                ->append($append('mock'))
                ->append($append('openai'))
            ->end()
        ->end();
};

return $configurator;
