<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Requests\Instagram;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateInstagramMedia extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        protected readonly string $instagramBusinessAccountId,
        protected readonly array $payload,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/{$this->instagramBusinessAccountId}/media";
    }

    protected function defaultBody(): array
    {
        return $this->payload;
    }
}
