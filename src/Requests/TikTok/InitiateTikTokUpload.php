<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Requests\TikTok;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class InitiateTikTokUpload extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly ?string $videoUrl,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/post/publish/inbox/video/init/';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return [
            'source_info' => [
                'source'    => $this->videoUrl !== null ? 'PULL_FROM_URL' : 'FILE_UPLOAD',
                'video_url' => $this->videoUrl,
            ],
        ];
    }
}
