<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Connectors;

use Illuma\SocialCaster\Contracts\SocialCredentials;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

abstract class PlatformConnector extends Connector
{
    use AcceptsJson;

    public function __construct(
        protected readonly SocialCredentials $credentials,
    ) {}

    protected function defaultAuth(): ?TokenAuthenticator
    {
        $token = $this->credentials->getSocialAccessToken();

        return $token ? new TokenAuthenticator($token) : null;
    }

    protected function defaultConfig(): array
    {
        return [
            'connect_timeout' => 10,
            'timeout' => 30,
        ];
    }
}
