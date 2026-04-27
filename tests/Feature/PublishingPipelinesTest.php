<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Tests\Feature;

use Illuma\SocialCaster\Contracts\PublishableContent;
use Illuma\SocialCaster\Contracts\SocialCredentials;
use Illuma\SocialCaster\Enums\SocialPlatform;
use Illuma\SocialCaster\Facades\SocialCaster;
use Illuma\SocialCaster\Requests\Facebook\CreateFacebookPost;
use Illuma\SocialCaster\Requests\Instagram\CreateInstagramMedia;
use Illuma\SocialCaster\Requests\Instagram\PublishInstagramMedia;
use Illuma\SocialCaster\Requests\LinkedIn\CreateLinkedInPost;
use Illuma\SocialCaster\Requests\Threads\CreateThreadsPost;
use Illuma\SocialCaster\Requests\TikTok\InitiateTikTokUpload;
use Illuma\SocialCaster\Requests\TikTok\PublishTikTokVideo;
use Illuma\SocialCaster\Requests\Twitter\CreateTweet;
use RuntimeException;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

/**
 * @param  array<string, mixed>  $data
 */
function createMockContent(SocialPlatform $platform, array $data = []): PublishableContent
{
    return new class($platform, $data) implements PublishableContent
    {
        /**
         * @param  array<string, mixed>  $data
         */
        public function __construct(private SocialPlatform $platform, private array $data) {}

        public function getSocialPlatform(): SocialPlatform
        {
            return $this->platform;
        }

        public function getPublishableBody(): ?string
        {
            $body = $this->data['body'] ?? 'Test body';

            return is_string($body) ? $body : null;
        }

        public function getPublishableTitle(): ?string
        {
            $title = $this->data['title'] ?? 'Test title';

            return is_string($title) ? $title : null;
        }

        public function getPublishableImagePath(): ?string
        {
            $path = $this->data['image_path'] ?? null;

            return is_string($path) ? $path : null;
        }

        public function getPublishableVideoUrl(): ?string
        {
            $url = $this->data['video_url'] ?? null;

            return is_string($url) ? $url : null;
        }

        /**
         * @return array<string, mixed>
         */
        public function getPublishableMetadata(): array
        {
            return [];
        }
    };
}

/**
 * @param  array<string, mixed>  $metadata
 */
function createMockCredentials(SocialPlatform $platform, array $metadata = []): SocialCredentials
{
    return new class($platform, $metadata) implements SocialCredentials
    {
        /**
         * @param  array<string, mixed>  $metadata
         */
        public function __construct(private SocialPlatform $platform, private array $metadata) {}

        public function getSocialPlatform(): SocialPlatform
        {
            return $this->platform;
        }

        public function getSocialAccessToken(): string
        {
            return 'test-token';
        }

        public function getSocialPublishingAccessToken(): string
        {
            $token = $this->metadata['publishing_token'] ?? 'test-publishing-token';

            return is_string($token) ? $token : 'test-publishing-token';
        }

        public function getSocialProviderUserId(): string
        {
            return '123456';
        }

        /**
         * @return array<string, mixed>
         */
        public function getSocialMetadata(): array
        {
            return $this->metadata;
        }
    };
}

test('it can publish to twitter', function () {
    Saloon::fake([
        CreateTweet::class => MockResponse::make(['data' => ['id' => 'twitter-123']], 201),
    ]);

    $content = createMockContent(SocialPlatform::Twitter);
    $credentials = createMockCredentials(SocialPlatform::Twitter);

    $result = SocialCaster::publish($content, $credentials);

    expect($result)->not->toBeNull()
        ->and($result->externalId)->toBe('twitter-123');

    Saloon::assertSent(CreateTweet::class);
});

test('it can publish to linkedin', function () {
    Saloon::fake([
        CreateLinkedInPost::class => MockResponse::make(['id' => 'linkedin-123'], 201),
    ]);

    $content = createMockContent(SocialPlatform::LinkedIn);
    $credentials = createMockCredentials(SocialPlatform::LinkedIn);

    $result = SocialCaster::publish($content, $credentials);

    expect($result)->not->toBeNull()
        ->and($result->externalId)->toBe('linkedin-123');

    Saloon::assertSent(CreateLinkedInPost::class);
});

test('it can publish to facebook', function () {
    Saloon::fake([
        CreateFacebookPost::class => MockResponse::make(['id' => 'facebook-123'], 201),
    ]);

    $content = createMockContent(SocialPlatform::Facebook);
    $credentials = createMockCredentials(SocialPlatform::Facebook, ['page_id' => 'page-123']);

    $result = SocialCaster::publish($content, $credentials);

    expect($result)->not->toBeNull()
        ->and($result->externalId)->toBe('facebook-123');

    Saloon::assertSent(CreateFacebookPost::class);
});

test('it can publish to instagram', function () {
    Saloon::fake([
        CreateInstagramMedia::class  => MockResponse::make(['id' => 'creation-123'], 201),
        PublishInstagramMedia::class => MockResponse::make(['id' => 'instagram-123'], 201),
    ]);

    $content = createMockContent(SocialPlatform::Instagram, ['image_path' => 'https://example.com/image.jpg']);
    $credentials = createMockCredentials(SocialPlatform::Instagram, ['instagram_business_account_id' => 'ig-123']);

    $result = SocialCaster::publish($content, $credentials);

    expect($result)->not->toBeNull()
        ->and($result->externalId)->toBe('instagram-123');

    Saloon::assertSent(CreateInstagramMedia::class);
    Saloon::assertSent(PublishInstagramMedia::class);
});

test('it can publish to threads', function () {
    Saloon::fake([
        CreateThreadsPost::class => MockResponse::make(['id' => 'threads-123'], 201),
    ]);

    $content = createMockContent(SocialPlatform::Threads);
    $credentials = createMockCredentials(SocialPlatform::Threads, ['threads_user_id' => 'user-123']);

    $result = SocialCaster::publish($content, $credentials);

    expect($result)->not->toBeNull()
        ->and($result->externalId)->toBe('threads-123');

    Saloon::assertSent(CreateThreadsPost::class);
});

test('it can publish to tiktok', function () {
    Saloon::fake([
        InitiateTikTokUpload::class => MockResponse::make(['data' => ['publish_id' => 'tiktok-init-123']], 200),
        PublishTikTokVideo::class   => MockResponse::make(['data' => ['publish_id' => 'tiktok-123']], 200),
    ]);

    $content = createMockContent(SocialPlatform::TikTok, ['video_url' => 'https://example.com/video.mp4']);
    $credentials = createMockCredentials(SocialPlatform::TikTok);

    $result = SocialCaster::publish($content, $credentials);

    expect($result)->not->toBeNull()
        ->and($result->externalId)->toBe('tiktok-123');

    Saloon::assertSent(InitiateTikTokUpload::class);
    Saloon::assertSent(PublishTikTokVideo::class);
});

test('it validates character limits', function () {
    $content = createMockContent(SocialPlatform::Twitter, ['body' => str_repeat('a', 281)]);
    $credentials = createMockCredentials(SocialPlatform::Twitter);
    $errors = SocialCaster::validate($content, $credentials);
    expect($errors)->toContain('Post body exceeds twitter character limit (280).');

    $content = createMockContent(SocialPlatform::Twitter, ['body' => str_repeat('a', 280)]);
    $errors = SocialCaster::validate($content, $credentials);
    expect($errors)->toBeEmpty();
});

test('it uses custom validation callbacks', function () {
    SocialCaster::registerValidationCallback(function (PublishableContent $content, SocialCredentials $credentials) {
        if ($credentials->getSocialProviderUserId() === '123456') {
            return 'Custom validation error';
        }

        return [];
    });

    $content = createMockContent(SocialPlatform::Twitter);
    $credentials = createMockCredentials(SocialPlatform::Twitter);

    $errors = SocialCaster::validate($content, $credentials);
    expect($errors)->toContain('Custom validation error');
});

test('it throws exception when platform does not match', function () {
    $content = createMockContent(SocialPlatform::Twitter);
    $credentials = createMockCredentials(SocialPlatform::Facebook);

    SocialCaster::publish($content, $credentials);
})->throws(RuntimeException::class, 'Content platform does not match the selected social account.');

test('it validates required body', function () {
    $content = createMockContent(SocialPlatform::Twitter, ['body' => '']);
    $errors = SocialCaster::validate($content);
    expect($errors)->toContain('Post body is required.');
});

test('it validates instagram image requirement', function () {
    $content = createMockContent(SocialPlatform::Instagram, ['image_path' => '']);
    $errors = SocialCaster::validate($content);
    expect($errors)->toContain('Instagram posts require an image.');
});

test('it validates tiktok video requirement', function () {
    $content = createMockContent(SocialPlatform::TikTok, ['video_url' => '']);
    $errors = SocialCaster::validate($content);
    expect($errors)->toContain('TikTok posts require a video URL.');
});

test('it validates credentials existence', function () {
    $content = createMockContent(SocialPlatform::Twitter);
    $errors = SocialCaster::validate($content, null);
    expect($errors)->toContain('No social account connected for publishing.');
});

test('it throws exception for missing facebook page id', function () {
    $content = createMockContent(SocialPlatform::Facebook);
    $credentials = createMockCredentials(SocialPlatform::Facebook, ['page_id' => '']);

    SocialCaster::publish($content, $credentials);
})->throws(RuntimeException::class, 'Facebook page ID and publishing access token are required.');

test('it throws exception for missing instagram business account id', function () {
    $content = createMockContent(SocialPlatform::Instagram, ['image_path' => 'https://example.com/image.jpg']);
    $credentials = createMockCredentials(SocialPlatform::Instagram, ['instagram_business_account_id' => '']);

    SocialCaster::publish($content, $credentials);
})->throws(RuntimeException::class, 'Instagram business account ID and publishing access token are required.');

test('it throws exception for missing threads user id', function () {
    $content = createMockContent(SocialPlatform::Threads);
    $credentials = createMockCredentials(SocialPlatform::Threads, ['threads_user_id' => '']);

    SocialCaster::publish($content, $credentials);
})->throws(RuntimeException::class, 'Threads user ID is required.');

test('it throws exception for missing tiktok video url', function () {
    $content = createMockContent(SocialPlatform::TikTok, ['video_url' => null]);
    $credentials = createMockCredentials(SocialPlatform::TikTok);

    SocialCaster::publish($content, $credentials);
})->throws(RuntimeException::class, 'TikTok publishing requires a public video URL.');
