<?php

namespace OneToMany\PhpAiBundle;

use OneToMany\AI\Client\Gemini\FileClient as GeminiFileClient;
use OneToMany\AI\Client\OpenAi\FileClient as OpenAiFileClient;
use OneToMany\AI\Contract\Client\FileClientInterface;
use OneToMany\AI\Contract\Client\QueryClientInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class PhpAiBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('gemini')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('api_key')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('openai')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('api_key')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $builder): void
    {
        // $builder
        //     ->registerForAutoconfiguration(FileClientInterface::class)
        //     ->addTag('1tomany.ai.file_client');

        // $builder
        //     ->registerForAutoconfiguration(QueryClientInterface::class)
        //     ->addTag('1tomany.ai.query_client');

        if ($config['gemini']['enabled']) {
            $definition = $builder->register(GeminiFileClient::class, GeminiFileClient::class);

            $definition
                ->setAutoconfigured(true)
                ->setAutowired(true)
                ->setArgument('$apiKey', $config['gemini']['api_key'])
                ->addTag('1tomany.ai.file_client');
        }

        if ($config['openai']['enabled']) {
            $builder->register(OpenAiFileClient::class, OpenAiFileClient::class)
                ->setArgument('$apiKey', $config['openai']['api_key'])
                ->setAutoconfigured(true)
                ->setAutowired(true)
                ->addTag('1tomany.ai.file_client');
        }

        $configurator->import('../config/services.yaml');
    }
}
