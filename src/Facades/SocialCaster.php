<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Facades;

use Illuma\SocialCaster\SocialCasterManager;
use Illuminate\Support\Facades\Facade;

/**
 * @see SocialCasterManager
 *
 * @method static \Illuma\SocialCaster\DTOs\PublishResult publish(\Illuma\SocialCaster\Contracts\PublishableContent $content, \Illuma\SocialCaster\Contracts\SocialCredentials $credentials)
 * @method static array validate(\Illuma\SocialCaster\Contracts\PublishableContent $content, ?\Illuma\SocialCaster\Contracts\SocialCredentials $credentials = null)
 * @method static void registerValidationCallback(\Closure $callback)
 */
class SocialCaster extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'social-caster';
    }
}
