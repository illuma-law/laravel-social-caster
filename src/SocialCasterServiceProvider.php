<?php

declare(strict_types=1);

namespace Illuma\SocialCaster;

use Illuminate\Foundation\Application;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SocialCasterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-social-caster')
            ->hasConfigFile('social-caster');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('social-caster', function (Application $app): SocialCasterManager {
            return new SocialCasterManager;
        });
    }
}
