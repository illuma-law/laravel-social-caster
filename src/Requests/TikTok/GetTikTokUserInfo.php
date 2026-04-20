<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Requests\TikTok;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTikTokUserInfo extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/user/info/';
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return [
            'fields' => 'open_id,display_name,avatar_url',
        ];
    }
}
