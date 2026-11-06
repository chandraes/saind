<?php

namespace App\Services;

class WhatsAppPhoneNumber
{
    public static function normalize(?string $value): ?string
    {
        $phone = preg_replace('/[\s().-]+/', '', trim($value ?? ''));
        if (! preg_match('/^(?:\+?62|0)8[1-9][0-9]{7,10}$/D', $phone)) {
            return null;
        }

        return ltrim($phone, '+');
    }
}
