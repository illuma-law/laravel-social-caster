# Laravel Social Caster

[![Latest Version on Packagist](https://img.shields.io/packagist/v/illuma-law/laravel-social-caster.svg?style=flat-square)](https://packagist.org/packages/illuma-law/laravel-social-caster)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/illuma-law/laravel-social-caster/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/illuma-law/laravel-social-caster/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/illuma-law/laravel-social-caster.svg?style=flat-square)](https://packagist.org/packages/illuma-law/laravel-social-caster)

A standalone Laravel package providing an elegant, unified API for publishing content to multiple social media platforms.

Instead of wrestling with half a dozen different SDKs, API documentation sites, and conflicting payload structures, Social Caster provides a single, uniform interface to broadcast content to Twitter, LinkedIn, Facebook, Instagram, Threads, and TikTok.

## Features

- **Unified Interface:** Publish text, images, and videos across multiple networks using a single `publish()` method.
- **Contract-Driven:** Implement `PublishableContent` on your Models (like a `Post` or `Tweet` model) and `SocialCredentials` on your auth models to decouple business logic from the API.
- **Built-in Validation:** Validates character limits, required media, and platform-specific constraints before making any network calls.
- **Supported Platforms:** 
  - Twitter (X)
  - LinkedIn (Profiles and Organization Pages)
  - Facebook (Pages)
  - Instagram
  - Threads
  - TikTok

## Installation

You can install the package via composer:

```bash
composer require illuma-law/laravel-social-caster
```

Publish the config file:

```bash
php artisan vendor:publish --tag="social-caster-config"
```

## Configuration

The published `config/social-caster.php` defines platform-specific constraints and defaults:

```php
return [
    // Pre-flight validation checks will fail if content exceeds these limits
    'char_limits' => [
        'twitter' => 280,
        'linkedin' => 3000,
        'facebook' => 63206,
        'instagram' => 2200,
        'threads' => 500,
        'tiktok' => 2000,
    ],
    'linkedin' => [
        'default_visibility' => 'PUBLIC', // PUBLIC or CONNECTIONS
    ],
];
```

## Usage & Integration

### 1. Define Publishable Content

Any model or DTO you wish to publish must implement the `PublishableContent` contract. This tells Social Caster exactly what to send to the platform.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuma\SocialCaster\Contracts\PublishableContent;
use Illuma\SocialCaster\Enums\SocialPlatform;

class BlogPost extends Model implements PublishableContent
{
    // Ensure the model knows which platform it targets
    public function getSocialPlatform(): SocialPlatform
    {
        return SocialPlatform::Twitter;
    }

    public function getPublishableBody(): ?string
    {
        return $this->social_caption; // e.g., "Check out our new post! #laravel"
    }

    public function getPublishableTitle(): ?string
    {
        return $this->title;
    }

    public function getPublishableImagePath(): ?string
    {
        // Must be a publicly accessible URL or local path depending on the platform
        return $this->header_image_url; 
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

### 2. Define Social Credentials

The account or token storage model must implement the `SocialCredentials` contract so Social Caster knows how to authenticate the request.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuma\SocialCaster\Contracts\SocialCredentials;
use Illuma\SocialCaster\Enums\SocialPlatform;

class SocialAccount extends Model implements SocialCredentials
{
    public function getSocialPlatform(): SocialPlatform
    {
        return SocialPlatform::from($this->provider_name); // 'twitter', 'linkedin', etc.
    }

    public function getSocialAccessToken(): ?string
    {
        return $this->oauth_token;
    }

    public function getSocialPublishingAccessToken(): ?string
    {
        // Used by platforms that require a separate token for publishing (e.g. FB Pages)
        return $this->page_access_token ?? $this->oauth_token;
    }

    public function getSocialProviderUserId(): ?string
    {
        // The external platform's ID for this user/page
        return $this->provider_user_id; 
    }

    public function getSocialMetadata(): array
    {
        // Extra auth tokens (like Twitter secrets) can be passed here
        return [
            'token_secret' => $this->oauth_token_secret,
            'client_id' => config('services.twitter.client_id'),
            'client_secret' => config('services.twitter.client_secret'),
        ];
    }
}
```

### 3. Validation and Publishing

You can now use the `SocialCaster` facade to validate and publish the content.

```php
use Illuma\SocialCaster\Facades\SocialCaster;

$post = BlogPost::find(1);
$account = SocialAccount::where('provider_name', 'twitter')->first();

// 1. Validate first (Optional but recommended)
$errors = SocialCaster::validate($post, $account);

if (!empty($errors)) {
    // Returns an array of error strings (e.g. ["Content exceeds 280 characters"])
    return back()->withErrors($errors);
}

// 2. Publish
$result = SocialCaster::publish($post, $account);

if ($result->successful) {
    $post->update(['external_social_id' => $result->externalId]);
    echo "Successfully published! ID: {$result->externalId}";
} else {
    // $result->error contains the API error message
    Log::error("Failed to publish", ['error' => $result->error]);
}
```

## Testing

The package provides a comprehensive Pest test suite to ensure API connectors handle payloads correctly.

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
