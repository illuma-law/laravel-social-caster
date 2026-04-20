# Laravel Social Caster

[![Latest Version on Packagist](https://img.shields.io/packagist/v/illuma-law/laravel-social-caster.svg?style=flat-square)](https://packagist.org/packages/illuma-law/laravel-social-caster)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/illuma-law/laravel-social-caster/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/illuma-law/laravel-social-caster/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/illuma-law/laravel-social-caster.svg?style=flat-square)](https://packagist.org/packages/illuma-law/laravel-social-caster)

A standalone Laravel package for social media publishing. Supports Twitter, LinkedIn, Facebook, Instagram, Threads, and TikTok.

## TL;DR

```php
use Illuma\SocialCaster\Facades\SocialCaster;

// Publish content to social media
$result = SocialCaster::publish($content, $credentials);

if ($result->successful) {
    echo "Published with ID: {$result->externalId}";
}
```

## Installation

You can install the package via composer:

```bash
composer require illuma-law/laravel-social-caster
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="social-caster-config"
```

This is the contents of the published config file:

```php
return [
    'char_limits' => [
        'twitter' => 280,
        'linkedin' => 3000,
        'facebook' => 63206,
        'instagram' => 2200,
        'threads' => 500,
        'tiktok' => 2000,
    ],
    'linkedin' => [
        'default_visibility' => 'PUBLIC',
    ],
];
```

## Usage

### Define Publishable Content

Your content model should implement the `PublishableContent` interface:

```php
use Illuma\SocialCaster\Contracts\PublishableContent;
use Illuma\SocialCaster\Enums\SocialPlatform;

class BlogPost implements PublishableContent
{
    public function getSocialPlatform(): SocialPlatform
    {
        return SocialPlatform::Twitter;
    }

    public function getPublishableBody(): ?string
    {
        return $this->title;
    }

    public function getPublishableTitle(): ?string
    {
        return $this->title;
    }

    public function getPublishableImagePath(): ?string
    {
        return $this->image_url;
    }

    public function getPublishableVideoUrl(): ?string
    {
        return null;
    }

    public function getPublishableMetadata(): array
    {
        return [];
    }
}
```

### Define Social Credentials

Your social account model should implement the `SocialCredentials` interface:

```php
use Illuma\SocialCaster\Contracts\SocialCredentials;
use Illuma\SocialCaster\Enums\SocialPlatform;

class SocialAccount implements SocialCredentials
{
    public function getSocialPlatform(): SocialPlatform
    {
        return SocialPlatform::Twitter;
    }

    public function getSocialAccessToken(): ?string
    {
        return $this->token;
    }

    public function getSocialPublishingAccessToken(): ?string
    {
        return $this->publishing_token;
    }

    public function getSocialProviderUserId(): ?string
    {
        return $this->provider_user_id;
    }

    public function getSocialMetadata(): array
    {
        return $this->metadata;
    }
}
```

### Publishing

Use the `SocialCaster` facade to publish content:

```php
use Illuma\SocialCaster\Facades\SocialCaster;

$result = SocialCaster::publish($content, $credentials);

echo $result->externalId;
```

### Validation

You can validate content before publishing:

```php
$errors = SocialCaster::validate($content, $credentials);

if (!empty($errors)) {
    // Handle validation errors
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

If you discover any security related issues, please email support@illuma.law instead of using the issue tracker.

## Credits

- [illuma-law](https://github.com/illuma-law)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
