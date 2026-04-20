<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Contracts;

use Illuma\SocialCaster\Enums\SocialPlatform;

interface SocialCredentials
{
    public function getSocialPlatform(): SocialPlatform;

    public function getSocialAccessToken(): ?string;

    public function getSocialPublishingAccessToken(): ?string;

    public function getSocialProviderUserId(): ?string;

    /**
     * @return array<string, mixed>
     */
    public function getSocialMetadata(): array;
}
