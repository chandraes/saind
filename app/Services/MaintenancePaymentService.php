<?php

namespace App\Services;

use InvalidArgumentException;

class MaintenancePaymentService
{
    /** @return array<string, string> */
    public static function optionsFor(?string $role, string $wallet): array
    {
        $options = match ($wallet) {
            'uj_ditahan_ban' => ['uj_ditahan_ban' => 'UJ Ditahan Ban'],
            'uj_ditahan' => ['uj_ditahan' => 'UJ Ditahan Sparepart'],
            default => throw new InvalidArgumentException('Jenis UJ Ditahan maintenance tidak valid.'),
        };

        if (in_array($role, config('maintenance.full_payment_roles', ['admin', 'su']), true)) {
            $options += ['kas_besar' => 'Kas Besar', 'dibayar_sendiri' => 'Dibayar Sendiri'];
        }

        return $options;
    }
}
