# AI and LLM SDK Bundle for Symfony

This package wraps the `1tomany/llm-sdk` library into an easy to use Symfony bundle.

## Installation

Install the bundle using Composer:

```
composer require 1tomany/llm-sdk-bundle
```

## Configuration

Below is the complete configuration for this bundle. To customize it for your Symfony application, create a file named `onetomany_llmsdk.yaml` in `config/packages/` and make the necessary changes.

```yaml
onetomany_llmsdk:
    anthropic:
        api_key: "%env(ANTHROPIC_API_KEY)%"
        api_version: "2023-06-01"
        http_client: "http_client"
        serializer: "serializer"
    gemini:
        api_key: "%env(GEMINI_API_KEY)%"
        api_version: "v1beta"
        http_client: "http_client"
        serializer: "serializer"
    openai:
        api_key: "%env(OPENAI_API_KEY)%"
        http_client: "http_client"
        serializer: "serializer"

when@dev:
    onetomany_llmsdk:
        mock:
            enabled: true
```

By default, the `http_client` and `serializer` properties in the `anthropic`, `gemini` and `openai` blocks use the `@http_client` and `@serializer` services defined in a standard Symfony application. You're free to use your own scoped HTTP client or serializer services.

If you wish to disable a vendor, simply delete the configuration block from the file. For example, if your application only uses Gemini, you would delete the `anthropic` and `openai` blocks, leaving you with:

```yaml
onetomany_llmsdk:
    gemini:
        api_key: "%env(GEMINI_API_KEY)%"
```

You'll also have to define the API keys in your `.env` file or by using the [Symfony Secrets](https://symfony.com/doc/current/configuration/secrets.html) component.

## Commands

- `onetomany:llm-sdk:list-models` console command to list all available models by LLM client

## Usage

Any action interface can be injected into a service. Because you can have multiple clients loaded in at once, the model passed into the request dictates what client to use. This makes it very easy to allow your users to select amongst any client supported by the core `1tomany/llm-sdk` library.

```php
<?php

namespace App\File\Action\Handler;

use OneToMany\LlmSdk\Contract\Action\File\UploadFileActionInterface;
use OneToMany\LlmSdk\Contract\Action\Query\ExecuteQueryActionInterface;

use function mime_content_type;

final readonly class QueryFileHandler
{
    public function __construct(
        private UploadFileActionInterface $uploadFileAction,
        private ExecuteQueryActionInterface $executeQueryAction,
    ) {
    }

    public function __invoke(string $path, string $prompt): void
    {
        $model = 'gemini-2.5-flash';

        /**
         * @var non-empty-lowercase-string $format
         */
        $format = mime_content_type($path);

        // Upload the file to cache it with the model
        $uploadRequest = new UploadRequest($model)->atPath($path)->withFormat($format);

        $response = $this->uploadFileAction->act(...[
            'request' => $uploadRequest,
        ]);

        // $response instanceof OneToMany\LlmSdk\Response\File\UploadResponse
        $fileUri = $response->getUri();

        // Compile and execute a query using the cached file
        $compileRequest = new CompileRequest($model)->withPrompt($prompt)->withFileUri($fileUri, $format);

        $response = $this->executeQueryAction->act(...[
            'request' => $compileRequest,
        ]);

        // $response instanceof OneToMany\LlmSdk\Response\Query\ExecuteResponse
        printf("Model output: %s\n", $response->getOutput());
    }
}
```

## Credits

- [Vic Cherubini](https://github.com/viccherubini), [1:N Labs, LLC](https://1tomany.com)

## License

The MIT License
