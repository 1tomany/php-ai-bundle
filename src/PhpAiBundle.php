<?php

namespace OneToMany\PhpAiBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class PhpAiBundle extends AbstractBundle
{
    /**
     * @param DefinitionConfigurator<'array'> $definition
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/config.php');
    }

    /**
     * @param array{
     *   gemini?: array{
     *     api_key: non-empty-string,
     *     enabled: bool,
     *   },
     *   openai?: array{
     *     api_key: non-empty-string,
     *     enabled: bool,
     *   }
     * } $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $clients = ['file', 'query'];

        foreach ($config as $vendor => $vendorConfig) {
            foreach ($clients as $client) {
                $id = sprintf('php_ai.client.%s.%s', $vendor, $client);

                if ($builder->has($id)) {
                    if (!$vendorConfig['enabled']) {
                        $builder->removeDefinition($id);
                    } else {

                        $builder
                            ->getDefinition($id)
                            ->setArgument('$apiKey', $vendorConfig['api_key'])
                            ->setArgument('$httpClient', new Reference($vendorConfig['http_client']))
                            ->setArgument('$serializer', new Reference($vendorConfig['serializer']));
                    }
                }
            }
        }
    }
}
