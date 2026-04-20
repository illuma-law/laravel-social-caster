<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Requests\Facebook;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class ListFacebookManagedPages extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/me/accounts?fields=id,name,access_token,instagram_business_account{id,username}';
    }
}
