<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Requests\Facebook;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateFacebookPost extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        protected readonly string $pageId,
        protected readonly array $payload,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/{$this->pageId}/feed";
    }

    protected function defaultBody(): array
    {
        return $this->payload;
    }
}
