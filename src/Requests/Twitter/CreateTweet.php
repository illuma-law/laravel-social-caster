<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Requests\Twitter;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateTweet extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $text,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/tweets';
    }

    protected function defaultBody(): array
    {
        return [
            'text' => $this->text,
        ];
    }
}
