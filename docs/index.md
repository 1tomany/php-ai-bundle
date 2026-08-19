# OneToMany AI Bundle

The OneToMany AI Bundle connects the framework-independent `1tomany/ai-php` library to Symfony's dependency-injection container.

## Installation

```console
composer require 1tomany/ai-php-bundle
```

Register `OneToMany\AI\Bundle\AIBundle` in `config/bundles.php` when it is not enabled automatically.

## Bundle configuration

The configuration alias is `onetomany_ai`:

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

### Transport

The bundle creates one shared `OneToMany\AI\Bridge\Transport`. This is safe because credentials remain in the provider implementations and are attached to individual requests rather than stored in the transport.

The standard Symfony `http_client` and `serializer` services are used by default. Custom implementations can be selected by service ID:

```yaml
onetomany_ai:
    transport:
        http_client: app.ai_http_client
        serializer: app.ai_serializer
```

A custom serializer must support JSON and the object, array, date-time, enum, and unwrapping denormalization used by the core library's response DTOs.

### Providers

Provider configuration is opt-in. Defining a provider registers both its file and query bridge implementations. Omitting the block leaves the provider unregistered.

```yaml
# Gemini only
onetomany_ai:
    gemini:
        api_key: '%env(GEMINI_API_KEY)%'
```

The default API versions are `v1beta` for Gemini and `v1` for OpenAI.

## Registered services

The following types are available for autowiring:

- `OneToMany\AI\AI`
- `OneToMany\AI\Resource\Files`
- `OneToMany\AI\Contract\Resource\FilesInterface`
- `OneToMany\AI\Resource\Queries`
- `OneToMany\AI\Contract\Resource\QueriesInterface`

Provider bridges and query request normalizers are private implementation details. They are registered explicitly without enabling bundle-wide autowiring or autoconfiguration.

## Resource usage

Use the `AI` facade when a service needs multiple resource types:

```php
use OneToMany\AI\AI;

final readonly class AIService
{
    public function __construct(
        private AI $ai,
    ) {
    }
}
```

Use a resource interface for narrower dependencies:

```php
use OneToMany\AI\Contract\Resource\QueriesInterface;

final readonly class QueryRunner
{
    public function __construct(
        private QueriesInterface $queries,
    ) {
    }
}
```

Refer to the core [`1tomany/ai-php`](https://github.com/1tomany/ai-php) project for resource and DTO documentation.
