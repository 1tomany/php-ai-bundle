# OneToMany AI Bundle

Symfony bindings for [`1tomany/ai-php`](https://github.com/1tomany/ai-php).

## Installation

Install the bundle with Composer:

```console
composer require 1tomany/ai-php-bundle
```

Enable the bundle in `config/bundles.php`:

```php
use OneToMany\AI\Bundle\AIBundle;

return [
    AIBundle::class => ['all' => true],
];
```

## Configuration

Create `config/packages/onetomany_ai.yaml` and configure one or both providers:

```yaml
onetomany_ai:
    transport:
        http_client: http_client
        serializer: serializer

    gemini:
        api_key: '%env(GEMINI_API_KEY)%'
        api_version: v1beta

    openai:
        api_key: '%env(OPENAI_API_KEY)%'
        api_version: v1
```

The transport uses Symfony's `http_client` and `serializer` services by default, so the `transport` block can normally be omitted. A custom service ID can be supplied for either dependency.

Provider blocks are optional. If a provider block is omitted, that provider is not registered for file or query operations.

## Usage

Inject the `AI` facade and use its resources:

```php
use OneToMany\AI\AI;
use OneToMany\AI\Model;
use OneToMany\AI\Provider;
use OneToMany\AI\Resource\File\LocalFile;
use OneToMany\AI\Resource\Query\Prompt;

final readonly class AnalyzeFile
{
    public function __construct(
        private AI $ai,
    ) {
    }

    public function __invoke(string $path): string
    {
        $file = $this->ai->files->upload(
            Provider::OpenAI,
            new LocalFile($path, 'application/pdf'),
        );

        $prompt = (new Prompt())
            ->addInputText('Summarize this file.')
            ->addRemoteFile($file);

        $response = $this->ai->queries->compileAndRun(
            Model::openai('gpt-5.4'),
            $prompt,
        );

        return $response->text ?? '';
    }
}
```

`FilesInterface` and `QueriesInterface` are also registered as autowiring aliases when a service only needs one resource.

See [the complete documentation](docs/index.md) for configuration and integration details.

## License

The MIT License.
