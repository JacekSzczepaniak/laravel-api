<?php

namespace Modules\Api\Docs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Person',
    required: ['id','first_name','last_name'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', format: 'int64', example: 1),
        new OA\Property(property: 'first_name', type: 'string', example: 'John'),
        new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'PersonCreateRequest',
    required: ['first_name','last_name'],
    properties: [
        new OA\Property(property: 'first_name', type: 'string', maxLength: 255, example: 'John'),
        new OA\Property(property: 'last_name', type: 'string', maxLength: 255, example: 'Doe'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'PersonUpdateRequest',
    properties: [
        new OA\Property(property: 'first_name', type: 'string', nullable: true),
        new OA\Property(property: 'last_name', type: 'string', nullable: true),
    ],
    type: 'object'
)]
final class PersonSchemas {}
