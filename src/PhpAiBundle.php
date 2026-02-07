<?php

namespace OneToMany\PhpAiBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class PhpAiBundle extends AbstractBundle
{
    /**
     * @var non-empty-list<'file'|'query'>
     */
    private array $clients = [
        'file',
        'query',
    ];

    /**
     * @param DefinitionConfigurator<'array'> $definition
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/config.php');
    }

    /**
     * @param array{
     *   gemini: array{
     *     api_key: non-empty-string,
     *     enabled: bool,
     *     http_client: non-empty-string,
     *     serializer: non-empty-string,
     *   },
     *   mock: array{
     *     enabled: bool,
     *   },
     *   openai: array{
     *     api_key: non-empty-string,
     *     enabled: bool,
     *     http_client: non-empty-string,
     *     serializer: non-empty-string,
     *   }
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        foreach ($config as $vendor => $vendorConfig) {
            foreach ($this->clients as $clientType) {
                $id = \vsprintf('php_ai.client.%s.%s', [
                    $vendor, $clientType,
                ]);

                if (!$builder->has($id)) {
                    continue;
                }

                if ($vendorConfig['enabled']) {
                    $definition = $builder->getDefinition($id);

                    if ($apiKey = $vendorConfig['api_key'] ?? null) {
                        $definition->setArgument('$apiKey', $apiKey);
                    }

                    if ($httpClient = $vendorConfig['http_client'] ?? null) {
                        $definition->setArgument('$httpClient', new Reference($httpClient));
                    }

                    if ($serializer = $vendorConfig['serializer'] ?? null) {
                        $definition->setArgument('$serializer', new Reference($serializer));
                    }
                } else {
                    $builder->removeDefinition($id);
                }
            }
        }
    }
}
