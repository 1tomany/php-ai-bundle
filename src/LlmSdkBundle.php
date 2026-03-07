<?php

namespace OneToMany\LlmSdkBundle;

use OneToMany\LlmSdk\Action\Batch\CreateBatchAction;
use OneToMany\LlmSdk\Action\Batch\ReadBatchAction;
use OneToMany\LlmSdk\Action\File\DeleteFileAction;
use OneToMany\LlmSdk\Action\File\UploadFileAction;
use OneToMany\LlmSdk\Action\Query\CompileQueryAction;
use OneToMany\LlmSdk\Action\Query\ExecuteQueryAction;
use OneToMany\LlmSdk\Client\Anthropic\AnthropicClient;
use OneToMany\LlmSdk\Client\Gemini\GeminiClient;
use OneToMany\LlmSdk\Client\Mock\MockClient;
use OneToMany\LlmSdk\Client\OpenAi\OpenAiClient;
use OneToMany\LlmSdk\Contract\Action\Batch\CreateBatchActionInterface;
use OneToMany\LlmSdk\Contract\Action\Batch\ReadBatchActionInterface;
use OneToMany\LlmSdk\Contract\Action\File\DeleteFileActionInterface;
use OneToMany\LlmSdk\Contract\Action\File\UploadFileActionInterface;
use OneToMany\LlmSdk\Contract\Action\Query\CompileQueryActionInterface;
use OneToMany\LlmSdk\Contract\Action\Query\ExecuteQueryActionInterface;
use OneToMany\LlmSdk\Factory\ClientFactory;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

class LlmSdkBundle extends AbstractBundle
{
    protected string $extensionAlias = 'onetomany_llmsdk';

    /**
     * @see Symfony\Component\Config\Definition\ConfigurableInterface
     *
     * @param DefinitionConfigurator<'array'> $definition
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
                ->children()
                    ->arrayNode('anthropic')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->stringNode('api_key')
                                ->cannotBeEmpty()
                                ->defaultValue('@@anthropic-api-key')
                            ->end()
                            ->stringNode('api_version')
                                ->cannotBeEmpty()
                                ->defaultValue('2023-06-01')
                            ->end()
                            ->stringNode('http_client')
                                ->cannotBeEmpty()
                                ->defaultValue('http_client')
                            ->end()
                            ->stringNode('serializer')
                                ->cannotBeEmpty()
                                ->defaultValue('serializer')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('gemini')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->stringNode('api_key')
                                ->cannotBeEmpty()
                                ->defaultValue('@@gemini-api-key')
                            ->end()
                            ->stringNode('api_version')
                                ->cannotBeEmpty()
                                ->defaultValue('v1beta')
                            ->end()
                            ->stringNode('http_client')
                                ->cannotBeEmpty()
                                ->defaultValue('http_client')
                            ->end()
                            ->stringNode('serializer')
                                ->cannotBeEmpty()
                                ->defaultValue('serializer')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('openai')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->stringNode('api_key')
                                ->cannotBeEmpty()
                                ->defaultValue('@@openai-api-key')
                            ->end()
                            ->stringNode('http_client')
                                ->cannotBeEmpty()
                                ->defaultValue('http_client')
                            ->end()
                            ->stringNode('serializer')
                                ->cannotBeEmpty()
                                ->defaultValue('serializer')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension\ConfigurableExtensionInterface
     *
     * @param array{
     *   anthropic: array{
     *     api_key: non-empty-string,
     *     api_version: non-empty-string,
     *     http_client: non-empty-string,
     *     serializer: non-empty-string,
     *   },
     *   gemini: array{
     *     api_key: non-empty-string,
     *     api_version: non-empty-string,
     *     http_client: non-empty-string,
     *     serializer: non-empty-string,
     *   },
     *   openai: array{
     *     api_key: non-empty-string,
     *     http_client: non-empty-string,
     *     serializer: non-empty-string,
     *   }
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container
            ->services()
                // Factories
                ->set(ClientFactory::class)
                    ->arg('$clients', tagged_iterator('onetomany.llmsdk.client'))

                // Clients
                ->set(AnthropicClient::class)
                    ->tag('onetomany.llmsdk.client')
                    ->arg('$httpClient', service($config['anthropic']['http_client']))
                    ->arg('$denormalizer', service($config['anthropic']['serializer']))
                    ->arg('$apiKey', $config['anthropic']['api_key'])
                    ->arg('$apiVersion', $config['anthropic']['api_version'])
                ->set(GeminiClient::class)
                    ->tag('onetomany.llmsdk.client')
                    ->arg('$httpClient', service($config['gemini']['http_client']))
                    ->arg('$denormalizer', service($config['gemini']['serializer']))
                    ->arg('$apiKey', $config['gemini']['api_key'])
                    ->arg('$apiVersion', $config['gemini']['api_version'])
                ->set(MockClient::class)
                    ->tag('onetomany.llmsdk.client')
                ->set(OpenAiClient::class)
                    ->tag('onetomany.llmsdk.client')
                    ->arg('$httpClient', service($config['openai']['http_client']))
                    ->arg('$denormalizer', service($config['openai']['serializer']))
                    ->arg('$apiKey', $config['openai']['api_key'])

                // Batch Actions
                ->set(CreateBatchAction::class)
                    ->arg('$clientFactory', service(ClientFactory::class))
                    ->alias(CreateBatchActionInterface::class, service(CreateBatchAction::class))
                ->set(ReadBatchAction::class)
                    ->arg('$clientFactory', service(ClientFactory::class))
                    ->alias(ReadBatchActionInterface::class, service(ReadBatchAction::class))

                // File Actions
                ->set(UploadFileAction::class)
                    ->arg('$clientFactory', service(ClientFactory::class))
                    ->alias(UploadFileActionInterface::class, service(UploadFileAction::class))
                ->set(DeleteFileAction::class)
                    ->arg('$clientFactory', service(ClientFactory::class))
                    ->alias(DeleteFileActionInterface::class, service(DeleteFileAction::class))

                // Query Actions
                ->set(CompileQueryAction::class)
                    ->arg('$clientFactory', service(ClientFactory::class))
                    ->alias(CompileQueryActionInterface::class, service(CompileQueryAction::class))
                ->set(ExecuteQueryAction::class)
                    ->arg('$clientFactory', service(ClientFactory::class))
                    ->alias(ExecuteQueryActionInterface::class, service(ExecuteQueryAction::class))
        ;
    }
}
