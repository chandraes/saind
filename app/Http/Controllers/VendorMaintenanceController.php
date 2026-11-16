<?php

namespace App\Http\Controllers;

use App\Models\FilterOliLog;
use App\Services\VendorMaintenanceInput;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Halaman web statistik Ban Luar, Aki, dan Filter & Oli untuk akun vendor:
 * mencatat penggantian langsung ke riwayat, serta ubah tanggal/hapus histori
 * Filter & Oli. Aturannya sama dengan aplikasi mobile (VendorMaintenanceInput).
 */
class VendorMaintenanceController extends Controller
{
    public function __construct(private VendorMaintenanceInput $input) {}

    public function storeTire(Request $request, int $vehicle): RedirectResponse
    {
        $this->input->storeTire($request, $this->input->vehicle($request, $vehicle));

        return back()->with('success', 'Penggantian ban berhasil dicatat.');
    }

    public function storeBattery(Request $request, int $vehicle): RedirectResponse
    {
        $this->input->storeBattery($request, $this->input->vehicle($request, $vehicle));

        return back()->with('success', 'Penggantian aki berhasil dicatat.');
    }

    public function storeFilterOil(Request $request, int $vehicle): RedirectResponse
    {
        $this->input->storeFilterOil($request, $this->input->vehicle($request, $vehicle));

        return back()->with('success', 'Penggantian filter & oli berhasil dicatat. Ritase dihitung ulang.');
    }

    public function updateFilterOil(Request $request, int $vehicle, int $log): RedirectResponse
    {
        $this->input->reschedule($request, $this->input->log(FilterOliLog::class, $request, $vehicle, $log));

        return back()->with('success', 'Tanggal ganti berhasil diubah. Ritase dihitung ulang.');
    }

    public function destroyFilterOil(Request $request, int $vehicle, int $log): RedirectResponse
    {
        $this->input->destroy($request, $this->input->log(FilterOliLog::class, $request, $vehicle, $log));

        return back()->with('success', 'Riwayat berhasil dihapus. Ritase dihitung ulang.');
    }
}
