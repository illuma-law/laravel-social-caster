<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Requests\Instagram;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class PublishInstagramMedia extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $instagramBusinessAccountId,
        protected readonly string $creationId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/{$this->instagramBusinessAccountId}/media_publish";
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return [
            'creation_id' => $this->creationId,
        ];
    }
}
