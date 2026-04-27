<?php

declare(strict_types=1);

namespace Illuma\SocialCaster\Requests\LinkedIn;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class ListLinkedInOrganizationAcls extends Request
{
    /**
     * Rest.li projection so each ACL element includes {@code organization~.localizedName}.
     */
    public const PROJECTION = '(elements*(*,organization~(localizedName)))';

    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/organizationAcls';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return [
            'q' => 'roleAssignee',
            'state' => 'APPROVED',
            'count' => 100,
            'projection' => self::PROJECTION,
        ];
    }
}
