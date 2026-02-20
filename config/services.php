<?php

use OneToMany\AI\Clients\Action\File\DeleteFileAction;
use OneToMany\AI\Clients\Action\File\UploadFileAction;
use OneToMany\AI\Clients\Action\Query\CompileQueryAction;
use OneToMany\AI\Clients\Action\Query\ExecuteQueryAction;
use OneToMany\AI\Clients\Client\Claude\FileClient as ClaudeFileClient;
use OneToMany\AI\Clients\Client\Gemini\FileClient as GeminiFileClient;
use OneToMany\AI\Clients\Client\Gemini\QueryClient as GeminiQueryClient;
use OneToMany\AI\Clients\Client\Mock\FileClient as MockFileClient;
use OneToMany\AI\Clients\Client\Mock\QueryClient as MockQueryClient;
use OneToMany\AI\Clients\Client\OpenAI\FileClient as OpenAIFileClient;
use OneToMany\AI\Clients\Client\OpenAI\QueryClient as OpenAIQueryClient;
use OneToMany\AI\Clients\Contract\Action\File\DeleteFileActionInterface;
use OneToMany\AI\Clients\Contract\Action\File\UploadFileActionInterface;
use OneToMany\AI\Clients\Contract\Action\Query\CompileQueryActionInterface;
use OneToMany\AI\Clients\Contract\Action\Query\ExecuteQueryActionInterface;
use OneToMany\AI\Clients\Factory\ClientFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container
        ->services()
            // Client Factories
            ->set('1tomany.ai.clients.factory.client', ClientFactory::class)
                ->abstract(true)
            ->set('1tomany.ai.clients.factory.client.file', ClientFactory::class)
                ->arg('$clients', tagged_iterator('1tomany.ai.clients.client.file'))
            ->set('1tomany.ai.clients.factory.client.query', ClientFactory::class)
                ->arg('$clients', tagged_iterator('1tomany.ai.clients.client.query'))

            // File Actions
            ->alias(DeleteFileActionInterface::class, service('1tomany.ai.clients.action.file.delete'))
            ->alias(UploadFileActionInterface::class, service('1tomany.ai.clients.action.file.upload'))
            ->set('1tomany.ai.clients.action.file.delete', DeleteFileAction::class)
                ->arg('$clientFactory', service('1tomany.ai.clients.factory.client.file'))
            ->set('1tomany.ai.clients.action.file.upload', UploadFileAction::class)
                ->arg('$clientFactory', service('1tomany.ai.clients.factory.client.file'))

            // File Clients
            ->set('1tomany.ai.clients.client.claude.file', ClaudeFileClient::class)
                ->tag('1tomany.ai.clients.client.file')
            ->set('1tomany.ai.clients.client.gemini.file', GeminiFileClient::class)
                ->tag('1tomany.ai.clients.client.file')
            ->set('1tomany.ai.clients.client.mock.file', MockFileClient::class)
                ->tag('1tomany.ai.clients.client.file')
            ->set('1tomany.ai.clients.client.openai.file', OpenAIFileClient::class)
                ->tag('1tomany.ai.clients.client.file')

            // Query Actions
            ->alias(CompileQueryActionInterface::class, service('1tomany.ai.clients.action.query.compile'))
            ->alias(ExecuteQueryActionInterface::class, service('1tomany.ai.clients.action.query.execute'))
            ->set('1tomany.ai.clients.action.query.compile', CompileQueryAction::class)
                ->arg('$clientFactory', service('1tomany.ai.clients.factory.client.query'))
            ->set('1tomany.ai.clients.action.query.execute', ExecuteQueryAction::class)
                ->arg('$clientFactory', service('1tomany.ai.clients.factory.client.query'))

            // Query Clients
            ->set('1tomany.ai.clients.client.gemini.query', GeminiQueryClient::class)
                ->tag('1tomany.ai.clients.client.query')
            ->set('1tomany.ai.clients.client.mock.query', MockQueryClient::class)
                ->tag('1tomany.ai.clients.client.query')
            ->set('1tomany.ai.clients.client.openai.query', OpenAIQueryClient::class)
                ->tag('1tomany.ai.clients.client.query')
    ;
};
