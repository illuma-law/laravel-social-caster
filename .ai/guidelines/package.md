---
description: Unified social media publishing API for Laravel — contract-driven, multi-platform, validated
---

# laravel-social-caster

Unified API for publishing content to multiple social media platforms. Contract-driven design with built-in validation.

## Namespace

`Illuma\SocialCaster`

## Key Classes & Facades

- `SocialCaster` facade — main entry point for publishing
- `SocialPlatform` enum — `Twitter`, `LinkedIn`, `Facebook`, `Instagram`, `Threads`, `TikTok`
- `PublishableContent` contract — implement on models/DTOs to define publishable content
- `SocialCredentials` contract — implement on models to supply platform credentials

## Implementing PublishableContent

```php
use Illuma\SocialCaster\Contracts\PublishableContent;
use Illuma\SocialCaster\Enums\SocialPlatform;

class SocialPost implements PublishableContent
{
    public function getContentBody(): string { return $this->body; }
    public function getContentPlatform(): SocialPlatform { return $this->platform; }
    public function getContentMetadata(): array { return []; }
}
```

## Implementing SocialCredentials

```php
use Illuma\SocialCaster\Contracts\SocialCredentials;
use Illuma\SocialCaster\Enums\SocialPlatform;

class SocialAccount implements SocialCredentials
{
    public function getPlatform(): SocialPlatform { return $this->platform; }
    public function getCredentials(): array { return $this->credentials; }
}
```

## Publishing

```php
use Illuma\SocialCaster\Facades\SocialCaster;

// Validate then publish
$result = SocialCaster::validate($content, $credentials);
if ($result->passes()) {
    SocialCaster::publish($content, $credentials);
}

// Or publish directly (throws on validation failure)
SocialCaster::publish($content, $credentials);
```

## Config

Publish: `php artisan vendor:publish --tag="social-caster-config"`

Set per-platform credentials in `config/social-caster.php` or supply via `SocialCredentials` contract.

## Registration (AppServiceProvider)

```php
use Illuma\SocialCaster\Contracts\PublishableContent;
use Illuma\SocialCaster\Contracts\SocialCredentials;
use Illuma\SocialCaster\Facades\SocialCaster;

SocialCaster::resolvePublishableContentUsing(fn ($post) => $post instanceof PublishableContent ? $post : null);
SocialCaster::resolveCredentialsUsing(fn ($account) => $account instanceof SocialCredentials ? $account : null);
```
