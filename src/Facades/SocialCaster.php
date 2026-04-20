<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Illuma\SocialCaster\SocialCasterManager
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
