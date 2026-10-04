<?php

namespace App\Http\Controllers;

use App\Models\AkiGantiCart;
use App\Models\AkiGantiInvoice;
use App\Models\AkiGantiInvoiceDetail;
use App\Models\AkiLog;
use App\Models\PosisiAki;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AkiGantiController extends Controller
{
    // Halaman Utama Form Input Penggantian Aki
    public function form_ganti_aki()
    {
        $vehicles = Vehicle::whereNot('status', 'nonaktif')->orderBy('nomor_lambung', 'asc')->get();
        $posisiAkis = PosisiAki::all();

        // Cek apakah ada keranjang aktif yang mengunci unit
        $activeCart = AkiGantiCart::first();
        $lockedVehicleId = $activeCart ? $activeCart->vehicle_id : null;
        $cartCount = AkiGantiCart::count();

        return view('billing.form-maintenance.aki.index', [
            'vehicles' => $vehicles,
            'posisiAkis' => $posisiAkis,
            'lockedVehicleId' => $lockedVehicleId,
            'cartCount' => $cartCount,
        ]);
    }

    // Get Informasi Kendaraan, Status Aki, & List Posisi Terpakai di Keranjang (AJAX)
    public function form_ganti_aki_get_vehicle_info(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        $vehicleId = $request->vehicle_id;

        $vehicle = Vehicle::leftJoin('upah_gendongs as ug', 'vehicles.id', 'ug.vehicle_id')
            ->leftJoin('vendors', 'vehicles.vendor_id', 'vendors.id')
            ->where('vehicles.id', $vehicleId)
            ->select('vehicles.*', 'ug.nama_driver as nama_driver', 'ug.nama_pengurus as pengurus', 'vendors.nama as nama_vendor')
            ->first();

        $akiLogs = AkiLog::where('vehicle_id', $vehicleId)
            ->orderBy('created_at', 'desc')
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
        $cartItems = AkiGantiCart::with('posisiAki')
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

    // 1. Tambah Aki ke Keranjang via AJAX
    public function form_ganti_aki_cart_add(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'posisi_aki_id' => 'required|exists:posisi_akis,id',
            'merk' => 'required|string|max:100',
            'kondisi' => 'required|numeric|min:1|max:100',
        ]);

        // Proteksi 1: Kunci kendaraan (Cek apakah ada keranjang unit lain yang menggantung)
        $existingCart = AkiGantiCart::first();
        if ($existingCart && $existingCart->vehicle_id != $validated['vehicle_id']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selesaikan atau kosongkan keranjang unit sebelumnya terlebih dahulu!',
            ], 422);
        }

        // Proteksi 2: Mencegah input posisi aki yang sama lebih dari sekali
        $existsPosisi = AkiGantiCart::where('vehicle_id', $validated['vehicle_id'])
            ->where('posisi_aki_id', $validated['posisi_aki_id'])
            ->exists();
        if ($existsPosisi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Posisi aki ini sudah ada di dalam keranjang!',
            ], 422);
        }

        try {
            AkiGantiCart::create([
                'vehicle_id' => $validated['vehicle_id'],
                'posisi_aki_id' => $validated['posisi_aki_id'],
                'merk' => strtoupper($validated['merk']),
                'no_seri' => '-',
                'kondisi' => $validated['kondisi'] ?? 100,
            ]);

            $totalCart = AkiGantiCart::where('vehicle_id', $validated['vehicle_id'])->count();

            return response()->json([
                'status' => 'success',
                'message' => 'Aki berhasil ditambahkan ke keranjang.',
                'cart_count' => $totalCart,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    // 2. Hapus Item dari Keranjang via AJAX
    public function form_ganti_aki_cart_delete($id)
    {
        $cart = AkiGantiCart::find($id);
        if ($cart) {
            $vehicleId = $cart->vehicle_id;
            $cart->delete();
            $totalCart = AkiGantiCart::where('vehicle_id', $vehicleId)->count();
        } else {
            $totalCart = 0;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Item keranjang berhasil dihapus.',
            'cart_count' => $totalCart,
        ]);
    }

    // 3. Kosongkan Keranjang via AJAX
    public function form_ganti_aki_cart_clear($vehicle_id)
    {
        AkiGantiCart::where('vehicle_id', $vehicle_id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Keranjang berhasil dikosongkan.']);
    }

    // 4. Halaman Terpisah: Konfirmasi Invoice & Review Keranjang Aki
    public function form_ganti_aki_confirm()
    {
        $cartItems = AkiGantiCart::with(['vehicle', 'posisiAki'])->get();

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
        ]);
    }

    // 5. Checkout Submit Form Keranjang Aki
    public function form_ganti_aki_checkout(Request $request)
    {
        if ($request->filled('total_nominal')) {
            $cleanedNominal = preg_replace('/[^0-9]/', '', $request->total_nominal);
            $request->merge(['total_nominal' => $cleanedNominal]);
        }

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'pembayaran' => ['required', Rule::in(array_keys(AkiGantiInvoice::getMetodePembayaranOptions()))],
            'total_nominal' => 'required_if:pembayaran,'.AkiGantiInvoice::PEMBAYARAN_KAS_BESAR.'|nullable|numeric|min:1',
            'nama_bank' => 'required_if:pembayaran,'.AkiGantiInvoice::PEMBAYARAN_KAS_BESAR.'|nullable|string|max:50',
            'nomor_rekening' => 'required_if:pembayaran,'.AkiGantiInvoice::PEMBAYARAN_KAS_BESAR.'|nullable|string|max:50',
            'nama_rekening' => 'required_if:pembayaran,'.AkiGantiInvoice::PEMBAYARAN_KAS_BESAR.'|nullable|string|max:100',
        ]);

        $vehicleId = $validated['vehicle_id'];
        $vehicleInfo = Vehicle::findOrFail($vehicleId);
        $cartItems = AkiGantiCart::where('vehicle_id', $vehicleId)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('billing.form-maintenance.aki')->with('error', 'Keranjang masih kosong!');
        }

        try {
            DB::beginTransaction();

            $totalNominal = $validated['pembayaran'] === AkiGantiInvoice::PEMBAYARAN_KAS_BESAR ? $validated['total_nominal'] : 0;
            $noInvoice = 'INV-AKI-'.date('YmdHis').'-'.$vehicleInfo->nomor_lambung;

            // 1. Simpan Header Invoice (Status: Pending)
            $invoice = AkiGantiInvoice::create([
                'no_invoice' => $noInvoice,
                'vehicle_id' => $vehicleId,
                'pembayaran' => $validated['pembayaran'],
                'total_nominal' => $totalNominal,
                'tanggal' => date('Y-m-d'),
                'status' => AkiGantiInvoice::STATUS_PENDING,
                'nama_bank' => $request->nama_bank,
                'nomor_rekening' => $request->nomor_rekening,
                'nama_rekening' => $request->nama_rekening,
            ]);

            // 2. Pindahkan data Keranjang ke Detail Invoice
            foreach ($cartItems as $item) {
                AkiGantiInvoiceDetail::create([
                    'aki_ganti_invoice_id' => $invoice->id,
                    'posisi_aki_id' => $item->posisi_aki_id,
                    'merk' => $item->merk,
                    'no_seri' => $item->no_seri,
                    'kondisi' => $item->kondisi,
                ]);
            }

            // 3. Bersihkan Keranjang
            AkiGantiCart::where('vehicle_id', $vehicleId)->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal memproses checkout: '.$th->getMessage());
        }

        return redirect()->route('billing.form-maintenance.aki')
            ->with('success', 'Penggantian aki berhasil dikirim ke Admin untuk Otorisasi.');
    }
}
