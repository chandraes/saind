<?php

namespace App\Services;

use App\Exceptions\LoadNoteRuleException;
use App\Models\Customer;
use App\Models\CustomerTagihan;
use App\Models\RuteNoteAlias;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Str;

class LoadNoteMatcher
{
    private function customerName(string $name): string
    {
        $name = preg_replace('/^\s*p\s*\.?\s*t\s*\.?\s*/i', '', $name);

        return preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii($name)));
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function inspect(User $user, array $data): array
    {
        app(LoadNoteDateRule::class)->assertMobileAllowed($data['stampout_date'], 'stampout_date');
        $header = $this->customerName($data['header_name']);
        $candidates = Customer::select(['id', 'nama', 'status'])->get()->map(function (Customer $customer) use ($header): array {
            $name = $this->customerName($customer->nama);
            $length = max(strlen($name), strlen($header));

            return ['customer' => $customer, 'score' => $length === 0 ? 0 : 1 - levenshtein($header, $name) / $length];
        })->sortByDesc('score')->values();
        $best = $candidates->first();
        $minimum = (float) config('mobile.load_note_rules.customer_min_similarity', 0.9);
        $gap = (float) config('mobile.load_note_rules.customer_min_margin', 0.1);
        if ($header === '' || ! $best || $best['score'] < $minimum ||
            ($candidates->count() > 1 && round($best['score'] - $candidates[1]['score'], 6) < $gap)) {
            throw new LoadNoteRuleException('header_name', 'Customer belum dapat ditentukan dengan pasti dari kepala nota. Foto ulang; jika tetap gagal, hubungi admin.', 'customer_uncertain', 'retake_photo');
        }
        $customer = $best['customer'];
        if ((int) $customer->status !== 1) {
            throw new LoadNoteRuleException('header_name', 'Customer pada nota tidak aktif. Hubungi admin.', 'customer_inactive', 'contact_admin');
        }
        if (! preg_match(config('mobile.load_note_rules.unit_pattern', '/^(?:SAI[ -]*)?([0-9]{1,10})$/i'), trim($data['unit_code']), $unit)) {
            throw new LoadNoteRuleException('unit_code', 'Nomor unit tidak terbaca dengan pasti. Foto ulang nota.', 'unit_unreadable', 'retake_photo');
        }
        $number = ltrim($unit[1], '0') ?: '0';
        $vehicles = Vehicle::where('vendor_id', $user->vendor_id)->whereIn('nomor_lambung', [$number, $unit[1]])->limit(2)->get();
        if ($vehicles->count() !== 1) {
            throw new LoadNoteRuleException('unit_code', 'Nomor lambung tidak ditemukan secara pasti pada vendor Anda. Periksa foto atau hubungi admin.', 'unit_not_found', 'contact_admin');
        }
        $vehicle = $vehicles->first();
        app(LoadNoteRules::class)->assertVehicle($vehicle);
        app(LoadNoteRules::class)->assertTicket((int) $user->vendor_id, $data['ticket_number']);
        $alias = RuteNoteAlias::with('rute')->where('normalized_name', RuteNoteAlias::normalize($data['route_reference']))->first();
        if (! $alias || ! CustomerTagihan::where('customer_id', $customer->id)->where('rute_id', $alias->rute_id)->exists()) {
            throw new LoadNoteRuleException('route_reference', 'Alias rute atau tarif customer–rute belum diatur. Hubungi admin; foto ulang tidak diperlukan.', 'route_reference_missing', 'contact_admin');
        }

        return ['customer_id' => $customer->id, 'customer_name' => $customer->nama,
            'customer_similarity' => round($best['score'] * 100, 2), 'vehicle_id' => $vehicle->id,
            'vehicle_number' => (string) $vehicle->nomor_lambung, 'rute_id' => $alias->rute_id,
            'rute_name' => $alias->rute->nama, 'ticket_number' => trim($data['ticket_number']),
            'loaded_on' => $data['stampout_date'], 'date_source' => 'stampout',
            'gross_weight' => $data['gross_weight'], 'tare_weight' => $data['tare_weight'],
            'net_weight' => number_format(((float) $data['gross_weight']) - ((float) $data['tare_weight']), 3, '.', ''),
            'confirmation_message' => 'Pastikan nota ini untuk nomor lambung '.$vehicle->nomor_lambung.'.'];
    }
}
