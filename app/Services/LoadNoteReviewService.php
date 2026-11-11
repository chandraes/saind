<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerTagihan;
use App\Models\LoadNoteDraft;
use App\Models\Rute;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\VendorUangJalan;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use LogicException;

class LoadNoteReviewService
{
    private function assertDraft(LoadNoteDraft $note, ?int $revision = null): void
    {
        if ($note->status !== 'draft' || $note->transaksi_id !== null) {
            throw ValidationException::withMessages(['note' => 'Nota sudah di-void atau diproses. Muat ulang daftar nota.']);
        }
        if ($revision !== null && $note->revision !== $revision) {
            throw ValidationException::withMessages(['revision' => 'Nota sudah diubah pengguna lain. Muat ulang dan periksa data terbaru.']);
        }
    }

    private function lock(LoadNoteDraft $note, ?int $targetVehicle = null): LoadNoteDraft
    {
        Vendor::whereKey($note->vendor_id)->lockForUpdate()->firstOrFail();
        $fresh = $note->fresh();
        Vehicle::whereIn('id', array_unique([$fresh->vehicle_id, $targetVehicle ?? $fresh->vehicle_id]))
            ->where('vendor_id', $fresh->vendor_id)->orderBy('id')->lockForUpdate()->get();

        return LoadNoteDraft::whereKey($note->id)->lockForUpdate()->firstOrFail();
    }

    private function assertReferences(LoadNoteDraft $note): void
    {
        if (! $note->customer_id || ! $note->rute_id || ! Customer::whereKey($note->customer_id)->where('status', 1)->exists()
            || ! CustomerTagihan::where('customer_id', $note->customer_id)->where('rute_id', $note->rute_id)->exists()) {
            throw ValidationException::withMessages(['rute_id' => 'Customer dan rute nota belum sesuai atau tarif belum diatur. Koreksi nota terlebih dahulu.']);
        }
    }

    private function record(User $user, LoadNoteDraft $note, string $action, string $reason, array $before): void
    {
        $note->events()->create(['actor_id' => $user->id, 'action' => $action, 'reason' => $reason,
            'before' => $before, 'after' => $this->snapshot($note)]);
    }

    private function snapshot(LoadNoteDraft $note): array
    {
        $snapshot = Arr::only($note->getAttributes(), ['vehicle_id', 'vehicle_number', 'customer_id', 'rute_id', 'ticket_number', 'mine_name',
            'loaded_on', 'gross_kg', 'tare_kg', 'net_kg', 'notes', 'status', 'revision', 'transaksi_id', 'transfer_override']);
        $snapshot['loaded_on'] = $note->loaded_on->format('Y-m-d');
        $snapshot['rute_name'] = $note->rute_id ? Rute::whereKey($note->rute_id)->value('nama') : null;

        return $snapshot;
    }

    public function update(User $user, LoadNoteDraft $note, array $data): LoadNoteDraft
    {
        Gate::forUser($user)->authorize('review', $note);

        return DB::transaction(function () use ($user, $note, $data): LoadNoteDraft {
            $locked = $this->lock($note, (int) $data['vehicle_id']);
            $this->assertDraft($locked, (int) $data['revision']);
            $before = $this->snapshot($locked);
            $vehicle = Vehicle::whereKey($data['vehicle_id'])->where('vendor_id', $locked->vendor_id)->firstOrFail();
            if ((int) $vehicle->id !== (int) $locked->vehicle_id) {
                app(LoadNoteRules::class)->assertVehicle($vehicle, $locked->id);
            }
            app(LoadNoteRules::class)->assertTicket((int) $locked->vendor_id, $data['ticket_number'], $locked->id);
            app(LoadNoteDateRule::class)->assertMobileAllowed($data['loaded_on']);
            $gross = app(LoadNoteDraftService::class)->kilograms((string) $data['gross_weight']);
            $tare = app(LoadNoteDraftService::class)->kilograms((string) $data['tare_weight']);
            if ($locked->vehicle_id !== (int) $data['vehicle_id'] || $locked->rute_id !== (int) $data['rute_id'] || $locked->customer_id !== (int) $data['customer_id']) {
                $locked->transfer_override = null;
            }
            $locked->fill(['vehicle_id' => $vehicle->id, 'vehicle_number' => (string) $vehicle->nomor_lambung,
                'customer_id' => (int) $data['customer_id'], 'rute_id' => (int) $data['rute_id'],
                'mine_name' => Customer::findOrFail($data['customer_id'])->nama, 'ticket_number' => trim($data['ticket_number']),
                'loaded_on' => $data['loaded_on'], 'date_source' => 'stampout', 'gross_kg' => $gross, 'tare_kg' => $tare,
                'net_kg' => $gross - $tare, 'notes' => trim($data['notes'] ?? ''), 'revision' => $locked->revision + 1]);
            $this->assertReferences($locked);
            $locked->save();
            $this->record($user, $locked, 'updated', trim($data['reason']), $before);

            return $locked;
        });
    }

    public function updatePayment(User $user, LoadNoteDraft $note, array $data): LoadNoteDraft
    {
        Gate::forUser($user)->authorize('adjustPayment', $note);

        return DB::transaction(function () use ($user, $note, $data): LoadNoteDraft {
            $locked = $this->lock($note);
            $this->assertDraft($locked, (int) $data['revision']);
            $this->assertReferences($locked);
            $before = $this->snapshot($locked);
            $locked->update(['transfer_override' => $data['use_system_amount'] ? null : (int) $data['transfer_amount'],
                'revision' => $locked->revision + 1]);
            $this->record($user, $locked, 'payment_updated', trim($data['reason']), $before);

            return $locked;
        });
    }

    public function paymentFormData(LoadNoteDraft $note): array
    {
        return $note->transfer_override === null ? [] : ['nominal_transaksi' => $note->transfer_override];
    }

    public function decide(User $user, LoadNoteDraft $note, array $data): LoadNoteDraft
    {
        Gate::forUser($user)->authorize('review', $note);

        return DB::transaction(function () use ($user, $note, $data): LoadNoteDraft {
            $locked = $this->lock($note);
            $this->assertDraft($locked, (int) $data['revision']);
            $before = $this->snapshot($locked);
            $locked->update(['status' => 'void', 'revision' => $locked->revision + 1]);
            $this->record($user, $locked, 'void', trim($data['reason']), $before);

            return $locked;
        });
    }

    public function forForward(User $user, int $id): LoadNoteDraft
    {
        $note = LoadNoteDraft::findOrFail($id);
        Gate::forUser($user)->authorize('review', $note);
        $this->assertReady($note);

        return $note;
    }

    private function assertReady(LoadNoteDraft $note): void
    {
        $this->assertDraft($note);
        $this->assertReferences($note);
        $vehicle = Vehicle::whereKey($note->vehicle_id)->where('vendor_id', $note->vendor_id)->firstOrFail();
        app(LoadNoteRules::class)->assertVehicle($vehicle, $note->id);
        app(LoadNoteDateRule::class)->assertMobileAllowed($note->loaded_on->format('Y-m-d'));
        app(LoadNoteRules::class)->assertTicket((int) $note->vendor_id, $note->ticket_number, $note->id);
        if (! DB::table('customer_rute')->where('customer_id', $note->customer_id)->where('rute_id', $note->rute_id)->exists()
            || ! VendorUangJalan::where('vendor_id', $note->vendor_id)->where('rute_id', $note->rute_id)->exists()) {
            throw ValidationException::withMessages(['rute_id' => 'Relasi customer–rute atau kesepakatan uang jalan vendor–rute belum diatur.']);
        }
    }

    public function lockForProcessing(User $user, LoadNoteDraft $note, int $revision): LoadNoteDraft
    {
        if (DB::transactionLevel() < 1) {
            throw new LogicException('Penerusan nota wajib berada dalam transaksi database.');
        }
        Gate::forUser($user)->authorize('review', $note);
        $locked = $this->lock($note);
        $this->assertDraft($locked, $revision);
        $this->assertReady($locked);

        return $locked;
    }

    public function formData(LoadNoteDraft $note): array
    {
        return ['vehicle_id' => $note->vehicle_id, 'p_vendor' => $note->vendor_id, 'customer_id' => $note->customer_id, 'rute_id' => $note->rute_id,
            'nota_muat' => $note->ticket_number, 'tanggal_muat' => $note->loaded_on->format('Y-m-d'),
            'tonase' => number_format($note->net_kg / 1000, 3, '.', ''), 'gross_muat' => number_format($note->gross_kg / 1000, 3, '.', ''),
            'tarra_muat' => number_format($note->tare_kg / 1000, 3, '.', '')];
    }

    public function markProcessed(User $user, LoadNoteDraft $locked, Transaksi $transaction): void
    {
        if (DB::transactionLevel() < 1) {
            throw new LogicException('Penerusan nota wajib berada dalam transaksi database.');
        }
        Gate::forUser($user)->authorize('review', $locked);
        $this->assertDraft($locked);
        $before = $this->snapshot($locked);
        $locked->update(['status' => 'processed', 'transaksi_id' => $transaction->id, 'revision' => $locked->revision + 1]);
        $this->record($user, $locked, 'processed', 'Diteruskan menjadi transaksi uang jalan #'.$transaction->id, $before);
    }
}
