<?php


namespace Modules\Api\Docs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PersonContact',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'type', type: 'string', example: 'email'),
        new OA\Property(property: 'value', type: 'string', example: 'user@example.com'),
        new OA\Property(property: 'is_primary', type: 'boolean'),
    ]
)]
#[OA\Schema(
    schema: 'ContactCreateRequest',
    required: ['type', 'value'],
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'email'),
        new OA\Property(property: 'value', type: 'string', example: 'user@example.com'),
        new OA\Property(property: 'is_primary', type: 'boolean', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'ContactUpdateRequest',
    properties: [
        new OA\Property(property: 'value', type: 'string', nullable: true),
        new OA\Property(property: 'is_primary', type: 'boolean', nullable: true),
    ]
)]
final class ContactSchemas
{
}
