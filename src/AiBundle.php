<?php

namespace OneToMany\AiBundle;

use OneToMany\AI\AiClient;
use OneToMany\AI\Bridge\Gemini\FileProvider as GeminiFileProvider;
use OneToMany\AI\Bridge\Gemini\Normalizer\MetadataNormalizer as GeminiMetadataNormalizer;
use OneToMany\AI\Bridge\Gemini\Normalizer\QueryNormalizer as GeminiQueryNormalizer;
use OneToMany\AI\Bridge\Gemini\QueryProvider as GeminiQueryProvider;
use OneToMany\AI\Bridge\Gemini\SearchStoreProvider as GeminiSearchStoreProvider;
use OneToMany\AI\Bridge\OpenAI\FileProvider as OpenAiFileProvider;
use OneToMany\AI\Bridge\OpenAI\Normalizer\QueryNormalizer as OpenAiQueryNormalizer;
use OneToMany\AI\Bridge\OpenAI\QueryProvider as OpenAiQueryProvider;
use OneToMany\AI\Bridge\OpenAI\SearchStoreProvider as OpenAiSearchStoreProvider;
use OneToMany\AI\Bridge\Transport;
use OneToMany\AI\Contract\AiClientInterface;
use OneToMany\AI\Contract\Resource\FilesInterface;
use OneToMany\AI\Contract\Resource\QueriesInterface;
use OneToMany\AI\Contract\Resource\SearchStoreFilesInterface;
use OneToMany\AI\Contract\Resource\SearchStoresInterface;
use OneToMany\AI\Resource\Files;
use OneToMany\AI\Resource\Queries;
use OneToMany\AI\Resource\SearchStoreFiles;
use OneToMany\AI\Resource\SearchStores;
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
    private const string GEMINI_METADATA_NORMALIZER_SERVICE = '.onetomany_ai.normalizer.gemini.metadata';
    private const string GEMINI_NORMALIZER_SERVICE = '.onetomany_ai.normalizer.gemini';
    private const string GEMINI_QUERY_PROVIDER_SERVICE = '.onetomany_ai.provider.gemini.query';
    private const string GEMINI_SEARCH_STORE_PROVIDER_SERVICE = '.onetomany_ai.provider.gemini.search_store';
    private const string OPENAI_FILE_PROVIDER_SERVICE = '.onetomany_ai.provider.openai.file';
    private const string OPENAI_NORMALIZER_SERVICE = '.onetomany_ai.normalizer.openai';
    private const string OPENAI_QUERY_PROVIDER_SERVICE = '.onetomany_ai.provider.openai.query';
    private const string OPENAI_SEARCH_STORE_PROVIDER_SERVICE = '.onetomany_ai.provider.openai.search_store';
    private const string QUERIES_SERVICE = '.onetomany_ai.resource.queries';
    private const string QUERY_PROVIDER_TAG = 'onetomany_ai.query_provider';
    private const string SEARCH_STORE_FILES_SERVICE = '.onetomany_ai.resource.search_store_files';
    private const string SEARCH_STORE_PROVIDER_TAG = 'onetomany_ai.search_store_provider';
    private const string SEARCH_STORES_SERVICE = '.onetomany_ai.resource.search_stores';
    private const string TRANSPORT_SERVICE = '.onetomany_ai.transport';

    /**
     * @see Symfony\Component\Config\Definition\ConfigurableInterface
     *
     * @param DefinitionConfigurator<'array'> $definition
     */
    #[\Override]
    public function configure(
        DefinitionConfigurator $definition,
    ): void {
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

            ->set(self::SEARCH_STORE_FILES_SERVICE, SearchStoreFiles::class)
                ->arg('$providers', tagged_iterator(self::SEARCH_STORE_PROVIDER_TAG))
                ->alias(SearchStoreFiles::class, service(self::SEARCH_STORE_FILES_SERVICE))
                ->alias(SearchStoreFilesInterface::class, service(self::SEARCH_STORE_FILES_SERVICE))

            ->set(self::SEARCH_STORES_SERVICE, SearchStores::class)
                ->arg('$providers', tagged_iterator(self::SEARCH_STORE_PROVIDER_TAG))
                ->arg('$files', service(self::SEARCH_STORE_FILES_SERVICE))
                ->alias(SearchStores::class, service(self::SEARCH_STORES_SERVICE))
                ->alias(SearchStoresInterface::class, service(self::SEARCH_STORES_SERVICE))

            ->set(self::AI_CLIENT_SERVICE, AiClient::class)
                ->arg('$files', service(self::FILES_SERVICE))
                ->arg('$queries', service(self::QUERIES_SERVICE))
                ->arg('$searchStores', service(self::SEARCH_STORES_SERVICE))
                ->alias(AiClientInterface::class, service(self::AI_CLIENT_SERVICE))
        ;

        if (isset($config['gemini'])) {
            $services
                ->set(self::GEMINI_METADATA_NORMALIZER_SERVICE, GeminiMetadataNormalizer::class)
                    ->tag('serializer.normalizer')

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

                ->set(self::GEMINI_SEARCH_STORE_PROVIDER_SERVICE, GeminiSearchStoreProvider::class)
                    ->arg('$transport', service(self::TRANSPORT_SERVICE))
                    ->arg('$serializer', service('serializer'))
                    ->arg('$apiKey', $config['gemini']['api_key'])
                    ->arg('$apiVersion', $config['gemini']['api_version'])
                    ->tag(self::SEARCH_STORE_PROVIDER_TAG)
            ;
        }

        if (isset($config['openai'])) {
            $services
                ->set(self::OPENAI_NORMALIZER_SERVICE, OpenAiQueryNormalizer::class)
                    ->tag('serializer.normalizer')

                ->set(self::OPENAI_FILE_PROVIDER_SERVICE, OpenAiFileProvider::class)
                    ->arg('$transport', service(self::TRANSPORT_SERVICE))
                    ->arg('$serializer', service('serializer'))
                    ->arg('$apiKey', $config['openai']['api_key'])
                    ->arg('$apiVersion', $config['openai']['api_version'])
                    ->tag(self::FILE_PROVIDER_TAG)

                ->set(self::OPENAI_QUERY_PROVIDER_SERVICE, OpenAiQueryProvider::class)
                    ->arg('$transport', service(self::TRANSPORT_SERVICE))
                    ->arg('$serializer', service('serializer'))
                    ->arg('$apiKey', $config['openai']['api_key'])
                    ->arg('$apiVersion', $config['openai']['api_version'])
                    ->tag(self::QUERY_PROVIDER_TAG)

                ->set(self::OPENAI_SEARCH_STORE_PROVIDER_SERVICE, OpenAiSearchStoreProvider::class)
                    ->arg('$transport', service(self::TRANSPORT_SERVICE))
                    ->arg('$serializer', service('serializer'))
                    ->arg('$apiKey', $config['openai']['api_key'])
                    ->arg('$apiVersion', $config['openai']['api_version'])
                    ->tag(self::SEARCH_STORE_PROVIDER_TAG)
            ;
        }
    }
}
