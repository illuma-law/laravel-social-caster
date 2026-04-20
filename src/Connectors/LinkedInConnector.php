<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Connectors;

class LinkedInConnector extends PlatformConnector
{
    public function resolveBaseUrl(): string
    {
        return 'https://api.linkedin.com/v2';
    }
}
