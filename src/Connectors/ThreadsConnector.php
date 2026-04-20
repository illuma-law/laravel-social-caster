<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Connectors;

class ThreadsConnector extends PlatformConnector
{
    public function resolveBaseUrl(): string
    {
        return 'https://graph.threads.net/v1.0';
    }
}
