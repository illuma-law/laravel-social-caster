<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Tests;

use Illuma\SocialCaster\SocialCasterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Saloon\Laravel\SaloonServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            SaloonServiceProvider::class,
            SocialCasterServiceProvider::class,
        ];
    }
}
