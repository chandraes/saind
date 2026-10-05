<?php

namespace App\Http\Controllers;

use App\Models\FilterOliGantiInvoice;
use App\Models\FilterOliGantiInvoiceDetail;
use App\Models\FilterOliLog;
use App\Models\KategoriFilterOliMesin;
use App\Models\Vehicle;
use App\Services\FilterOliRitaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class FilterOliReportController extends Controller
{
    public function recap(Request $request): View
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:approved,rejected'],
            'pembayaran' => ['nullable', 'in:kas_besar,dibayar_sendiri'],
        ]);
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->toDateString();
        if ($endDate < $startDate) {
            throw ValidationException::withMessages(['end_date' => 'Tanggal akhir harus setelah atau sama dengan tanggal awal.']);
        }
        $query = FilterOliGantiInvoice::query()
            ->whereIn('status', ['approved', 'rejected'])
            ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['pembayaran'] ?? null, fn ($query, $payment) => $query->where('pembayaran', $payment));
        $summary = [
            'total' => (clone $query)->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'cash' => (clone $query)->where('status', 'approved')->where('pembayaran', 'kas_besar')->sum('total_nominal'),
        ];
        $invoices = $query->with('vehicle.vendor')->withCount('details')->latest()->orderByDesc('id')->paginate(25)->withQueryString();

        return view('rekap.maintenance.filter-oli.index', compact('invoices', 'summary', 'startDate', 'endDate', 'filters'));
    }

    public function recapDetail(int $id, FilterOliGantiController $controller): View
    {
        FilterOliGantiInvoice::whereIn('status', ['approved', 'rejected'])->findOrFail($id);

        return $controller->show($id)->with('backUrl', route('rekap.maintenance.filter-oli'));
    }

    public function statistics(Request $request): View
    {
        $filters = $request->validate(['vehicle_id' => ['nullable', 'integer', Rule::exists('vehicles', 'id')->where(fn ($query) => $query->where('status', '!=', 'nonaktif')->where('pembatasan_filter_oli', true))]]);
        $vehicles = Vehicle::where('status', '!=', 'nonaktif')->where('pembatasan_filter_oli', true)->orderBy('nomor_lambung')->get();
        $vehicle = isset($filters['vehicle_id']) ? $vehicles->firstWhere('id', $filters['vehicle_id']) : null;
        $categories = KategoriFilterOliMesin::orderBy('id')->get();
        $logs = collect();
        if ($vehicle) {
            $logs = FilterOliLog::where('vehicle_id', $vehicle->id)
                ->where('created_at', '<=', now())->latest()->orderByDesc('id')->get()
                ->unique('kategori_filter_oli_mesin_id')->keyBy('kategori_filter_oli_mesin_id');
        }
        $dueCount = $logs->filter(fn (FilterOliLog $log): bool => (float) $log->ritase >= $log->limit_ritase)->count();

        return view('rekap.statistik.filter-oli.index', compact('vehicles', 'vehicle', 'categories', 'logs', 'dueCount'));
    }

    public function history(int $vehicle, int $category): View
    {
        $vehicle = Vehicle::findOrFail($vehicle);
        $category = KategoriFilterOliMesin::findOrFail($category);
        $logs = FilterOliLog::where('vehicle_id', $vehicle->id)->where('kategori_filter_oli_mesin_id', $category->id)
            ->latest()->orderByDesc('id')->paginate(25)->withQueryString();

        return view('rekap.statistik.filter-oli.history', compact('vehicle', 'category', 'logs'));
    }

    public function transactions(int $log): View
    {
        $log = FilterOliLog::with('kategori')->findOrFail($log);
        $transactions = DB::table('filter_oli_log_transaksis as audit')
            ->join('transaksis', 'audit.transaksi_id', '=', 'transaksis.id')
            ->join('kas_uang_jalans', 'transaksis.kas_uang_jalan_id', '=', 'kas_uang_jalans.id')
            ->join('rutes', 'kas_uang_jalans.rute_id', '=', 'rutes.id')
            ->where('audit.filter_oli_log_id', $log->id)
            ->select('transaksis.id', 'transaksis.created_at', 'kas_uang_jalans.nomor_uang_jalan', 'rutes.nama as rute', 'rutes.jarak', 'audit.nilai_ritase')
            ->orderBy('transaksis.created_at')->orderBy('transaksis.id')->get();

        return view('rekap.statistik.filter-oli.transactions', compact('log', 'transactions'));
    }

    public function updateHistory(Request $request, int $log, FilterOliRitaseService $ritase): RedirectResponse
    {
        $data = $request->validate(['tanggal_ganti' => ['required', 'date_format:Y-m-d', 'before_or_equal:today']], [
            'tanggal_ganti.required' => 'Tanggal ganti wajib diisi.',
            'tanggal_ganti.date_format' => 'Format tanggal ganti tidak valid.',
            'tanggal_ganti.before_or_equal' => 'Tanggal ganti tidak boleh setelah hari ini.',
        ]);
        $record = FilterOliLog::findOrFail($log);
        try {
            DB::transaction(function () use ($record, $data, $ritase): void {
                Vehicle::whereKey($record->vehicle_id)->lockForUpdate()->firstOrFail();
                $record = FilterOliLog::whereKey($record->id)->lockForUpdate()->firstOrFail();
                $date = Carbon::parse($data['tanggal_ganti'])->startOfDay();
                $record->update(['created_at' => $date]);
                FilterOliGantiInvoiceDetail::where('filter_oli_log_id', $record->id)->update(['created_at' => $date]);
                $ritase->refreshVehicle($record->vehicle_id);
            }, 3);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Tanggal ganti gagal diperbarui. Silakan coba kembali.');
        }

        return back()->with('success', 'Tanggal ganti diperbarui. Ritase dan catatan transaksi seluruh periode penggantian telah dihitung ulang.');
    }

    public function deleteHistory(int $log, FilterOliRitaseService $ritase): RedirectResponse
    {
        $record = FilterOliLog::findOrFail($log);
        try {
            DB::transaction(function () use ($record, $ritase): void {
                Vehicle::whereKey($record->vehicle_id)->lockForUpdate()->firstOrFail();
                $record = FilterOliLog::whereKey($record->id)->lockForUpdate()->firstOrFail();
                FilterOliGantiInvoiceDetail::where('filter_oli_log_id', $record->id)->update(['filter_oli_log_id' => null]);
                $record->delete();
                $ritase->refreshVehicle($record->vehicle_id);
            }, 3);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Histori gagal dihapus. Silakan coba kembali.');
        }

        return back()->with('success', 'Histori dihapus dan ritase dihitung ulang. Invoice dan catatan pembayaran tetap tersimpan.');
    }
}
