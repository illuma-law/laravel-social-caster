<?php

declare(strict_types=1);

namespace Illuma\SocialCaster;

use Closure;
use Illuma\SocialCaster\Connectors\FacebookConnector;
use Illuma\SocialCaster\Connectors\InstagramConnector;
use Illuma\SocialCaster\Connectors\LinkedInConnector;
use Illuma\SocialCaster\Connectors\ThreadsConnector;
use Illuma\SocialCaster\Connectors\TikTokConnector;
use Illuma\SocialCaster\Connectors\TwitterConnector;
use Illuma\SocialCaster\Contracts\PublishableContent;
use Illuma\SocialCaster\Contracts\SocialCredentials;
use Illuma\SocialCaster\DTOs\PublishResult;
use Illuma\SocialCaster\Enums\SocialPlatform;
use Illuma\SocialCaster\Requests\Facebook\CreateFacebookPost;
use Illuma\SocialCaster\Requests\Instagram\CreateInstagramMedia;
use Illuma\SocialCaster\Requests\Instagram\PublishInstagramMedia;
use Illuma\SocialCaster\Requests\LinkedIn\CreateLinkedInPost;
use Illuma\SocialCaster\Requests\Threads\CreateThreadsPost;
use Illuma\SocialCaster\Requests\TikTok\InitiateTikTokUpload;
use Illuma\SocialCaster\Requests\TikTok\PublishTikTokVideo;
use Illuma\SocialCaster\Requests\Twitter\CreateTweet;
use Illuminate\Support\Facades\Config;
use RuntimeException;
use Saloon\Http\Response;

class SocialCasterManager
{
    /** @var array<int, Closure> */
    protected array $validationCallbacks = [];

    public function registerValidationCallback(Closure $callback): void
    {
        $this->validationCallbacks[] = $callback;
    }

    public function publish(PublishableContent $content, SocialCredentials $credentials): PublishResult
    {
        if ($content->getSocialPlatform() !== $credentials->getSocialPlatform()) {
            throw new RuntimeException('Content platform does not match the selected social account.');
        }

        return match ($credentials->getSocialPlatform()) {
            SocialPlatform::Twitter   => $this->publishToTwitter($content, $credentials),
            SocialPlatform::LinkedIn  => $this->publishToLinkedIn($content, $credentials),
            SocialPlatform::Facebook  => $this->publishToFacebook($content, $credentials),
            SocialPlatform::Instagram => $this->publishToInstagram($content, $credentials),
            SocialPlatform::Threads   => $this->publishToThreads($content, $credentials),
            SocialPlatform::TikTok    => $this->publishToTikTok($content, $credentials),
        };
    }

    /**
     * @return list<string>
     */
    public function validate(PublishableContent $content, ?SocialCredentials $credentials = null): array
    {
        $errors = [];

        $body = $content->getPublishableBody();

        if ($body === null || $body === '') {
            $errors[] = 'Post body is required.';
        }

        $platform = $content->getSocialPlatform();
        $charLimit = Config::get("social-caster.char_limits.{$platform->value}", 3000);

        if (mb_strlen((string) $body) > $charLimit) {
            $errors[] = "Post body exceeds {$platform->value} character limit ({$charLimit}).";
        }

        if ($platform === SocialPlatform::Instagram && ($content->getPublishableImagePath() === null || $content->getPublishableImagePath() === '')) {
            $errors[] = 'Instagram posts require an image.';
        }

        if ($platform === SocialPlatform::TikTok && ($content->getPublishableVideoUrl() === null || $content->getPublishableVideoUrl() === '')) {
            $errors[] = 'TikTok posts require a video URL.';
        }

        if ($credentials !== null) {
            foreach ($this->validationCallbacks as $callback) {
                $result = $callback($content, $credentials);

                if (is_array($result)) {
                    $errors = array_merge($errors, $result);
                } elseif (is_string($result)) {
                    $errors[] = $result;
                }
            }
        }

        return $errors;
    }

    protected function publishToTwitter(PublishableContent $content, SocialCredentials $credentials): PublishResult
    {
        $response = (new TwitterConnector($credentials))->send(
            new CreateTweet($content->getPublishableBody()),
        );
        $this->assertSuccessful($response, 'X/Twitter');

        return new PublishResult(
            externalId: data_get($response->json(), 'data.id'),
            externalUrl: null,
            rawResponse: $response->json(),
        );
    }

    protected function publishToLinkedIn(PublishableContent $content, SocialCredentials $credentials): PublishResult
    {
        $authorUrn = (string) data_get($credentials->getSocialMetadata(), 'author_urn', 'urn:li:person:' . $credentials->getSocialProviderUserId());

        $response = (new LinkedInConnector($credentials))->send(
            new CreateLinkedInPost([
                'author'          => $authorUrn,
                'lifecycleState'  => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary'    => ['text' => $content->getPublishableBody()],
                        'shareMediaCategory' => 'NONE',
                    ],
                ],
                'visibility' => [
                    'com.linkedin.ugc.MemberNetworkVisibility' => Config::get('social-caster.linkedin.default_visibility', 'PUBLIC'),
                ],
            ]),
        );
        $this->assertSuccessful($response, 'LinkedIn');

        return new PublishResult(
            externalId: $response->header('x-restli-id') ?? data_get($response->json(), 'id'),
            externalUrl: null,
            rawResponse: $response->json(),
        );
    }

    protected function publishToFacebook(PublishableContent $content, SocialCredentials $credentials): PublishResult
    {
        $pageId = data_get($credentials->getSocialMetadata(), 'page_id');

        if (! is_string($pageId) || trim($pageId) === '' || $credentials->getSocialPublishingAccessToken() === null) {
            throw new RuntimeException('Facebook page ID and publishing access token are required.');
        }

        $response = (new FacebookConnector($credentials))->send(
            new CreateFacebookPost($pageId, ['message' => $content->getPublishableBody()]),
        );
        $this->assertSuccessful($response, 'Facebook');

        return new PublishResult(
            externalId: data_get($response->json(), 'id'),
            externalUrl: null,
            rawResponse: $response->json(),
        );
    }

    protected function publishToInstagram(PublishableContent $content, SocialCredentials $credentials): PublishResult
    {
        $instagramBusinessAccountId = data_get($credentials->getSocialMetadata(), 'instagram_business_account_id');

        if (! is_string($instagramBusinessAccountId) || trim($instagramBusinessAccountId) === '' || $credentials->getSocialPublishingAccessToken() === null) {
            throw new RuntimeException('Instagram business account ID and publishing access token are required.');
        }

        $imageUrl = $content->getPublishableImagePath();

        if ($imageUrl === null) {
            throw new RuntimeException('Instagram publishing requires an image.');
        }

        $connector = new InstagramConnector($credentials);
        $createResponse = $connector->send(
            new CreateInstagramMedia($instagramBusinessAccountId, [
                'image_url' => $imageUrl,
                'caption'   => $content->getPublishableBody(),
            ]),
        );
        $this->assertSuccessful($createResponse, 'Instagram');

        $creationId = data_get($createResponse->json(), 'id');

        if (! is_string($creationId) || $creationId === '') {
            throw new RuntimeException('Instagram media creation did not return a creation ID.');
        }

        $publishResponse = $connector->send(
            new PublishInstagramMedia($instagramBusinessAccountId, $creationId),
        );
        $this->assertSuccessful($publishResponse, 'Instagram');

        return new PublishResult(
            externalId: data_get($publishResponse->json(), 'id'),
            externalUrl: null,
            rawResponse: [
                'create'  => $createResponse->json(),
                'publish' => $publishResponse->json(),
            ],
        );
    }

    protected function publishToThreads(PublishableContent $content, SocialCredentials $credentials): PublishResult
    {
        $userId = data_get($credentials->getSocialMetadata(), 'threads_user_id');

        if (! is_string($userId) || trim($userId) === '') {
            throw new RuntimeException('Threads user ID is required.');
        }

        $response = (new ThreadsConnector($credentials))->send(
            new CreateThreadsPost(trim($userId), $content->getPublishableBody()),
        );
        $this->assertSuccessful($response, 'Threads');

        return new PublishResult(
            externalId: data_get($response->json(), 'id'),
            externalUrl: null,
            rawResponse: $response->json(),
        );
    }

    protected function publishToTikTok(PublishableContent $content, SocialCredentials $credentials): PublishResult
    {
        $videoUrl = $content->getPublishableVideoUrl();

        if ($videoUrl === null) {
            throw new RuntimeException('TikTok publishing requires a public video URL.');
        }

        $connector = new TikTokConnector($credentials);
        $initResponse = $connector->send(
            new InitiateTikTokUpload($videoUrl),
        );
        $this->assertSuccessful($initResponse, 'TikTok');

        $publishId = data_get($initResponse->json(), 'data.publish_id');

        if (! is_string($publishId) || $publishId === '') {
            throw new RuntimeException('TikTok upload initiation did not return publish_id.');
        }

        $publishResponse = $connector->send(
            new PublishTikTokVideo($publishId, $content->getPublishableTitle() ?? $content->getPublishableBody()),
        );
        $this->assertSuccessful($publishResponse, 'TikTok');

        return new PublishResult(
            externalId: data_get($publishResponse->json(), 'data.publish_id', $publishId),
            externalUrl: null,
            rawResponse: [
                'init'    => $initResponse->json(),
                'publish' => $publishResponse->json(),
            ],
        );
    }

    protected function assertSuccessful(Response $response, string $platform): void
    {
        if ($response->failed()) {
            throw new RuntimeException(
                sprintf('%s API request failed (%d): %s', $platform, $response->status(), $response->body())
            );
        }
    }
}
