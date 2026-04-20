<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Enums;

enum SocialPlatform: string
{
    case Twitter = 'twitter';
    case LinkedIn = 'linkedin';
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case Threads = 'threads';
    case TikTok = 'tiktok';
}
