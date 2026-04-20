<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Requests\LinkedIn;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateLinkedInPost extends Request
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        protected readonly array $payload,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/ugcPosts';
    }

    protected function defaultBody(): array
    {
        return $this->payload;
    }
}
