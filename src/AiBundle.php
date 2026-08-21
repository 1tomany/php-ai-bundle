<?php

namespace OneToMany\Bundle\AiBundle;

use OneToMany\AI\AiClient;
use OneToMany\AI\Bridge\Gemini\FileProvider as GeminiFileProvider;
use OneToMany\AI\Bridge\Gemini\Normalizer\QueryNormalizer as GeminiQueryNormalizer;
use OneToMany\AI\Bridge\Gemini\QueryProvider as GeminiQueryProvider;
use OneToMany\AI\Bridge\OpenAI\FileProvider as OpenAIFileProvider;
use OneToMany\AI\Bridge\OpenAI\Normalizer\QueryNormalizer as OpenAIQueryNormalizer;
use OneToMany\AI\Bridge\OpenAI\QueryProvider as OpenAIQueryProvider;
use OneToMany\AI\Bridge\Transport;
use OneToMany\AI\Contract\AiClientInterface;
use OneToMany\AI\Contract\Resource\FilesInterface;
use OneToMany\AI\Contract\Resource\QueriesInterface;
use OneToMany\AI\Resource\Files;
use OneToMany\AI\Resource\Queries;
use OneToMany\AI\Validator\ModelValidator;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

class AiBundle extends AbstractBundle
{
    protected string $extensionAlias = 'onetomany_ai';

    private const string AI_CLIENT_SERVICE = '.onetomany_ai.ai_client';
    private const string FILE_PROVIDER_TAG = 'onetomany_ai.file_provider';
    private const string FILES_SERVICE = '.onetomany_ai.resource.files';
    private const string GEMINI_FILE_PROVIDER_SERVICE = '.onetomany_ai.provider.gemini.file';
    private const string GEMINI_NORMALIZER_SERVICE = '.onetomany_ai.normalizer.gemini';
    private const string GEMINI_QUERY_PROVIDER_SERVICE = '.onetomany_ai.provider.gemini.query';
    private const string OPENAI_FILE_PROVIDER_SERVICE = '.onetomany_ai.provider.openai.file';
    private const string OPENAI_NORMALIZER_SERVICE = '.onetomany_ai.normalizer.openai';
    private const string OPENAI_QUERY_PROVIDER_SERVICE = '.onetomany_ai.provider.openai.query';
    private const string QUERIES_SERVICE = '.onetomany_ai.resource.queries';
    private const string QUERY_PROVIDER_TAG = 'onetomany_ai.query_provider';
    private const string TRANSPORT_SERVICE = '.onetomany_ai.transport';

    /**
     * @see Symfony\Component\Config\Definition\ConfigurableInterface
     *
     * @param DefinitionConfigurator<'array'> $definition
     */
    #[\Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('transport')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->stringNode('http_client')
                                ->cannotBeEmpty()
                                ->defaultValue('http_client')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('gemini')
                        ->children()
                            ->stringNode('api_key')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->stringNode('api_version')
                                ->cannotBeEmpty()
                                ->defaultValue('v1beta')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('openai')
                        ->children()
                            ->stringNode('api_key')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->stringNode('api_version')
                                ->cannotBeEmpty()
                                ->defaultValue('v1')
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
     *   transport: array{
     *     http_client: non-empty-string,
     *   },
     *   gemini?: array{
     *     api_key: non-empty-string,
     *     api_version: non-empty-string,
     *   },
     *   openai?: array{
     *     api_key: non-empty-string,
     *     api_version: non-empty-string,
     *   },
     * } $config
     */
    #[\Override]
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $services = $container->services();

        $services
            ->set(ModelValidator::class)
                ->tag('validator.constraint_validator')

            ->set(self::TRANSPORT_SERVICE, Transport::class)
                ->arg('$httpClient', service($config['transport']['http_client']))
                ->arg('$serializer', service('serializer'))

            ->set(self::FILES_SERVICE, Files::class)
                ->arg('$providers', tagged_iterator(self::FILE_PROVIDER_TAG))
                ->alias(Files::class, service(self::FILES_SERVICE))
                ->alias(FilesInterface::class, service(self::FILES_SERVICE))

            ->set(self::QUERIES_SERVICE, Queries::class)
                ->arg('$providers', tagged_iterator(self::QUERY_PROVIDER_TAG))
                ->alias(Queries::class, service(self::QUERIES_SERVICE))
                ->alias(QueriesInterface::class, service(self::QUERIES_SERVICE))

            ->set(self::AI_CLIENT_SERVICE, AiClient::class)
                ->arg('$files', service(self::FILES_SERVICE))
                ->arg('$queries', service(self::QUERIES_SERVICE))
                ->alias(AiClientInterface::class, service(self::AI_CLIENT_SERVICE))
        ;

        if (isset($config['gemini'])) {
            $services
                ->set(self::GEMINI_NORMALIZER_SERVICE, GeminiQueryNormalizer::class)
                    ->tag('serializer.normalizer')

                ->set(self::GEMINI_FILE_PROVIDER_SERVICE, GeminiFileProvider::class)
                    ->arg('$transport', service(self::TRANSPORT_SERVICE))
                    ->arg('$serializer', service('serializer'))
                    ->arg('$apiKey', $config['gemini']['api_key'])
                    ->arg('$apiVersion', $config['gemini']['api_version'])
                    ->tag(self::FILE_PROVIDER_TAG)

                ->set(self::GEMINI_QUERY_PROVIDER_SERVICE, GeminiQueryProvider::class)
                    ->arg('$transport', service(self::TRANSPORT_SERVICE))
                    ->arg('$serializer', service('serializer'))
                    ->arg('$apiKey', $config['gemini']['api_key'])
                    ->arg('$apiVersion', $config['gemini']['api_version'])
                    ->tag(self::QUERY_PROVIDER_TAG)
            ;
        }

        if (isset($config['openai'])) {
            $services
                ->set(self::OPENAI_NORMALIZER_SERVICE, OpenAIQueryNormalizer::class)
                    ->tag('serializer.normalizer')

                ->set(self::OPENAI_FILE_PROVIDER_SERVICE, OpenAIFileProvider::class)
                    ->arg('$transport', service(self::TRANSPORT_SERVICE))
                    ->arg('$serializer', service('serializer'))
                    ->arg('$apiKey', $config['openai']['api_key'])
                    ->arg('$apiVersion', $config['openai']['api_version'])
                    ->tag(self::FILE_PROVIDER_TAG)

                ->set(self::OPENAI_QUERY_PROVIDER_SERVICE, OpenAIQueryProvider::class)
                    ->arg('$transport', service(self::TRANSPORT_SERVICE))
                    ->arg('$serializer', service('serializer'))
                    ->arg('$apiKey', $config['openai']['api_key'])
                    ->arg('$apiVersion', $config['openai']['api_version'])
                    ->tag(self::QUERY_PROVIDER_TAG)
            ;
        }
    }
}
