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
use OneToMany\AI\Contract\AiClientInterface;
use OneToMany\AI\Model;
use OneToMany\AI\Vendor;
use OneToMany\AI\Resource\File\LocalFile;
use OneToMany\AI\Resource\Query\Prompt;

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
        $file = $this->aiClient->files->upload(
            Provider::OpenAI,
            new LocalFile($path, 'application/pdf'),
        );

        // Run a query against the uploaded file
        $response = $this->aiClient->queries->compileAndRun(
            Model::openai('gpt-5.4'),
            Prompt::with('Summarize this file.', $file),
        );

        if (null !== $response->error) {
            throw new \RuntimeException(sprintf('Query failed: %s.', $response->error));
        }

        if (null === $response->text) {
            throw new \RuntimeException('Query failed to generate output.');
        }

        return $response->text;
    }
}
```

The `OneToMany\AI\Contract\Resource\FilesInterface` and `OneToMany\AI\Contract\Resource\QueriesInterface` interfaces are also registered as autowiring aliases to the `OneToMany\AI\Resource\Files` and `OneToMany\AI\Resource\Queries` when a service only needs one resource, though it is recommended to use the `OneToMany\AI\AiClient` facade.

## Credits

- [Vic Cherubini](https://github.com/viccherubini), [1:N Labs, LLC](https://1tomany.com)

## License

The MIT License
