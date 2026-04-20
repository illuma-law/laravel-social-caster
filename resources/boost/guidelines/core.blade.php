# illuma-law/laravel-social-caster

Social media publishing for Twitter, LinkedIn, Facebook, Instagram, Threads, and TikTok.

## Usage

### 1. Implement Interfaces

**PublishableContent**:
```php
class BlogPost implements PublishableContent {
    public function getSocialPlatform(): SocialPlatform { return SocialPlatform::Twitter; }
    public function getPublishableBody(): ?string { return $this->title; }
    // ...
}
```

**SocialCredentials**:
```php
class SocialAccount implements SocialCredentials {
    public function getSocialAccessToken(): ?string { return $this->token; }
    // ...
}
```

### 2. Publishing

```php
use Illuma\SocialCaster\Facades\SocialCaster;

$result = SocialCaster::publish($content, $credentials);

if ($result->successful) {
    // $result->externalId
}
```

### 3. Validation

```php
$errors = SocialCaster::validate($content, $credentials);
```

## Configuration

Publish config: `php artisan vendor:publish --tag="social-caster-config"`

Options in `config/social-caster.php`:
- `char_limits`: Platform character limits.
- `linkedin.default_visibility`: PUBLIC/CONNECTIONS.
