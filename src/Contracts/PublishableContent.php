<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Contracts;

use Illuma\SocialCaster\Enums\SocialPlatform;

interface PublishableContent
{
    public function getSocialPlatform(): SocialPlatform;

    public function getPublishableBody(): ?string;

    public function getPublishableTitle(): ?string;

    public function getPublishableImagePath(): ?string;

    public function getPublishableVideoUrl(): ?string;

    /**
     * @return array<string, mixed>
     */
    public function getPublishableMetadata(): array;
}
