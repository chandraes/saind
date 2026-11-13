<?php

return [
    'product_name' => 'DSGroup Hauling',
    'api_version' => 'v1',
    'workspace_id' => env('MOBILE_WORKSPACE_ID'),
    'workspace_name' => env('MOBILE_WORKSPACE_NAME'),
    'company_name' => env('MOBILE_COMPANY_NAME'),
    // Manifest bertanda tangan DS Groups (mobile:workspace-manifest); app menolak origin tanpa manifest sah.
    'workspace_manifest' => env('MOBILE_WORKSPACE_MANIFEST'),
    // Repo trust (GitHub Pages) yang memuat daftar pencabutan + timestamp; disalin oleh mobile:trust-sync.
    'trust_url' => env('MOBILE_TRUST_URL', 'https://trust.ds-groups.com/hauling/trust.json'),
    'token_expiration_minutes' => (int) env('MOBILE_TOKEN_EXPIRATION_MINUTES', 43200),
    'load_note_rules' => [
        'max_do_count' => (int) env('MOBILE_NOTE_MAX_DO_COUNT', 2),
        'allowed_vehicle_statuses' => ['aktif'],
        'blocking_note_statuses' => ['draft'],
        'enforce_mobile_backdate' => (bool) env('MOBILE_NOTE_ENFORCE_BACKDATE', false),
        'backdate_days' => (int) env('MOBILE_NOTE_BACKDATE_DAYS', 2),
        'customer_min_similarity' => (float) env('MOBILE_NOTE_CUSTOMER_MIN_SIMILARITY', 0.9),
        'customer_min_margin' => (float) env('MOBILE_NOTE_CUSTOMER_MIN_MARGIN', 0.1),
        'unit_pattern' => '/^(?:SAI[ -]*)?([0-9]{1,10})$/i',
    ],
    'load_note_roles' => ['vendor', 'vendor-induk', 'vendor-operational'],
    'database_roles' => ['admin', 'su'],
    'roles' => ['admin', 'su', 'user', 'asisten-user', 'vendor', 'vendor-induk', 'vendor-operational', 'operasional', 'driver'],
];
