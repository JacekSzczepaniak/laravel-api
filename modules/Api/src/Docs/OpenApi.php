<?php

namespace Modules\Api\Docs;

use OpenApi\Attributes as OA;

#[OA\OpenApi]
#[OA\Info(version: '1.0.0', description: 'Public API', title: 'API')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    bearerFormat: 'Token',
    scheme: 'bearer'
)]
final class OpenApi {}
