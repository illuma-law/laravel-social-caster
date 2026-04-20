<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\DTOs;

final readonly class PublishResult
{
    public function __construct(
        public ?string $externalId,
        public ?string $externalUrl,
        public mixed $rawResponse,
    ) {}
}
