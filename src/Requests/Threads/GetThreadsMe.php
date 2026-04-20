<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Requests\Threads;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetThreadsMe extends Request implements HasBody
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/me';
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return [
            'fields' => 'id,username,name',
        ];
    }
}
