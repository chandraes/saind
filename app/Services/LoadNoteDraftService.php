<?php

namespace App\Services;

use App\Exceptions\LoadNoteRuleException;
use App\Models\LoadNoteDraft;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class LoadNoteDraftService
{
    public function photo(LoadNoteDraft $draft): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($draft->photo_path), 404);

        return Storage::disk('local')->response($draft->photo_path, 'nota-'.$draft->id.'.'.pathinfo($draft->photo_path, PATHINFO_EXTENSION),
            ['Content-Type' => $draft->photo_mime, 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'], 'inline');
    }

    public function kilograms(string $value): int
    {
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');

        return ((int) $whole * 1000) + (int) str_pad($fraction, 3, '0');
    }

    /** @param array<string, mixed> $data
     * @return array{0: LoadNoteDraft, 1: bool}
     */
    public function store(User $user, array $data, UploadedFile $photo): array
    {
        $scanned = isset($data['header_name']);
        $attributes = $scanned ? ['header_name' => trim($data['header_name']), 'unit_code' => trim($data['unit_code']),
            'route_reference' => trim($data['route_reference']), 'stampout_date' => $data['stampout_date'],
            'confirmed_vehicle_id' => (int) $data['confirmed_vehicle_id'], 'ticket_number' => trim($data['ticket_number']),
            'gross_kg' => $this->kilograms((string) $data['gross_weight']), 'tare_kg' => $this->kilograms((string) $data['tare_weight'])] : ['vehicle_id' => (int) $data['vehicle_id'], 'ticket_number' => trim($data['ticket_number']),
                'mine_name' => trim($data['mine_name']), 'loaded_on' => $data['loaded_on'],
                'driver_name' => trim($data['driver_name'] ?? ''), 'notes' => trim($data['notes'] ?? ''),
                'gross_kg' => $this->kilograms((string) $data['gross_weight']), 'tare_kg' => $this->kilograms((string) $data['tare_weight'])];
        $hash = hash('sha256', json_encode($attributes, JSON_THROW_ON_ERROR).hash_file('sha256', $photo->getRealPath()));
        $path = null;
        try {
            return DB::transaction(function () use ($user, $data, $photo, $attributes, $hash, $scanned, &$path): array {
                Vendor::whereKey($user->vendor_id)->lockForUpdate()->firstOrFail();
                $existing = LoadNoteDraft::where('vendor_id', $user->vendor_id)->where('request_id', $data['request_id'])->first();
                if ($existing) {
                    abort_unless(hash_equals($existing->payload_hash, $hash), 409, 'Permintaan ini sudah tersimpan dengan data berbeda. Periksa daftar nota sebelum mengirim ulang.');

                    return [$existing->load('creator:id,name'), false];
                }
                if ($scanned) {
                    $matched = app(LoadNoteMatcher::class)->inspect($user, $data);
                    if ((int) $data['confirmed_vehicle_id'] !== (int) $matched['vehicle_id']) {
                        throw new LoadNoteRuleException('unit_code', 'Hasil pencocokan kendaraan berubah. Periksa ringkasan dan konfirmasi nomor lambung kembali.', 'vehicle_confirmation_changed', 'confirm_vehicle');
                    }
                    $attributes = ['customer_id' => $matched['customer_id'], 'rute_id' => $matched['rute_id'],
                        'vehicle_id' => $matched['vehicle_id'], 'ticket_number' => $matched['ticket_number'],
                        'mine_name' => $matched['customer_name'], 'loaded_on' => $matched['loaded_on'],
                        'header_name' => trim($data['header_name']), 'unit_code' => trim($data['unit_code']),
                        'route_reference' => trim($data['route_reference']), 'date_source' => 'stampout',
                        'driver_name' => '', 'notes' => '',
                        'gross_kg' => $attributes['gross_kg'], 'tare_kg' => $attributes['tare_kg']];
                }
                $vehicle = Vehicle::whereKey($attributes['vehicle_id'])->where('vendor_id', $user->vendor_id)->lockForUpdate()->firstOrFail();
                app(LoadNoteRules::class)->assertVehicle($vehicle);
                app(LoadNoteRules::class)->assertTicket((int) $user->vendor_id, $attributes['ticket_number']);
                app(LoadNoteDateRule::class)->assertMobileAllowed($attributes['loaded_on']);
                $path = $photo->storeAs('mobile/load-notes/'.$user->vendor_id, Str::uuid().'.'.$photo->extension(), 'local');
                if (! is_string($path)) {
                    throw new RuntimeException('Foto nota belum dapat disimpan.');
                }
                $draft = LoadNoteDraft::create([...$attributes, 'request_id' => $data['request_id'],
                    'payload_hash' => $hash, 'vendor_id' => $user->vendor_id, 'created_by' => $user->id,
                    'vehicle_number' => (string) $vehicle->nomor_lambung, 'net_kg' => $attributes['gross_kg'] - $attributes['tare_kg'],
                    'photo_path' => $path, 'photo_mime' => $photo->getMimeType(), 'status' => 'draft']);

                return [$draft->load('creator:id,name'), true];
            });
        } catch (Throwable $exception) {
            if (is_string($path)) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
    }
}
