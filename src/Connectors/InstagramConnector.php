<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Connectors;

class InstagramConnector extends PlatformConnector
{
    public function resolveBaseUrl(): string
    {
        return 'https://graph.facebook.com/v21.0';
    }
}
