<?php

declare(strict_types=1);

namespace Illuma\SocialCaster;

use Illuminate\Support\ServiceProvider;

class SocialCasterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/social-caster.php', 'social-caster');

        $this->app->singleton('social-caster', function ($app) {
            return new SocialCasterManager();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/social-caster.php' => config_path('social-caster.php'),
            ], 'social-caster-config');
        }
    }
}
