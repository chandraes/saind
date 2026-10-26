<?php

return [
    'product_name' => 'DSGroup Hauling',
    'api_version' => 'v1',
    'workspace_id' => env('MOBILE_WORKSPACE_ID'),
    'workspace_name' => env('MOBILE_WORKSPACE_NAME'),
    'company_name' => env('MOBILE_COMPANY_NAME'),
    'token_expiration_minutes' => (int) env('MOBILE_TOKEN_EXPIRATION_MINUTES', 43200),
    'roles' => ['admin', 'su', 'user', 'asisten-user', 'vendor-operational'],
];
