<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileUnloadingNoteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $journey = $this->kas_uang_jalan;
        $completed = (int) $this->status === 3;

        return ['transfer_destination' => $this->whenHas('mobile_transfer_destination', fn () => $this->mobile_transfer_destination), 'payment' => $this->whenHas('mobile_payment', fn () => $this->mobile_payment), 'id' => (int) $this->id, 'status' => $completed ? 'completed' : 'waiting',
            'vehicle_id' => (int) $journey->vehicle_id, 'vehicle_number' => $journey->vehicle?->nomor_lambung,
            'route_name' => $journey->rute?->nama, 'mine_name' => $journey->customer?->nama,
            'money_out_number' => 'UJ'.sprintf('%02d', $journey->nomor_uang_jalan), 'ticket_number' => (string) $this->nota_muat,
            'loaded_on' => $this->tanggal_muat, 'unloaded_on' => $completed ? $this->tanggal_bongkar : null,
            'unloading_ticket_number' => $completed ? $this->nota_bongkar : null,
            'gross_weight' => number_format((float) $this->gross_muat, 3, '.', ''),
            'tare_weight' => number_format((float) $this->tarra_muat, 3, '.', ''),
            'net_weight' => number_format((float) $this->tonase, 3, '.', ''),
            'unloading_gross_weight' => $completed ? $this->weight($this->gross_bongkar) : null,
            'unloading_tare_weight' => $completed ? $this->weight($this->tarra_bongkar) : null,
            'unloading_net_weight' => $completed ? $this->weight($this->timbangan_bongkar) : null];
    }

    private function weight(mixed $value): ?string
    {
        return $value === null || $value === '' ? null : number_format((float) $value, 3, '.', '');
    }
}
