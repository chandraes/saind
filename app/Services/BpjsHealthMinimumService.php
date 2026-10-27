<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BpjsHealthMinimumService
{
    private array $years = [];

    private ?bool $tableExists = null;

    public function setting(int $year, bool $lock = false): ?object
    {
        $this->tableExists ??= Schema::hasTable('bpjs_health_minimums');
        if (! $this->tableExists) {
            return null;
        }
        if ($lock || ! array_key_exists($year, $this->years)) {
            $query = DB::table('bpjs_health_minimums')->where('year', $year);
            if ($lock) {
                $query->lockForUpdate();
            }
            $this->years[$year] = $query->first();
        }

        return $this->years[$year];
    }

    public function requireConfigured(int $year, bool $lock = false): object
    {
        $setting = $this->setting($year, $lock);
        if (! $setting || (int) $setting->minimum_wage < 1 || (int) $setting->minimum_wage > 12000000) {
            throw ValidationException::withMessages(['bpjs_health' => 'Dasar minimum BPJS Kesehatan tahun '.$year.' belum diatur. Lengkapi melalui menu Database sebelum memproses gaji.']);
        }

        return $setting;
    }

    public function base(int $fixedIncome, int $year): int
    {
        return min(12000000, max($fixedIncome, (int) ($this->setting($year)?->minimum_wage ?? 0)));
    }
}
