<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Requests\TikTok;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class PublishTikTokVideo extends Request
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $publishId,
        protected readonly string $caption,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/post/publish/video/publish/';
    }

    protected function defaultBody(): array
    {
        return [
            'publish_id' => $this->publishId,
            'post_info'  => [
                'title' => $this->caption,
            ],
        ];
    }
}
