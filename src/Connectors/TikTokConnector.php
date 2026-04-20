<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Connectors;

class TikTokConnector extends PlatformConnector
{
    public function resolveBaseUrl(): string
    {
        return 'https://open.tiktokapis.com/v2';
    }
}
