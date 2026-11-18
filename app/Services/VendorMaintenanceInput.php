<?php

namespace App\Services;

use App\Models\AkiLog;
use App\Models\BanLog;
use App\Models\FilterOliLog;
use App\Models\Vehicle;
use App\Support\MaintenanceHistoryGuard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Input langsung riwayat ban, aki, dan filter & oli oleh akun vendor (tanpa Form
 * Maintenance/otorisasi). Satu sumber aturan untuk aplikasi mobile dan web: isian,
 * validasi, kepemilikan nolam, dan konfirmasi password akun sendiri. Ubah tanggal/hapus
 * riwayat juga dipakai Admin & SU (semua nolam, password konfirmasi).
 */
class VendorMaintenanceInput
{
    public function __construct(private MaintenanceLogWriter $writer) {}

    /** Nolam aktif milik vendor akun yang boleh dicatat; vendor-induk & vendor-operational ditolak. */
    public function vehicle(Request $request, int $vehicle): Vehicle
    {
        $user = $request->user();
        abort_unless($user->canRecordMaintenanceDirectly(), 403);

        return Vehicle::query()->where('vendor_id', $user->vendor_id)->where('status', '!=', 'nonaktif')->findOrFail($vehicle);
    }

    public function storeTire(Request $request, Vehicle $unit): BanLog
    {
        $data = $request->validate([
            'position_id' => ['required', 'integer', Rule::exists('posisi_bans', 'id')],
            'source' => ['required', Rule::in(['baru', 'serep'])],
            'brand' => ['required_if:source,baru', 'nullable', 'string', 'max:100'],
            'serial_number' => ['required_if:source,baru', 'nullable', 'string', 'max:100'],
            'condition' => ['required_if:source,baru', 'nullable', 'integer', 'min:1', 'max:100'],
            ...$this->dateRule(),
        ], $this->messages());
        if ($data['source'] === 'serep' && (int) $data['position_id'] === MaintenanceLogWriter::SPARE_TIRE_POSITION) {
            throw ValidationException::withMessages(['position_id' => 'Posisi serep tidak bisa diisi dari ban serep.']);
        }

        return DB::transaction(function () use ($unit, $data): BanLog {
            Vehicle::whereKey($unit->id)->lockForUpdate()->firstOrFail();
            $baru = $data['source'] === 'baru';

            return $this->writer->tire($unit->id, (int) $data['position_id'], $data['source'],
                $baru ? strtoupper(trim($data['brand'])) : null, $baru ? strtoupper(trim($data['serial_number'])) : null,
                $baru ? (int) $data['condition'] : null, $this->date($data));
        }, 3);
    }

    public function storeBattery(Request $request, Vehicle $unit): AkiLog
    {
        $data = $request->validate([
            'position_id' => ['required', 'integer', Rule::exists('posisi_akis', 'id')],
            'brand' => ['required', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'condition' => ['required', 'integer', 'min:1', 'max:100'],
            ...$this->dateRule(),
        ], $this->messages());
        $serial = trim((string) ($data['serial_number'] ?? ''));

        return $this->writer->battery($unit->id, (int) $data['position_id'], strtoupper(trim($data['brand'])),
            $serial === '' ? null : strtoupper($serial), (int) $data['condition'], $this->date($data));
    }

    public function storeFilterOil(Request $request, Vehicle $unit): FilterOliLog
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('kategori_filter_oli_mesins', 'id')],
            'brand' => ['required', 'string', 'max:100'],
            'condition' => ['required', 'integer', 'min:1', 'max:100'],
            ...$this->dateRule(),
        ], $this->messages());

        return DB::transaction(function () use ($unit, $data): FilterOliLog {
            Vehicle::whereKey($unit->id)->lockForUpdate()->firstOrFail();
            $log = $this->writer->filterOil($unit->id, (int) $data['category_id'], strtoupper(trim($data['brand'])),
                (int) $data['condition'], $this->date($data));
            $this->writer->refreshFilterOil($unit->id);

            return $log;
        }, 3);
    }

    /**
     * Riwayat milik nolam vendor akun.
     *
     * @template T of BanLog|AkiLog|FilterOliLog
     *
     * @param  class-string<T>  $model
     * @return T
     */
    public function log(string $model, Request $request, int $vehicle, int $id): BanLog|AkiLog|FilterOliLog
    {
        // Akun vendor hanya nolam vendornya; Admin & SU semua nolam.
        $unit = Vehicle::query()
            ->when($request->user()->isVendorAccount(), fn ($query) => $query->where('vendor_id', $request->user()->vendor_id))
            ->findOrFail($vehicle);
        $log = $model::query()->where('vehicle_id', $unit->id)->findOrFail($id);
        MaintenanceHistoryGuard::authorize($request, $log);

        return $log;
    }

    /** Ubah tanggal ganti setelah konfirmasi password; filter & oli menghitung ulang ritase. */
    public function reschedule(Request $request, BanLog|AkiLog|FilterOliLog $log): BanLog|AkiLog|FilterOliLog
    {
        $data = $request->validate($this->dateRule(), $this->messages());
        $this->confirmPassword($request);
        if ($log instanceof FilterOliLog) {
            $this->writer->rescheduleFilterOil($log, $this->date($data));
        } else {
            $log->update(['created_at' => $this->date($data)]);
        }

        return $log->fresh();
    }

    /** Hapus riwayat setelah konfirmasi password; filter & oli menghitung ulang ritase. */
    public function destroy(Request $request, BanLog|AkiLog|FilterOliLog $log): void
    {
        $this->confirmPassword($request);
        $log instanceof FilterOliLog ? $this->writer->removeFilterOil($log) : $log->delete();
    }

    /** @return array<string, array<int, string>> */
    private function dateRule(): array
    {
        return ['replaced_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today']];
    }

    /** @param array<string, mixed> $data */
    private function date(array $data): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $data['replaced_on'])->startOfDay();
    }

    /** @return array<string, string> */
    private function messages(): array
    {
        return [
            'position_id.required' => 'Pilih posisi.',
            'category_id.required' => 'Pilih kategori.',
            'brand.required' => 'Merk wajib diisi.',
            'brand.required_if' => 'Merk wajib diisi untuk ban baru.',
            'serial_number.required_if' => 'Nomor seri wajib diisi untuk ban baru.',
            'condition.required' => 'Kondisi wajib diisi.',
            'condition.required_if' => 'Kondisi wajib diisi untuk ban baru.',
            'condition.min' => 'Kondisi antara 1 sampai 100.',
            'condition.max' => 'Kondisi antara 1 sampai 100.',
            'replaced_on.required' => 'Tanggal ganti wajib diisi.',
            'replaced_on.before_or_equal' => 'Tanggal ganti tidak boleh melebihi hari ini.',
        ];
    }

    private function confirmPassword(Request $request): void
    {
        $request->validate(['password' => ['required', 'string', 'max:1024']], ['password.required' => $request->user()->isVendorAccount()
            ? 'Masukkan password akun Anda.' : 'Masukkan password konfirmasi.']);
        if (! MaintenanceHistoryGuard::passwordMatches($request)) {
            throw ValidationException::withMessages(['password' => 'Password salah.']);
        }
    }
}
