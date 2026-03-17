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
use OneToMany\LlmSdk\Contract\Action\Output\GenerateOutputActionInterface;
use OneToMany\LlmSdk\Contract\Enum\Model;
use OneToMany\LlmSdk\Contract\Enum\Vendor;
use OneToMany\LlmSdk\Request\File\UploadFileRequest;
use OneToMany\LlmSdk\Request\Query\CompileQueryRequest;

use function mime_content_type;
use function sprintf;

final readonly class QueryFileHandler
{
    public function __construct(
        private UploadFileActionInterface $uploadFileAction,
        private GenerateOutputActionInterface $generateOutputAction,
    ) {
    }

    public function __invoke(string $path, string $prompt): void
    {
        $vendor = 'gemini';
        // $vendor = OneToMany\LlmSdk\Contract\Enum\Vendor::Gemini;

        $model = 'gemini-2.5-flash';
        // $model = OneToMany\LlmSdk\Contract\Enum\Model::Gemini25Flash;

        if (!$format = mime_content_type($path)) {
            throw new \InvalidArgumentException(sprintf('Failed to determine the format of the file "%s".', $path));
        }

        // Upload the file to cache it with the model vendor
        $uploadFileRequest = new UploadFileRequest('gemini', $path)->usingFormat($format);

        $response = $this->uploadFileAction->act(...[
            'request' => $uploadFileRequest,
        ]);

        // $response instanceof OneToMany\LlmSdk\Response\File\UploadFileResponse
        $uri = $response->getUri();

        // Compile a query to generate output
        $compileQueryRequest = new CompileQueryRequest($model)
            ->withUserPrompt($prompt)
            ->withFile($uri, $format);

        $response = $this->generateOutputAction->act(...[
            'request' => $compileQueryRequest,
        ]);

        // $response instanceof OneToMany\LlmSdk\Response\Output\GenerateOutputResponse
        printf("Model output: %s\n", $response->getOutput());
    }
}
```

## Credits

- [Vic Cherubini](https://github.com/viccherubini), [1:N Labs, LLC](https://1tomany.com)

## License

The MIT License
