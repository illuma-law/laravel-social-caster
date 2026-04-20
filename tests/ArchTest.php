<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Tests;

test('architecture')
    ->expect('Illuma\SocialCaster')
    ->toUseStrictTypes()
    ->not->toUse(['die', 'dd', 'dump', 'var_dump']);

test('facades')
    ->expect('Illuma\SocialCaster\Facades')
    ->toExtend('Illuminate\Support\Facades\Facade');

test('contracts')
    ->expect('Illuma\SocialCaster\Contracts')
    ->toBeInterfaces();

test('enums')
    ->expect('Illuma\SocialCaster\Enums')
    ->toBeEnums();
