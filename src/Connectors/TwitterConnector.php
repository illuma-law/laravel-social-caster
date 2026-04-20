<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Connectors;

class TwitterConnector extends PlatformConnector
{
    public function resolveBaseUrl(): string
    {
        return 'https://api.twitter.com/2';
    }
}
