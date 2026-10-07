<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutFilterOliGantiRequest;
use App\Http\Requests\StoreFilterOliGantiCartRequest;
use App\Models\FilterOliGantiCart;
use App\Models\FilterOliGantiInvoice;
use App\Models\FilterOliGantiInvoiceDetail;
use App\Models\FilterOliLog;
use App\Models\KategoriFilterOliMesin;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\FilterOliRitaseService;
use App\Services\KasBesarService;
use App\Services\KasVendorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class FilterOliGantiController extends Controller
{
    public function __construct(private FilterOliRitaseService $ritase) {}

    private function cart(Request $request): Builder
    {
        return FilterOliGantiCart::where('user_id', $request->user()->id);
    }

    public function index(Request $request): View
    {
        $request->validate(['vehicle_id' => ['nullable', Rule::exists('vehicles', 'id')->where(fn ($query) => $query->where('status', '!=', 'nonaktif')->where('pembatasan_filter_oli', true))]]);
        $cartItems = $this->cart($request)->get();
        $vehicleId = $cartItems->first()?->vehicle_id ?? $request->input('vehicle_id');
        $vehicle = $vehicleId ? Vehicle::with('vendor')->findOrFail($vehicleId) : null;
        $logs = $vehicleId ? FilterOliLog::with('kategori')->where('vehicle_id', $vehicleId)
            ->orderByDesc('created_at')->orderByDesc('id')->get()->unique('kategori_filter_oli_mesin_id') : collect();

        return view('billing.form-maintenance.filter-oli.index', [
            'vehicles' => Vehicle::where('status', '!=', 'nonaktif')->where('pembatasan_filter_oli', true)->orderBy('nomor_lambung')->get(),
            'kategori' => KategoriFilterOliMesin::orderBy('id')->get(),
            'vehicle' => $vehicle, 'logs' => $logs, 'cartItems' => $cartItems,
        ]);
    }

    public function add(StoreFilterOliGantiCartRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $cart = $this->cart($request)->get();
            $data = $request->validated();
            if ($cart->isNotEmpty() && $cart->first()->vehicle_id !== (int) $data['vehicle_id']) {
                throw ValidationException::withMessages(['vehicle_id' => 'Selesaikan atau kosongkan keranjang unit sebelumnya.']);
            }
            if ($cart->contains('kategori_filter_oli_mesin_id', $data['kategori_filter_oli_mesin_id'])) {
                throw ValidationException::withMessages(['kategori_filter_oli_mesin_id' => 'Kategori ini sudah ada di keranjang.']);
            }
            FilterOliGantiCart::create([
                'user_id' => $request->user()->id, 'vehicle_id' => $data['vehicle_id'],
                'kategori_filter_oli_mesin_id' => $data['kategori_filter_oli_mesin_id'],
                'merk' => $data['merk'], 'kondisi' => $data['kondisi'], 'ritase' => 0,
                'created_at' => Carbon::parse($data['tanggal_ganti'])->startOfDay(),
            ]);
        });

        return to_route('billing.form-maintenance.filter-oli')->with('success', 'Item berhasil ditambahkan ke keranjang.');
    }

    public function delete(Request $request, int $id): RedirectResponse
    {
        DB::transaction(function () use ($request, $id): void {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $this->cart($request)->findOrFail($id)->delete();
        });

        return back()->with('success', 'Item keranjang berhasil dihapus.');
    }

    public function clear(Request $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $this->cart($request)->delete();
        });

        return to_route('billing.form-maintenance.filter-oli')->with('success', 'Keranjang berhasil dikosongkan.');
    }

    public function confirm(Request $request): View|RedirectResponse
    {
        $items = $this->cart($request)->with(['vehicle.vendor', 'kategori'])->orderBy('id')->get();
        if ($items->isEmpty()) {
            return to_route('billing.form-maintenance.filter-oli')->with('error', 'Keranjang masih kosong.');
        }

        return view('billing.form-maintenance.filter-oli.cart', ['cartItems' => $items, 'vehicle' => $items->first()->vehicle, 'kasBesarEnabled' => (bool) config('maintenance.filter_oli.kas_besar_enabled')]);
    }

    public function checkout(CheckoutFilterOliGantiRequest $request): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($request, $data): void {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $items = $this->cart($request)->with('kategori')->lockForUpdate()->get();
            $submittedIds = array_map('intval', array_keys($data['items']));
            $cartIds = $items->modelKeys();
            sort($submittedIds);
            sort($cartIds);
            if ($items->isEmpty() || $submittedIds !== $cartIds || $items->contains(fn ($item) => $item->vehicle_id !== (int) $data['vehicle_id'])) {
                throw ValidationException::withMessages(['items' => 'Keranjang berubah atau tidak sesuai. Muat ulang dan periksa kembali.']);
            }
            $vehicle = Vehicle::findOrFail($data['vehicle_id']);
            if ($vehicle->status === 'nonaktif') {
                throw ValidationException::withMessages(['vehicle_id' => 'Kendaraan sudah nonaktif.']);
            }
            $invoice = FilterOliGantiInvoice::create([
                'no_invoice' => 'INV-FO-'.now()->format('Ymd').'-'.Str::ulid(),
                'vehicle_id' => $vehicle->id, 'user_id' => $request->user()->id,
                'pembayaran' => $data['pembayaran'], 'total_nominal' => $data['total_nominal'] ?? 0,
                'nama_bank' => $data['nama_bank'] ?? null, 'nomor_rekening' => $data['nomor_rekening'] ?? null,
                'nama_rekening' => $data['nama_rekening'] ?? null,
            ]);
            $invoice->update(['no_invoice' => 'FO-'.str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT)]);
            foreach ($items as $item) {
                $date = Carbon::parse($data['items'][$item->id]['tanggal_ganti'])->startOfDay();
                $invoice->details()->create([
                    'kategori_filter_oli_mesin_id' => $item->kategori_filter_oli_mesin_id,
                    'merk' => $item->merk, 'kondisi' => $item->kondisi,
                    'ritase' => $this->ritase->preview($vehicle->id, $item->kategori_filter_oli_mesin_id, $date)->sum('nilai_ritase'),
                    'limit_ritase' => $item->kategori->limit_ritase,
                    'created_at' => $date,
                ]);
            }
            $this->cart($request)->delete();
        });

        return to_route('billing.form-maintenance.filter-oli')->with('success', 'Penggantian filter & oli mesin dikirim untuk otorisasi.');
    }

    public function authorization(): View
    {
        $invoices = FilterOliGantiInvoice::with(['vehicle.vendor', 'details.kategori'])
            ->where('status', FilterOliGantiInvoice::STATUS_PENDING)->oldest()->get();
        $logs = FilterOliLog::whereIn('vehicle_id', $invoices->pluck('vehicle_id'))
            ->orderByDesc('created_at')->orderByDesc('id')->get()->groupBy('vehicle_id');
        foreach ($invoices as $invoice) {
            $warnings = [];
            foreach ($invoice->details as $detail) {
                $previous = ($logs->get($invoice->vehicle_id) ?? collect())->first(fn ($log) => $log->kategori_filter_oli_mesin_id === $detail->kategori_filter_oli_mesin_id && $log->created_at <= $detail->created_at);
                if ($previous && (float) $previous->ritase < $detail->kategori->limit_ritase) {
                    $warnings[] = $detail->kategori->nama.': '.number_format((float) $previous->ritase, 1, ',', '.').' dari '.$detail->kategori->limit_ritase.' rit';
                }
            }
            $invoice->setAttribute('ritase_warning', implode('; ', $warnings));
        }

        return view('billing.otorisasi-maintenance.filter-oli.index', compact('invoices'));
    }

    public function show(int $id): View
    {
        $invoice = FilterOliGantiInvoice::with(['vehicle.vendor', 'details.kategori'])->findOrFail($id);
        $logs = FilterOliLog::where('vehicle_id', $invoice->vehicle_id)
            ->whereNotIn('id', $invoice->details->pluck('filter_oli_log_id')->filter())
            ->orderByDesc('created_at')->orderByDesc('id')->get();
        $previousLogs = $invoice->details->mapWithKeys(fn ($detail) => [$detail->id => $logs->first(fn ($log) => $log->kategori_filter_oli_mesin_id === $detail->kategori_filter_oli_mesin_id && $log->created_at <= $detail->created_at)]);

        $ritaseTransactions = $invoice->details->mapWithKeys(function ($detail) use ($invoice) {
            if ($detail->filter_oli_log_id) {
                $rows = DB::table('filter_oli_log_transaksis as audit')
                    ->join('transaksis', 'audit.transaksi_id', '=', 'transaksis.id')
                    ->join('kas_uang_jalans', 'transaksis.kas_uang_jalan_id', '=', 'kas_uang_jalans.id')
                    ->join('rutes', 'kas_uang_jalans.rute_id', '=', 'rutes.id')
                    ->where('audit.filter_oli_log_id', $detail->filter_oli_log_id)
                    ->select('transaksis.id', 'transaksis.created_at', 'kas_uang_jalans.nomor_uang_jalan', 'rutes.nama as rute', 'rutes.jarak', 'audit.nilai_ritase')
                    ->orderBy('transaksis.created_at')->get();
            } elseif ($invoice->status === FilterOliGantiInvoice::STATUS_PENDING) {
                $rows = $this->ritase->preview($invoice->vehicle_id, $detail->kategori_filter_oli_mesin_id, $detail->created_at);
            } else {
                return [$detail->id => collect()];
            }
            $detail->ritase = $rows->sum('nilai_ritase');

            return [$detail->id => $rows];
        });

        return view('billing.otorisasi-maintenance.filter-oli.show', compact('invoice', 'previousLogs', 'ritaseTransactions'));
    }

    public function updateDetail(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'merk' => ['required', 'string', 'max:100'],
            'kondisi' => ['required', 'integer', 'min:1', 'max:100'],
            'tanggal_ganti' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);
        $detail = FilterOliGantiInvoiceDetail::findOrFail($id);
        try {
            DB::transaction(function () use ($detail, $data): void {
                $invoice = FilterOliGantiInvoice::lockForUpdate()->findOrFail($detail->filter_oli_ganti_invoice_id);
                $this->ensurePending($invoice);
                $date = Carbon::parse($data['tanggal_ganti'])->startOfDay();
                $detail->update(['merk' => $data['merk'], 'kondisi' => $data['kondisi'],
                    'created_at' => $date,
                    'ritase' => $this->ritase->preview($invoice->vehicle_id, $detail->kategori_filter_oli_mesin_id, $date)->sum('nilai_ritase')]);
            });
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->validator->errors()->first());
        }

        return back()->with('success', 'Detail penggantian berhasil diperbarui.');
    }

    public function approve(Request $request, int $id, KasBesarService $kasBesar, KasVendorService $kasVendor): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $id, $kasBesar, $kasVendor): void {
                $invoice = FilterOliGantiInvoice::with(['vehicle', 'details'])->lockForUpdate()->findOrFail($id);
                $this->ensurePending($invoice);
                foreach ($invoice->details as $detail) {
                    $log = FilterOliLog::create([
                        'vehicle_id' => $invoice->vehicle_id, 'kategori_filter_oli_mesin_id' => $detail->kategori_filter_oli_mesin_id,
                        'merk' => $detail->merk, 'kondisi' => $detail->kondisi, 'ritase' => $detail->ritase,
                        'created_at' => $detail->created_at,
                    ]);
                    $detail->update(['filter_oli_log_id' => $log->id]);
                }
                $this->ritase->refreshVehicle($invoice->vehicle_id);
                if ($invoice->pembayaran === 'kas_besar') {
                    $kasBesar->potongSaldo([
                        'nominal_transaksi' => (int) $invoice->total_nominal,
                        'uraian' => 'Penggantian Filter & Oli Mesin Unit '.$invoice->vehicle->nomor_lambung,
                        'filter_oli_ganti_invoice_id' => $invoice->id,
                        'bank' => $invoice->nama_bank, 'no_rekening' => $invoice->nomor_rekening,
                        'transfer_ke' => $invoice->nama_rekening,
                    ]);
                    if ($invoice->vehicle->vendor_id) {
                        $kasVendor->tambahHutang([
                            'vendor_id' => $invoice->vehicle->vendor_id, 'vehicle_id' => $invoice->vehicle_id,
                            'nominal_transaksi' => (int) $invoice->total_nominal,
                            'filter_oli_ganti_invoice_id' => $invoice->id,
                            'uraian' => 'Penggantian Filter & Oli Mesin ('.$invoice->no_invoice.')',
                        ]);
                    }
                }
                $invoice->update(['status' => FilterOliGantiInvoice::STATUS_APPROVED,
                    'authorized_by' => $request->user()->id, 'authorized_at' => now()]);
            });
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->validator->errors()->first());
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Otorisasi gagal. Periksa saldo Kas Besar dan coba kembali.');
        }

        return back()->with('success', 'Otorisasi berhasil. Histori penggantian filter & oli mesin telah dicatat.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $id): void {
                $invoice = FilterOliGantiInvoice::lockForUpdate()->findOrFail($id);
                $this->ensurePending($invoice);
                $invoice->update(['status' => FilterOliGantiInvoice::STATUS_REJECTED,
                    'authorized_by' => $request->user()->id, 'authorized_at' => now()]);
            });
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->validator->errors()->first());
        }

        return back()->with('success', 'Penggantian filter & oli mesin ditolak.');
    }

    private function ensurePending(FilterOliGantiInvoice $invoice): void
    {
        if ($invoice->status !== FilterOliGantiInvoice::STATUS_PENDING) {
            throw ValidationException::withMessages(['invoice' => 'Invoice ini sudah diproses sebelumnya.']);
        }
    }
}
