# PHP AI and LLM Bundle for Symfony

This package wraps the [`1tomany/php-ai`](https://github.com/1tomany/php-ai) library into an easy to use Symfony bundle.

## Installation

Install the bundle using Composer:

```console
composer require 1tomany/php-ai-bundle
```

## Configuration

To change the default configuration, create a file named `onetomany_ai.yaml` in `config/packages/` with the following contents and adjust accordingly:

```yaml
onetomany_ai:
    transport:
        http_client: http_client

    gemini:
        api_key: "%env(GEMINI_API_KEY)%"
        api_version: v1beta

    openai:
        api_key: "%env(OPENAI_API_KEY)%"
        api_version: v1
```

The transport uses Symfony's `http_client` service by default, so the `transport` block can normally be omitted. The bundle registers its provider-specific query normalizers with Symfony's `serializer` service and injects that serializer into the transport and providers.

Provider blocks are optional. If a provider block is omitted, that provider is not registered with the `OneToMany\AI\AiClient` facade.

## Usage

Inject the `OneToMany\AI\Contract\AiClientInterface` facade and use its resources:

```php
<?php

use OneToMany\AI\Contract\AiClientInterface;
use OneToMany\AI\Model;
use OneToMany\AI\ModelVendor;
use OneToMany\AI\Resource\File\LocalFile;
use OneToMany\AI\Resource\Prompt\InputFile;
use OneToMany\AI\Resource\Prompt\Prompt;

use function sprintf;

final readonly class AnalyzeFile
{
    public function __construct(
        private AiClientInterface $aiClient,
    ) {
    }

    public function __invoke(string $path): string
    {
        // Upload a file to the LLM vendor
        $localFile = new LocalFile($path);

        $remoteFile = $this->aiClient->files->upload(
            ModelVendor::OpenAI, $localFile,
        );

        $prompt = Prompt::create(
            'openai:gpt-5.4',
            'Summarize this file.',
            new InputFile(
                $remoteFile->getId(),
                $localFile->getType(),
            ),
        );

        // Compile a prompt into a query and send it
        $response = $this->aiClient->prompts->send($prompt);

        if (null !== $response->getError()) {
            throw new \RuntimeException(sprintf('Error: %s.', $response->getError()));
        }

        if (null !== $response->getRefusal()) {
            throw new \RuntimeException(sprintf('Refusal: %s.', $response->getRefusal()));
        }

        if (null === $response->getText()) {
            throw new \RuntimeException('Query failed to generate output.');
        }

        return $response->getText();
    }
}
```

## Credits

- [Vic Cherubini](https://github.com/viccherubini), [1:N Labs, LLC](https://1tomany.com)

## License

The MIT License
