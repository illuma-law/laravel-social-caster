<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Tests\Feature;

use Illuma\SocialCaster\Contracts\PublishableContent;
use Illuma\SocialCaster\Contracts\SocialCredentials;
use Illuma\SocialCaster\DTOs\PublishResult;
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
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    $this->mockClient = new MockClient();
});

function createMockContent(SocialPlatform $platform, array $data = []): PublishableContent
{
    return new class($platform, $data) implements PublishableContent {
        public function __construct(private SocialPlatform $platform, private array $data) {}
        public function getSocialPlatform(): SocialPlatform { return $this->platform; }
        public function getPublishableBody(): ?string { return $this->data['body'] ?? 'Test body'; }
        public function getPublishableTitle(): ?string { return $this->data['title'] ?? 'Test title'; }
        public function getPublishableImagePath(): ?string { return $this->data['image_path'] ?? null; }
        public function getPublishableVideoUrl(): ?string { return $this->data['video_url'] ?? null; }
        public function getPublishableMetadata(): array { return []; }
    };
}

function createMockCredentials(SocialPlatform $platform, array $metadata = []): SocialCredentials
{
    return new class($platform, $metadata) implements SocialCredentials {
        public function __construct(private SocialPlatform $platform, private array $metadata) {}
        public function getSocialPlatform(): SocialPlatform { return $this->platform; }
        public function getSocialAccessToken(): ?string { return 'test-token'; }
        public function getSocialPublishingAccessToken(): ?string { return 'test-publishing-token'; }
        public function getSocialProviderUserId(): ?string { return '123456'; }
        public function getSocialMetadata(): array { return $this->metadata; }
    };
}

test('it can publish to twitter', function () {
    $this->mockClient->addResponse(MockResponse::make(['data' => ['id' => 'twitter-123']], 201), CreateTweet::class);

    $content = createMockContent(SocialPlatform::Twitter);
    $credentials = createMockCredentials(SocialPlatform::Twitter);

    $result = SocialCaster::publish($content, $credentials);

    expect($result)->toBeInstanceOf(PublishResult::class)
        ->and($result->externalId)->toBe('twitter-123');
    
    $this->mockClient->assertSent(CreateTweet::class);
});

test('it can publish to linkedin', function () {
    $this->mockClient->addResponse(MockResponse::make(['id' => 'linkedin-123'], 201), CreateLinkedInPost::class);

    $content = createMockContent(SocialPlatform::LinkedIn);
    $credentials = createMockCredentials(SocialPlatform::LinkedIn);

    $result = SocialCaster::publish($content, $credentials);

    expect($result)->toBeInstanceOf(PublishResult::class)
        ->and($result->externalId)->toBe('linkedin-123');

    $this->mockClient->assertSent(CreateLinkedInPost::class);
});

test('it can publish to facebook', function () {
    $this->mockClient->addResponse(MockResponse::make(['id' => 'facebook-123'], 201), CreateFacebookPost::class);

    $content = createMockContent(SocialPlatform::Facebook);
    $credentials = createMockCredentials(SocialPlatform::Facebook, ['page_id' => 'page-123']);

    $result = SocialCaster::publish($content, $credentials);

    expect($result)->toBeInstanceOf(PublishResult::class)
        ->and($result->externalId)->toBe('facebook-123');

    $this->mockClient->assertSent(CreateFacebookPost::class);
});

test('it can publish to instagram', function () {
    $this->mockClient->addResponse(MockResponse::make(['id' => 'creation-123'], 201), CreateInstagramMedia::class);
    $this->mockClient->addResponse(MockResponse::make(['id' => 'instagram-123'], 201), PublishInstagramMedia::class);

    $content = createMockContent(SocialPlatform::Instagram, ['image_path' => 'https://example.com/image.jpg']);
    $credentials = createMockCredentials(SocialPlatform::Instagram, ['instagram_business_account_id' => 'ig-123']);

    $result = SocialCaster::publish($content, $credentials);

    expect($result)->toBeInstanceOf(PublishResult::class)
        ->and($result->externalId)->toBe('instagram-123');

    $this->mockClient->assertSent(CreateInstagramMedia::class);
    $this->mockClient->assertSent(PublishInstagramMedia::class);
});

test('it can publish to threads', function () {
    $this->mockClient->addResponse(MockResponse::make(['id' => 'threads-123'], 201), CreateThreadsPost::class);

    $content = createMockContent(SocialPlatform::Threads);
    $credentials = createMockCredentials(SocialPlatform::Threads, ['threads_user_id' => 'user-123']);

    $result = SocialCaster::publish($content, $credentials);

    expect($result)->toBeInstanceOf(PublishResult::class)
        ->and($result->externalId)->toBe('threads-123');

    $this->mockClient->assertSent(CreateThreadsPost::class);
});

test('it can publish to tiktok', function () {
    $this->mockClient->addResponse(MockResponse::make(['data' => ['publish_id' => 'tiktok-init-123']], 200), InitiateTikTokUpload::class);
    $this->mockClient->addResponse(MockResponse::make(['data' => ['publish_id' => 'tiktok-123']], 200), PublishTikTokVideo::class);

    $content = createMockContent(SocialPlatform::TikTok, ['video_url' => 'https://example.com/video.mp4']);
    $credentials = createMockCredentials(SocialPlatform::TikTok);

    $result = SocialCaster::publish($content, $credentials);

    expect($result)->toBeInstanceOf(PublishResult::class)
        ->and($result->externalId)->toBe('tiktok-123');

    $this->mockClient->assertSent(InitiateTikTokUpload::class);
    $this->mockClient->assertSent(PublishTikTokVideo::class);
});

test('it validates character limits', function () {
    $content = createMockContent(SocialPlatform::Twitter, ['body' => str_repeat('a', 281)]);
    $errors = SocialCaster::validate($content);
    expect($errors)->toContain('Post body exceeds twitter character limit (280).');

    $content = createMockContent(SocialPlatform::Twitter, ['body' => str_repeat('a', 280)]);
    $errors = SocialCaster::validate($content);
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
