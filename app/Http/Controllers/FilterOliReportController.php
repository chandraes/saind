<?php

namespace App\Http\Controllers;

use App\Models\FilterOliGantiInvoice;
use App\Models\FilterOliLog;
use App\Models\KategoriFilterOliMesin;
use App\Models\Vehicle;
use App\Services\MaintenanceLogWriter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
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
            'pembayaran' => ['nullable', 'in:kas_besar,dibayar_sendiri,uj_ditahan'],
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

    public function statistics(Request $request): View|RedirectResponse
    {
        if ($request->user()->isVendorAccount() && ! $request->filled('vehicle_id')) {
            return redirect()->route('vendor-operational.statistics.vehicles', ['report' => 'filter-oli']);
        }
        $filters = $request->validate(['vehicle_id' => ['nullable', 'integer', Rule::exists('vehicles', 'id')->where(fn ($query) => Vehicle::whereFilterOilEligible($query, $request->user()))]]);
        $vehicles = Vehicle::whereFilterOilEligible(Vehicle::query(), $request->user())
            ->when($request->user()->isVendorAccount(), fn ($query) => $query->where('vendor_id', $request->user()->vendor_id)->whereKey($request->input('vehicle_id')))
            ->orderBy('nomor_lambung')->get();
        $vehicle = isset($filters['vehicle_id']) ? $vehicles->firstWhere('id', $filters['vehicle_id']) : null;
        $categories = KategoriFilterOliMesin::orderBy('id')->get();
        $logs = collect();
        if ($vehicle) {
            $logs = FilterOliLog::with('kategori')->where('vehicle_id', $vehicle->id)
                ->when($request->user()->isVendorAccount(), fn ($query) => $query->where('id', function ($latest) {
                    $latest->from('filter_oli_logs as latest')->select('latest.id')->whereColumn('latest.vehicle_id', 'filter_oli_logs.vehicle_id')->whereColumn('latest.kategori_filter_oli_mesin_id', 'filter_oli_logs.kategori_filter_oli_mesin_id')->where('latest.created_at', '<=', now())->orderByDesc('latest.created_at')->orderByDesc('latest.id')->limit(1);
                }))
                ->where('created_at', '<=', now())->latest()->orderByDesc('id')->get()
                ->unique('kategori_filter_oli_mesin_id')->keyBy('kategori_filter_oli_mesin_id');
        }
        $dueCount = $logs->filter(fn (FilterOliLog $log): bool => (float) $log->ritase >= $log->kategori->limit_ritase)->count();

        return view('rekap.statistik.filter-oli.index', compact('vehicles', 'vehicle', 'categories', 'logs', 'dueCount'));
    }

    public function history(Request $request, int $vehicle, int $category): View|JsonResponse
    {
        $vehicle = Vehicle::findOrFail($vehicle);
        $category = KategoriFilterOliMesin::findOrFail($category);
        if ($request->user()->isVendorAccount() && ! $request->ajax()) {
            return view('rekap.statistik.filter-oli.vendor-history', compact('vehicle', 'category'));
        }
        $logs = FilterOliLog::where('vehicle_id', $vehicle->id)->where('kategori_filter_oli_mesin_id', $category->id)
            ->latest()->orderByDesc('id');

        if ($request->user()->isVendorAccount()) {
            $total = (clone $logs)->count();
            $logs = $logs->offset(max(0, $request->integer('start')))->limit(25)->get();

            return response()->json(['data' => $logs->map(fn ($log) => ['id' => $log->id, 'tanggal' => $log->created_at->format('d-m-Y'), 'merk' => $log->merk, 'kondisi' => $log->kondisi, 'ritase' => $log->ritase]), 'recordsFiltered' => $total]);
        }
        $logs = $logs->paginate(25)->withQueryString();

        return view('rekap.statistik.filter-oli.history', compact('vehicle', 'category', 'logs'));
    }

    public function transactions(Request $request, int $log): View|JsonResponse
    {
        $log = FilterOliLog::with('kategori')->findOrFail($log);
        $transactions = DB::table('filter_oli_log_transaksis as audit')
            ->join('transaksis', 'audit.transaksi_id', '=', 'transaksis.id')
            ->join('kas_uang_jalans', 'transaksis.kas_uang_jalan_id', '=', 'kas_uang_jalans.id')
            ->join('rutes', 'kas_uang_jalans.rute_id', '=', 'rutes.id')
            ->where('audit.filter_oli_log_id', $log->id)
            ->select('transaksis.id', 'transaksis.created_at', 'kas_uang_jalans.nomor_uang_jalan', 'rutes.nama as rute', 'rutes.jarak', 'audit.nilai_ritase')
            ->orderBy('transaksis.created_at')->orderBy('transaksis.id');
        if ($request->user()->isVendorAccount()) {
            $transactions->where('kas_uang_jalans.vendor_id', $request->user()->vendor_id)->where('kas_uang_jalans.vehicle_id', $log->vehicle_id);
            $total = (clone $transactions)->count();

            return response()->json(['data' => $transactions->offset(max(0, $request->integer('start')))->limit(25)->get(), 'recordsFiltered' => $total]);
        }
        $transactions = $transactions->get();

        return view('rekap.statistik.filter-oli.transactions', compact('log', 'transactions'));
    }

    public function updateHistory(Request $request, int $log, MaintenanceLogWriter $writer): RedirectResponse
    {
        $data = $request->validate(['tanggal_ganti' => ['required', 'date_format:Y-m-d', 'before_or_equal:today']], [
            'tanggal_ganti.required' => 'Tanggal ganti wajib diisi.',
            'tanggal_ganti.date_format' => 'Format tanggal ganti tidak valid.',
            'tanggal_ganti.before_or_equal' => 'Tanggal ganti tidak boleh setelah hari ini.',
        ]);
        try {
            $writer->rescheduleFilterOil(FilterOliLog::findOrFail($log), Carbon::parse($data['tanggal_ganti'])->startOfDay());
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Tanggal ganti gagal diperbarui. Silakan coba kembali.');
        }

        return back()->with('success', 'Tanggal ganti diperbarui. Ritase dan catatan transaksi seluruh periode penggantian telah dihitung ulang.');
    }

    public function deleteHistory(int $log, MaintenanceLogWriter $writer): RedirectResponse
    {
        try {
            $writer->removeFilterOil(FilterOliLog::findOrFail($log));
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Histori gagal dihapus. Silakan coba kembali.');
        }

        return back()->with('success', 'Histori dihapus dan ritase dihitung ulang. Invoice dan catatan pembayaran tetap tersimpan.');
    }
}
