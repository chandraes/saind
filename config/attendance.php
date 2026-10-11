<?php

return [
    'enabled' => env('ATTENDANCE_ENABLED', false),
    'url' => env('ATTENDANCE_URL'),
    'token' => env('ATTENDANCE_TOKEN'),
    'ca_bundle' => env('ATTENDANCE_CA_BUNDLE'),
    'send_immediately' => env('ATTENDANCE_SEND_IMMEDIATELY', true),
];
