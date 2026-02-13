<?php

use OneToMany\AI\Action\File\DeleteFileAction;
use OneToMany\AI\Action\File\UploadFileAction;
use OneToMany\AI\Action\Query\CompileQueryAction;
use OneToMany\AI\Action\Query\ExecuteQueryAction;
use OneToMany\AI\Client\Claude\FileClient as ClaudeFileClient;
use OneToMany\AI\Client\Gemini\FileClient as GeminiFileClient;
use OneToMany\AI\Client\Gemini\QueryClient as GeminiQueryClient;
use OneToMany\AI\Client\Mock\FileClient as MockFileClient;
use OneToMany\AI\Client\Mock\QueryClient as MockQueryClient;
use OneToMany\AI\Client\OpenAi\FileClient as OpenAiFileClient;
use OneToMany\AI\Client\OpenAi\QueryClient as OpenAiQueryClient;
use OneToMany\AI\Contract\Action\File\DeleteFileActionInterface;
use OneToMany\AI\Contract\Action\File\UploadFileActionInterface;
use OneToMany\AI\Contract\Action\Query\CompileQueryActionInterface;
use OneToMany\AI\Contract\Action\Query\ExecuteQueryActionInterface;
use OneToMany\AI\Factory\ClientFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container
        ->services()
            // Client Factories
            ->set('php_ai.factory.client', ClientFactory::class)
                ->abstract(true)
            ->set('php_ai.factory.client.file', ClientFactory::class)
                ->arg('$clients', tagged_iterator('php_ai.client.file'))
            ->set('php_ai.factory.client.query', ClientFactory::class)
                ->arg('$clients', tagged_iterator('php_ai.client.query'))

            // File Actions
            ->alias(DeleteFileActionInterface::class, service('php_ai.action.file.delete'))
            ->alias(UploadFileActionInterface::class, service('php_ai.action.file.upload'))
            ->set('php_ai.action.file.delete', DeleteFileAction::class)
                ->arg('$clientFactory', service('php_ai.factory.client.file'))
            ->set('php_ai.action.file.upload', UploadFileAction::class)
                ->arg('$clientFactory', service('php_ai.factory.client.file'))

            // File Clients
            ->set('php_ai.client.claude.file', ClaudeFileClient::class)
                ->tag('php_ai.client.file')
            ->set('php_ai.client.gemini.file', GeminiFileClient::class)
                ->tag('php_ai.client.file')
            ->set('php_ai.client.mock.file', MockFileClient::class)
                ->tag('php_ai.client.file')
            ->set('php_ai.client.openai.file', OpenAiFileClient::class)
                ->tag('php_ai.client.file')

            // Query Actions
            ->alias(CompileQueryActionInterface::class, service('php_ai.action.query.compile'))
            ->alias(ExecuteQueryActionInterface::class, service('php_ai.action.query.execute'))
            ->set('php_ai.action.query.compile', CompileQueryAction::class)
                ->arg('$clientFactory', service('php_ai.factory.client.query'))
            ->set('php_ai.action.query.execute', ExecuteQueryAction::class)
                ->arg('$clientFactory', service('php_ai.factory.client.query'))

            // Query Clients
            ->set('php_ai.client.gemini.query', GeminiQueryClient::class)
                ->tag('php_ai.client.query')
            ->set('php_ai.client.mock.query', MockQueryClient::class)
                ->tag('php_ai.client.query')
            ->set('php_ai.client.openai.query', OpenAiQueryClient::class)
                ->tag('php_ai.client.query')
    ;
};
