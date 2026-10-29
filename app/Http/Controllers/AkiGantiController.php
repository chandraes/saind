<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutAkiGantiRequest;
use App\Models\AkiGantiCart;
use App\Models\AkiGantiInvoice;
use App\Models\AkiLog;
use App\Models\PosisiAki;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\MaintenanceUjDitahanService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AkiGantiController extends Controller
{
    private function cart(Request $request): Builder
    {
        return AkiGantiCart::where('user_id', $request->user()->id);
    }

    private function lockCartOwner(Request $request): void
    {
        User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
    }

    // Halaman Utama Form Input Penggantian Aki
    public function form_ganti_aki(Request $request): View
    {
        $vehicles = Vehicle::whereNot('status', 'nonaktif')->orderBy('nomor_lambung', 'asc')->get();
        $posisiAkis = PosisiAki::all();

        // Cek apakah ada keranjang aktif yang mengunci unit
        $activeCart = $this->cart($request)->first();
        $lockedVehicleId = $activeCart ? $activeCart->vehicle_id : null;
        $cartCount = $this->cart($request)->count();

        return view('billing.form-maintenance.aki.index', [
            'vehicles' => $vehicles,
            'posisiAkis' => $posisiAkis,
            'lockedVehicleId' => $lockedVehicleId,
            'cartCount' => $cartCount,
        ]);
    }

    // Get Informasi Kendaraan, Status Aki, & List Posisi Terpakai di Keranjang (AJAX)
    public function form_ganti_aki_get_vehicle_info(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => ['required', 'integer', Rule::exists('vehicles', 'id')->where(fn ($query) => $query->where('status', '!=', 'nonaktif'))],
        ]);

        $vehicleId = $request->vehicle_id;

        $vehicle = Vehicle::leftJoin('upah_gendongs as ug', 'vehicles.id', 'ug.vehicle_id')
            ->leftJoin('vendors', 'vehicles.vendor_id', 'vendors.id')
            ->where('vehicles.id', $vehicleId)
            ->select('vehicles.*', 'ug.nama_driver as nama_driver', 'ug.nama_pengurus as pengurus', 'vendors.nama as nama_vendor')
            ->first();

        $akiLogs = AkiLog::where('vehicle_id', $vehicleId)
            ->orderBy('created_at', 'desc')->orderByDesc('id')
            ->get()
            ->unique('posisi_aki_id')
            ->keyBy('posisi_aki_id');

        $today = today();

        $statusAki = PosisiAki::all()->map(function ($posisi) use ($akiLogs, $today) {
            $log = $akiLogs->get($posisi->id);
            $tanggalGanti = $log?->created_at?->copy()->startOfDay();

            return [
                'posisi' => $posisi->nama,
                'merk' => $log ? $log->merk : '-',
                'no_seri' => $log ? $log->no_seri : '-',
                'kondisi' => $log ? $log->kondisi.'%' : '-',
                'tanggal_ganti' => $tanggalGanti?->format('d-m-Y') ?? '-',
                'jumlah_hari' => $tanggalGanti ? max(0, (int) $tanggalGanti->diffInDays($today, false)) : null,
            ];
        });

        // Load item keranjang & ID posisi aki yang sudah digunakan
        $cartItems = $this->cart($request)->with('posisiAki')
            ->where('vehicle_id', $vehicleId)
            ->orderBy('created_at', 'asc')
            ->get();

        $usedPosisiIds = $cartItems->pluck('posisi_aki_id')->toArray();

        return response()->json([
            'vehicle' => [
                'nomor_lambung' => $vehicle->nomor_lambung,
                'vendor' => $vehicle->nama_vendor ?? '-',
                'pengurus' => $vehicle->pengurus ?? '-',
                'driver' => $vehicle->nama_driver ?? '-',
            ],
            'batteries' => $statusAki,
            'used_posisi_ids' => $usedPosisiIds,
            'cart_count' => $cartItems->count(),
        ]);
    }

    public function form_ganti_aki_cart_add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer', Rule::exists('vehicles', 'id')->where(fn ($query) => $query->where('status', '!=', 'nonaktif'))],
            'posisi_aki_id' => ['required', 'integer', 'exists:posisi_akis,id'],
            'merk' => ['required', 'string', 'max:100'],
            'kondisi' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
        DB::transaction(function () use ($request, $validated): void {
            $this->lockCartOwner($request);
            $cart = $this->cart($request)->lockForUpdate()->get();
            if ($cart->isNotEmpty() && (int) $cart->first()->vehicle_id !== (int) $validated['vehicle_id']) {
                throw ValidationException::withMessages(['vehicle_id' => 'Selesaikan atau kosongkan keranjang unit sebelumnya.']);
            }
            if ($cart->contains('posisi_aki_id', $validated['posisi_aki_id'])) {
                throw ValidationException::withMessages(['posisi_aki_id' => 'Posisi aki ini sudah ada di keranjang.']);
            }
            AkiGantiCart::create($validated + ['user_id' => $request->user()->id, 'no_seri' => '-']);
        }, 3);

        return response()->json(['status' => 'success', 'message' => 'Aki berhasil ditambahkan ke keranjang.', 'cart_count' => $this->cart($request)->count()]);
    }

    public function form_ganti_aki_cart_delete(Request $request, int $id): JsonResponse
    {
        DB::transaction(function () use ($request, $id): void {
            $this->lockCartOwner($request);
            $this->cart($request)->lockForUpdate()->findOrFail($id)->delete();
        }, 3);

        return response()->json(['status' => 'success', 'message' => 'Item keranjang berhasil dihapus.', 'cart_count' => $this->cart($request)->count()]);
    }

    public function form_ganti_aki_cart_clear(Request $request, int $vehicle_id): JsonResponse
    {
        DB::transaction(function () use ($request, $vehicle_id): void {
            $this->lockCartOwner($request);
            $items = $this->cart($request)->where('vehicle_id', $vehicle_id)->lockForUpdate()->get();
            foreach ($items as $item) {
                $item->delete();
            }
        }, 3);

        return response()->json(['status' => 'success', 'message' => 'Keranjang berhasil dikosongkan.']);
    }

    // 4. Halaman Terpisah: Konfirmasi Invoice & Review Keranjang Aki
    public function form_ganti_aki_confirm(Request $request): View|RedirectResponse
    {
        $cartItems = $this->cart($request)->with(['vehicle', 'posisiAki'])->orderBy('id')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('billing.form-maintenance.aki')
                ->with('error', 'Keranjang masih kosong. Silahkan pilih aki terlebih dahulu.');
        }

        $vehicle = $cartItems->first()->vehicle;
        $vehicleInfo = Vehicle::leftJoin('upah_gendongs as ug', 'vehicles.id', 'ug.vehicle_id')
            ->leftJoin('vendors', 'vehicles.vendor_id', 'vendors.id')
            ->where('vehicles.id', $vehicle->id)
            ->select('vehicles.*', 'ug.nama_driver as nama_driver', 'ug.nama_pengurus as pengurus', 'vendors.nama as nama_vendor')
            ->first();

        return view('billing.form-maintenance.aki.cart', [
            'vehicle' => $vehicleInfo,
            'cartItems' => $cartItems,
            'ujDitahanBalance' => app(MaintenanceUjDitahanService::class)->balance($vehicle->id),
        ]);
    }

    public function form_ganti_aki_checkout(CheckoutAkiGantiRequest $request, MaintenanceUjDitahanService $ujDitahan): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($request, $data, $ujDitahan): void {
            $this->lockCartOwner($request);
            $items = $this->cart($request)->orderBy('id')->lockForUpdate()->get();
            $submittedIds = array_map('intval', array_keys($data['items']));
            $cartIds = $items->modelKeys();
            sort($submittedIds);
            sort($cartIds);
            if ($items->isEmpty() || $submittedIds !== $cartIds || $items->contains(fn ($item) => (int) $item->vehicle_id !== (int) $data['vehicle_id'])) {
                throw ValidationException::withMessages(['items' => 'Keranjang berubah atau tidak sesuai. Muat ulang dan periksa kembali.']);
            }
            $vehicle = Vehicle::whereKey($data['vehicle_id'])->lockForUpdate()->firstOrFail();
            if ($vehicle->status === 'nonaktif') {
                throw ValidationException::withMessages(['vehicle_id' => 'Kendaraan sudah nonaktif.']);
            }
            if ($data['pembayaran'] === AkiGantiInvoice::PEMBAYARAN_UJ_DITAHAN) {
                $ujDitahan->assertAvailable($vehicle->id, (int) $data['total_nominal']);
            }
            $invoice = AkiGantiInvoice::create([
                'no_invoice' => 'AKI-'.Str::ulid(), 'vehicle_id' => $vehicle->id,
                'user_id' => $request->user()->id, 'tanggal' => today()->toDateString(),
                'pembayaran' => $data['pembayaran'], 'total_nominal' => $data['total_nominal'] ?? 0,
                'nama_bank' => $data['nama_bank'] ?? null, 'nomor_rekening' => $data['nomor_rekening'] ?? null,
                'nama_rekening' => $data['nama_rekening'] ?? null, 'status' => AkiGantiInvoice::STATUS_PENDING,
            ]);
            $invoice->update(['no_invoice' => 'AKI-'.str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT)]);
            foreach ($items as $item) {
                $invoice->details()->create([
                    'posisi_aki_id' => $item->posisi_aki_id, 'merk' => $item->merk,
                    'no_seri' => $item->no_seri, 'kondisi' => $item->kondisi,
                    'created_at' => Carbon::parse($data['items'][$item->id]['tanggal_ganti'])->startOfDay(),
                ]);
                $item->delete();
            }
        }, 3);

        return to_route('billing.form-maintenance.aki')->with('success', 'Penggantian aki berhasil dikirim untuk otorisasi.');
    }
}
