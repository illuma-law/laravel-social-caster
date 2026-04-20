<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Requests\Threads;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateThreadsPost extends Request
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $userId,
        protected readonly string $text,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/{$this->userId}/threads";
    }

    protected function defaultBody(): array
    {
        return [
            'text'       => $this->text,
            'media_type' => 'TEXT',
        ];
    }
}
