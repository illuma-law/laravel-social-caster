<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Requests\Twitter;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTwitterMe extends Request implements HasBody
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/users/me';
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return [
            'user.fields' => 'id,name,username',
        ];
    }
}
