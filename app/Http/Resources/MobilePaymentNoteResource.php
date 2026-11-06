<?php

namespace App\Http\Resources;

use App\Services\VendorOperationalBilling;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobilePaymentNoteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $kind = $this->mobile_kind;
        $journey = $kind === 'utama' ? $this->resource : $this->transaksi;
        $cash = $journey->kas_uang_jalan;
        $customer = $kind === 'utama' ? $cash?->customer : ($this->customer ?? $cash?->customer);
        $weightSource = match ((int) $customer?->tagihan_dari) {
            1 => 'loaded',
            2 => 'unloaded',
            default => null,
        };

        return ['id' => (int) $this->id, 'kind' => $kind, 'category_name' => VendorOperationalBilling::CATEGORIES[$kind],
            'vehicle_number' => $cash?->vehicle?->nomor_lambung, 'ticket_number' => $journey->nota_muat,
            'unloading_ticket_number' => $journey->nota_bongkar,
            'route_name' => ($kind === 'utama' ? $cash?->rute?->nama : $this->rute?->nama) ?? $cash?->rute?->nama,
            'loaded_on' => $journey->tanggal_muat, 'unloaded_on' => $journey->tanggal_bongkar,
            'net_weight' => $journey->tonase, 'unloading_net_weight' => $journey->timbangan_bongkar,
            'billing_weight_source' => $weightSource,
            'billing_weight' => match ($weightSource) {
                'loaded' => $journey->tonase,
                'unloaded' => $journey->timbangan_bongkar,
                default => null,
            },
            'money_out_number' => $cash ? 'UJ'.sprintf('%02d', $cash->nomor_uang_jalan) : null,
            'money_out_amount' => $kind === 'utama' && $cash?->nominal_transaksi !== null ? (int) $cash->nominal_transaksi : null,
            'fields' => $this->whenHas('mobile_fields', fn () => $this->mobile_fields)];
    }
}
