<?php

namespace App\Http\Controllers;

use App\Models\BanLog;
use App\Models\Customer;
use App\Models\GroupWa;
use App\Models\InvoiceAdditional;
use App\Models\InvoiceBayar;
use App\Models\InvoiceBonus;
use App\Models\InvoiceBonusDetail;
use App\Models\InvoiceCsr;
use App\Models\InvoiceCsrDetail;
use App\Models\InvoiceTagihan;
use App\Models\KasUangJalan;
use App\Models\KeranjangNotaBayar;
use App\Models\Konfigurasi;
use App\Models\PasswordKonfirmasi;
use App\Models\Rekening;
use App\Models\Sponsor;
use App\Models\Transaksi;
use App\Models\TransaksiAdditional;
use App\Models\UjDitahanDetail;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Services\CustomerBilling;
use App\Services\FilterOliRitaseService;
use App\Services\UjDitahanBanService;
use App\Services\UnloadingNoteCompletion;
use App\Services\VendorPaymentCart;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransaksiController extends Controller
{
    public function index()
    {
        $data = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
            ->leftJoin('vehicles as v', 'kuj.vehicle_id', 'v.id')
            ->select('transaksis.*', 'kuj.customer_id as customer_id', 'v.vendor_id as vendor_id')
            ->where('transaksis.void', 0)->get();

        $customer = Customer::all();

        $invoice = InvoiceTagihan::where('lunas', 0)->count();
        $bayar = InvoiceBayar::where('lunas', 0)->count();
        $bonus = InvoiceBonus::where('lunas', 0)->count();
        $invoice_csr = InvoiceCsr::where('lunas', 0)->count();

        $vendor = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
            ->where('status', 3)
            ->where('transaksis.bayar', 0)
            ->where('transaksis.void', 0)
            ->get()->unique('vendor_id');

        $sponsor = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
            ->join('vendors as v', 'kuj.vendor_id', 'v.id')
            ->join('sponsors as s', 'v.sponsor_id', 's.id')
            ->where('transaksis.bonus', 0)
            ->where('transaksis.status', 3)
            ->where('transaksis.void', 0)
            ->get()->unique('sponsor_id');

        $csr = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
            ->join('customers as c', 'kuj.customer_id', 'c.id')
            ->where('transaksis.csr', 0)
            ->where('transaksis.status', 3)
            ->where('transaksis.void', 0)
            ->where('c.csr', 1)
            ->get()->unique('customer_id');

        // dd($bayar);
        return view('billing.transaksi.index', [
            'data' => $data,
            'customer' => $customer,
            'vendor' => $vendor,
            'sponsor' => $sponsor,
            'invoice' => $invoice,
            'bayar' => $bayar,
            'bonus' => $bonus,
            'csr' => $csr,
            'invoice_csr' => $invoice_csr,
        ]);
    }

    public function nota_muat()
    {
        $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute', 'kas_uang_jalan.customer', 'kas_uang_jalan.vehicle'])->where('status', 1)->where('void', 0)->get();
        $konfigurasi = Konfigurasi::where('kode', 'nota-muat')->first()->status ?? 0;

        return view('billing.transaksi.nota-muat.index', [
            'data' => $data,
            'konfigurasi' => $konfigurasi,

        ]);
    }

    public function nota_muat_update(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'nota_muat' => 'required',
            'tonase' => 'required|numeric',
            'tanggal_muat' => 'required',
            'gross_muat' => 'nullable|numeric',
            'tarra_muat' => 'nullable|numeric',
        ]);

        $konfigurasi = Konfigurasi::where('kode', 'nota-muat')->first()->status ?? 0;

        if ($konfigurasi == 1) {
            // check if tanggal muat is older than 2 day from today, if true then return error
            $tanggalMuat = strtotime($data['tanggal_muat']);
            $today = strtotime(date('Y-m-d'));

            $diff = $today - $tanggalMuat;
            $diff = $diff / (60 * 60 * 24);

            if ($diff > 2) {
                return redirect()->back()->with('error', 'Tanggal muat tidak boleh lebih dari H-2 dari hari ini!!');
            }

        }

        $data['harga_customer'] = $transaksi->kas_uang_jalan->customer->customer_tagihan->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->harga_tagihan;

        $vendor = $transaksi->kas_uang_jalan->vendor->pembayaran;

        if ($vendor == 'opname') {
            $data['harga_vendor'] = $transaksi->kas_uang_jalan->customer->customer_tagihan->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->opname;
        } elseif ($vendor == 'titipan') {
            $data['harga_vendor'] = $transaksi->kas_uang_jalan->customer->customer_tagihan->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->titipan;
        } elseif ($vendor == 'titipan_khusus') {
            $data['harga_vendor'] = $transaksi->kas_uang_jalan->customer->customer_tagihan->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->titipan_khusus;
        }

        if ($transaksi->kas_uang_jalan->customer->csr == 1) {
            $jarak = $transaksi->kas_uang_jalan->rute->jarak;
            if ($jarak > 50) {
                $data['harga_csr'] = $transaksi->kas_uang_jalan->customer->harga_csr_atas;
            } else {
                $data['harga_csr'] = $transaksi->kas_uang_jalan->customer->harga_csr_bawah;
            }
        }

        $data['tanggal_muat'] = date('Y-m-d', strtotime($data['tanggal_muat']));
        $data['status'] = 2;

        try {
            $transaksi->update($data);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Terdapat nota yang sama!!');
        }

        return redirect()->back()->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_bongkar()
    {
        $data = Transaksi::getNotaBongkar();

        return view('billing.transaksi.nota-bongkar.index', [
            'data' => $data,
        ]);
    }

    public function nota_bongkar_update(Request $request, Transaksi $transaksi, UnloadingNoteCompletion $completion)
    {
        $data = $request->validate([
            'nota_bongkar' => 'required',
            'timbangan_bongkar' => 'required|numeric',
            'tanggal_bongkar' => 'required',
            'gross_bongkar' => 'nullable|numeric',
            'tarra_bongkar' => 'nullable|numeric',
        ]);

        try {
            $completion->complete($transaksi->id, $data);
        } catch (ValidationException $exception) {
            return redirect()->back()->with('error', collect($exception->errors())->flatten()->first());
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }

        return redirect()->back()->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_tagihan(Request $request, Customer $customer)
    {
        $req = $request->validate([
            'rute_id' => 'nullable|exists:rutes,id',
            'filter_date' => 'nullable|required_if:tanggal_filter,!=, null|in:tanggal_muat,tanggal_bongkar,tanggal',
            'tanggal_filter' => 'nullable|required_if:filter_date,tanggal_muat,tanggal_bongkar,tanggal',
        ]);

        $rute_id = $req['rute_id'] ?? null;
        $filter_date = $req['filter_date'] ?? null;
        $tanggal_filter = $req['tanggal_filter'] ?? null;

        /** @var UrlGenerator */
        $url = url();

        // Store current URL in session
        session(['previous_url' => $url->full()]);

        $rute = $customer->rute;

        $data = Transaksi::getTagihanData($customer->id, $rute_id, $filter_date, $tanggal_filter);
        $keranjang = Transaksi::getKeranjangTagihanData($customer->id)->count();

        return view('billing.transaksi.tagihan.index', [
            'data' => $data,
            'customer' => $customer,
            'rute' => $rute,
            'rute_id' => $rute_id,
            'filter_date' => $req['filter_date'] ?? null,
            'tanggal_filter' => $req['tanggal_filter'] ?? null,
            'keranjang' => $keranjang,
        ]);
    }

    public function nota_tagihan_checked(Transaksi $transaksi)
    {
        $db = new Transaksi;

        $db->changeStateNotaFisik($transaksi->id);

        return redirect()->back()->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_tagihan_unchecked(Transaksi $transaksi, Request $request)
    {
        $data = $request->validate([
            'password' => 'required',
        ]);

        $password = PasswordKonfirmasi::first();

        if (! $password) {
            return response()->json(['message' => 'Password not found'], 404);
        }

        if ($data['password'] != $password->password) {
            return response()->json(['message' => 'Password yang anda masukan Salah'], 400);
        }

        $db = new Transaksi;

        $db->changeStateNotaFisik($transaksi->id);

        return response()->json(['message' => 'Berhasil menyimpan data!!'], 200);

    }

    public function tagihan_export(Request $request, Customer $customer)
    {
        $req = $request->validate([
            'rute_id' => 'nullable|exists:rutes,id',
            'filter_date' => 'nullable|required_if:tanggal_filter,!=, null|in:tanggal_muat,tanggal_bongkar,tanggal',
            'tanggal_filter' => 'nullable|required_if:filter_date,tanggal_muat,tanggal_bongkar,tanggal',
        ]);

        $rute_id = $req['rute_id'] ?? null;
        $filter_date = $req['filter_date'] ?? null;
        $tanggal_filter = $req['tanggal_filter'] ?? null;

        $data = Transaksi::getTagihanData($customer->id, $rute_id, $filter_date, $tanggal_filter);

        // Tabel DomPDF butuh ±1,2 MB per baris walau CSS sudah ringan (pdf-nota-tagihan.css); batas server
        // 128 MB / 30 dtk membuat nota panjang gagal. Naikkan khusus ekspor ini (±850 baris).
        ini_set('memory_limit', '1024M');
        set_time_limit(120);

        // get latest data from month before current month
        // dd($bulan);
        if (Auth::user()->role == 'admin') {
            $pdf = Pdf::loadview('billing.transaksi.tagihan.export-admin', [
                'data' => $data,
                'customer' => $customer,
            ])->setPaper('a4', 'landscape');
        } else {
            $pdf = Pdf::loadview('billing.transaksi.tagihan.export', [
                'data' => $data,
                'customer' => $customer,
            ])->setPaper('a4', 'landscape');
        }

        return $pdf->stream('Nota Tagihan '.$customer->singkatan.'.pdf');
    }

    public function void_tagihan(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'password' => 'required',
        ]);

        $password = PasswordKonfirmasi::first();

        if (! $password) {
            return redirect()->back()->with('error', 'Password belum diatur!!');
        }

        if ($data['password'] != $password->password) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        return view('billing.transaksi.tagihan.void', [
            'data' => $transaksi,
        ]);
    }

    public function void_tagihan_store(Request $request, Transaksi $transaksi, FilterOliRitaseService $filterOliRitase): RedirectResponse
    {
        return $this->processVoid($request, $transaksi, $filterOliRitase);
    }

    public function void(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'password' => 'required',
        ]);

        $password = PasswordKonfirmasi::first();

        if (! $password) {
            return redirect()->back()->with('error', 'Password belum diatur!!');
        }

        if ($data['password'] != $password->password) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        return view('billing.transaksi.void', [
            'data' => $transaksi,
        ]);
    }

    public function void_store(Request $request, Transaksi $transaksi, FilterOliRitaseService $filterOliRitase): RedirectResponse
    {
        return $this->processVoid($request, $transaksi, $filterOliRitase);
    }

    private function processVoid(Request $request, Transaksi $transaksi, FilterOliRitaseService $filterOliRitase): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'alasan' => ['required', 'string', 'max:200'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('billing.index')
                ->withErrors($validator)
                ->withInput()
                ->with('error', $validator->errors()->first('alasan'));
        }

        $data = $validator->validated();
        $data['void'] = 1;
        $data['nota_muat'] = null;
        $data['nota_bongkar'] = null;

        $transaksi->load(['kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute']);
        $kuj = $transaksi->kas_uang_jalan;
        $vehicle = $kuj->vehicle;

        if ($transaksi->void == 1) {
            return redirect()->route('billing.index')->with('error', 'Transaksi sudah di void sebelumnya!!');
        }

        try {
            DB::beginTransaction();
            $vehicle = Vehicle::whereKey($vehicle->id)->lockForUpdate()->firstOrFail();
            $transaksi = Transaksi::whereKey($transaksi->id)->lockForUpdate()->firstOrFail();
            if ($transaksi->void == 1) {
                DB::rollBack();

                return redirect()->route('billing.index')->with('error', 'Transaksi sudah di void sebelumnya!!');
            }

            $transaksi->update($data);

            $rek = Rekening::where('untuk', 'kas-uang-jalan')->lockForUpdate()->first();
            $last = KasUangJalan::latest('id')->lockForUpdate()->first();

            $store = KasUangJalan::create([
                'void' => 1,
                'kode_void' => 'UJ'.sprintf('%02d', $kuj->nomor_uang_jalan),
                'jenis_transaksi_id' => 1,
                'nominal_transaksi' => $kuj->nominal_transaksi,
                'tanggal' => date('Y-m-d'),
                'saldo' => ($last ? $last->saldo : 0) + $kuj->nominal_transaksi,
                'transfer_ke' => substr($rek->nama_rekening ?? '', 0, 15),
                'bank' => $rek->nama_bank ?? '-',
                'no_rekening' => $rek->nomor_rekening ?? '-',
            ]);

            $this->rollbackRitaseBan($kuj->vehicle_id, $kuj->rute, $transaksi->id);
            $filterOliRitase->rollbackTransaction($transaksi);

            $cekMobil = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                ->where('kuj.vehicle_id', $kuj->vehicle_id)
                ->where('transaksis.status', '<', 3)
                ->where('transaksis.void', 0)
                ->exists();

            if (! $cekMobil) {
                $vehicle->update(['status' => 'aktif']);
            }

            if ($transaksi->nota_fisik == 0 && $vehicle->do_count > 0) {
                $vehicle->decrement('do_count');
            }

            $cekUjDitahan = UjDitahanDetail::where('transaksi_id', $transaksi->id)
                ->where('jenis', 'masuk')->orderBy('id')->lockForUpdate()->first();

            if ($cekUjDitahan) {
                $ujDitahan = $cekUjDitahan->ujDitahan()->lockForUpdate()->firstOrFail();

                UjDitahanDetail::create([
                    'uj_ditahan_id' => $ujDitahan->id,
                    'jenis' => 'keluar',
                    'transaksi_id' => $transaksi->id,
                    'driver_id' => $cekUjDitahan->driver_id,
                    'bank' => $rek->nama_bank,
                    'no_rekening' => $rek->nomor_rekening,
                    'nama_rekening' => $rek->nama_rekening,
                    'nominal' => $cekUjDitahan->nominal,
                    'keterangan' => 'Void UJ'.sprintf('%02d', $kuj->nomor_uang_jalan).' - '.$data['alasan'],
                ]);

                $ujDitahan->decrement('saldo', $cekUjDitahan->nominal);
                $ujDitahan->increment('total_keluar', $cekUjDitahan->nominal);
            }
            $nominalUjDitahanBan = app(UjDitahanBanService::class)->reverse($transaksi, $vehicle, $data['alasan'], $rek);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('billing.index')->with('error', 'Terdapat Error pada saat menyimpan data!!'.$th->getMessage());
        }

        // ==========================================
        // PENGIRIMAN WA
        // ==========================================
        try {
            $dbWa = app(GroupWa::class);
            $group = $dbWa->where('untuk', 'kas-uang-jalan')->first();

            $nominalUjDitahan = 0;

            if ($cekUjDitahan) {
                $nominalUjDitahan = $cekUjDitahan->nominal;
            }

            if ($group) {
                $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                            "*Void Uang Jalan*\n".
                            "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                            '*UJ'.sprintf('%02d', $kuj->nomor_uang_jalan)."*\n\n".
                            'Nomor Lambung : '.$vehicle->nomor_lambung."\n".
                            'Vendor : '.$kuj->vendor->nama."\n\n".
                            'Tambang : '.$kuj->customer->singkatan."\n".
                            'Rute : '.$kuj->rute->nama."\n\n".
                            'Alasan : '.$data['alasan']."\n".
                            'Nilai :  *Rp. '.number_format($kuj->nominal_transaksi - $nominalUjDitahan - $nominalUjDitahanBan, 0, ',', '.').",-*\n\n".
                            "Ditransfer ke rek:\n\n".
                            'Bank     : '.($rek->nama_bank ?? '-')."\n".
                            'Nama    : '.($rek->nama_rekening ?? '-')."\n".
                            'No. Rek : '.($rek->nomor_rekening ?? '-')."\n\n".
                            "==========================\n".
                            "Sisa Saldo Kas Uang Jalan : \n".
                            'Rp. '.number_format($store->saldo, 0, ',', '.')."\n\n".
                            "Terima kasih 🙏🙏🙏\n";

                try {
                    $dbWa->sendWa($group->nama_group, $pesan);
                } catch (\Throwable $exception) {
                    Log::warning('Notifikasi void kas uang jalan gagal.', ['transaksi_id' => $transaksi->id, 'error' => $exception->getMessage()]);
                }
            }

            if ($cekUjDitahan || $nominalUjDitahanBan > 0) {
                $pesan2 = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                            "*Void Uang Jalan Ditahan*\n".
                            "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                            '*UJ'.sprintf('%02d', $kuj->nomor_uang_jalan)."*\n\n".
                            'Nomor Lambung : '.$vehicle->nomor_lambung."\n".
                            'Vendor : '.$kuj->vendor->nama."\n\n".
                            'Tambang : '.$kuj->customer->singkatan."\n".
                            'Rute : '.$kuj->rute->nama."\n\n".
                            'Alasan : '.$data['alasan']."\n".
                            'Nilai :  *Rp. '.number_format($nominalUjDitahan + $nominalUjDitahanBan, 0, ',', '.').",-*\n\n".
                            'Sparepart : Rp. '.number_format($nominalUjDitahan, 0, ',', '.')."\n".
                            'Ban : Rp. '.number_format($nominalUjDitahanBan, 0, ',', '.')."\n\n".
                            "Ditransfer ke rek:\n\n".
                            'Bank     : '.($rek->nama_bank ?? '-')."\n".
                            'Nama    : '.($rek->nama_rekening ?? '-')."\n".
                            'No. Rek : '.($rek->nomor_rekening ?? '-')."\n\n".
                            "==========================\n".
                            "Sisa Saldo Kas Uang Jalan : \n".
                            'Rp. '.number_format($store->saldo, 0, ',', '.')."\n\n".
                            "Terima kasih 🙏🙏🙏\n";

                $groupUjDitahan = $dbWa->where('untuk', 'kas-uj-ditahan')->first();

                if ($groupUjDitahan) {
                    $dbWa->sendWa($groupUjDitahan->nama_group, $pesan2);
                }
            }
        } catch (\Throwable $th) {
            Log::warning('Notifikasi void uang jalan gagal.', ['transaksi_id' => $transaksi->id, 'error' => $th->getMessage()]);
        }

        return redirect()->route('billing.index')->with('success', 'Berhasil menyimpan data!!');
    }

    /**
     * Rollback ritase ban luar kendaraan berdasarkan transaksi
     */
    private function rollbackRitaseBan($vehicleId, $rute, $transaksiId)
    {
        if (! $transaksiId) {
            return;
        }

        // 1. Cek apakah transaksi ini memiliki riwayat di tabel pivot
        $pivotRecords = DB::table('ban_log_transaksis')->where('transaksi_id', $transaksiId)->get();

        if ($pivotRecords->isNotEmpty()) {
            // SKENARIO A: TRANSAKSI BARU (Ada di Pivot)
            // Kurangi ritase tepat pada ban yang dulu terpasang saat transaksi ini dibuat
            foreach ($pivotRecords as $record) {
                BanLog::where('id', $record->ban_log_id)->update([
                    'ritase' => DB::raw("GREATEST(0, ritase - {$record->nilai_ritase})"),
                ]);
            }

            // Hapus riwayat dari pivot agar tidak muncul di modal pop-up histori
            DB::table('ban_log_transaksis')->where('transaksi_id', $transaksiId)->delete();

        } else {
            // SKENARIO B: TRANSAKSI LAMA (Belum tercatat di Pivot / Fallback)
            if (! $rute || ! $vehicleId) {
                return;
            }

            $jarak = (float) $rute->jarak;
            $penguranganRitase = ($jarak > 50) ? 1.0 : 0.5;

            // Cari ID BanLog TERBARU untuk setiap posisi ban pada kendaraan tersebut
            $activeBanLogIds = BanLog::where('vehicle_id', $vehicleId)
                ->select(DB::raw('MAX(id) as id'))
                ->groupBy('posisi_ban_id')
                ->pluck('id');

            // Kurangi ritase dengan proteksi GREATEST agar nilai tidak minus (< 0)
            if ($activeBanLogIds->isNotEmpty()) {
                BanLog::whereIn('id', $activeBanLogIds)->update([
                    'ritase' => DB::raw("GREATEST(0, ritase - {$penguranganRitase})"),
                ]);
            }
        }
    }

    public function back(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'password' => 'required',
        ]);

        $password = PasswordKonfirmasi::first();

        if (! $password) {
            return redirect()->back()->with('error', 'Password belum diatur!!');
        }

        if ($data['password'] != $password->password) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        $transaksi->update([
            'status' => 1,
            'nota_muat' => null,
            'tonase' => null,
            'nota_bongkar' => null,
            'timbangan_bongkar' => null,
        ]);

        return redirect()->route('billing.transaksi.index')->with('success', 'Berhasil menyimpan data!!');

    }

    public function nota_tagihan_edit(Transaksi $transaksi, Request $request)
    {
        $data = $request->validate([
            'password' => 'required',
        ]);

        $password = PasswordKonfirmasi::first();

        if (! $password) {
            return redirect()->back()->with('error', 'Password belum diatur!!');
        }

        if ($data['password'] != $password->password) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        return view('billing.transaksi.tagihan.edit', [
            'd' => $transaksi,
        ]);
    }

    public function nota_tagihan_update(Request $request, Transaksi $transaksi, CustomerBilling $billing)
    {
        $data = $request->validate([
            'tonase' => 'required|numeric',
            'gross_muat' => 'nullable|numeric',
            'tarra_muat' => 'nullable|numeric',
            'timbangan_bongkar' => 'required|numeric',
            'gross_bongkar' => 'nullable|numeric',
            'tarra_bongkar' => 'nullable|numeric',
            'nota_muat' => 'required',
            'nota_bongkar' => 'required',
            'tanggal_muat' => 'required',
            'tanggal_bongkar' => 'required',
        ]);

        try {
            $billing->updateNote($transaksi->id, $data);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th instanceof ValidationException ? collect($th->errors())->flatten()->first() : 'Terdapat nota yang sama!!');
        }

        // Redirect to previous URL
        return redirect()->to(session('previous_url'))->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_tagihan_lanjut_pilih(Request $request, Customer $customer, CustomerBilling $billing)
    {
        $data = $request->validate([
            'selectedData' => 'required|string',
        ]);
        $selectedIds = array_map('intval', array_filter(explode(',', trim($data['selectedData'], ','))));
        if (empty($selectedIds)) {
            return back()->with('error', 'Tidak ada data yang dipilih.');
        }

        try {
            $billing->addToCart($customer, $selectedIds);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat memproses data: '.$e->getMessage());
        }

        return redirect()->route('transaksi.nota-tagihan', $customer)
            ->with('success', 'Berhasil memindahkan data ke Keranjang. Silahkan cek keranjang untuk validasi lanjutan!!');
    }

    public function keranjang_tagihan(Request $request, Customer $customer)
    {
        $req = $request->validate([
            'rute_id' => 'nullable|exists:rutes,id',
            'filter_date' => 'nullable|required_if:tanggal_filter,!=, null|in:tanggal_muat,tanggal_bongkar,tanggal',
            'tanggal_filter' => 'nullable|required_if:filter_date,tanggal_muat,tanggal_bongkar,tanggal',
        ]);

        $rute_id = $req['rute_id'] ?? null;
        $filter_date = $req['filter_date'] ?? null;
        $tanggal_filter = $req['tanggal_filter'] ?? null;

        /** @var UrlGenerator */
        $url = url();

        // Store current URL in session
        session(['previous_url' => $url->full()]);

        $rute = $customer->rute;

        $data = Transaksi::getKeranjangTagihanData($customer->id, $rute_id, $filter_date, $tanggal_filter);
        $additionalsData = InvoiceAdditional::where('customer_id', $customer->id)
            ->where('status', 1)
            ->where('is_finished', 0)
            ->whereIn('jenis', ['kompensasi_jr', 'penyesuaian_bbm', 'achievement'])
            ->get()
            ->keyBy('jenis');

        // Susun array additionals dari koleksi yang sudah di-keyBy
        $additionals = [
            'kompensasi_jr' => $additionalsData->get('kompensasi_jr'),
            'penyesuaian_bbm' => $additionalsData->get('penyesuaian_bbm'),
            'achievement' => $additionalsData->get('achievement'),
        ];

        // dd($additionals);
        return view('billing.transaksi.tagihan.keranjang.index', [
            'data' => $data,
            'customer' => $customer,
            'rute' => $rute,
            'rute_id' => $rute_id,
            'filter_date' => $req['filter_date'] ?? null,
            'tanggal_filter' => $req['tanggal_filter'] ?? null,
            'additionals' => $additionals,

        ]);

    }

    public function keranjang_tagihan_lanjut(Request $request, Customer $customer, CustomerBilling $billing)
    {
        $data = $request->validate([
            'penyesuaian' => 'required',
            'penalty' => 'required',
            'penalty_akhir' => 'required',
            'tanggal_hardcopy' => 'required',
            'estimasi_pembayaran' => 'required',
            'no_resi' => 'required',
            'no_validasi' => 'required',
            'ppn_dipungut' => 'required',
        ]);
        $data['tanggal_hardcopy'] = Carbon::createFromFormat('d-m-Y', $data['tanggal_hardcopy'])->format('Y-m-d');
        $data['estimasi_pembayaran'] = Carbon::createFromFormat('d-m-Y', $data['estimasi_pembayaran'])->format('Y-m-d');
        foreach (['penyesuaian', 'penalty', 'penalty_akhir'] as $field) {
            $data[$field] = str_replace('.', '', $data[$field]);
        }
        $data['ppn_dipungut'] = (int) $data['ppn_dipungut'];

        try {
            $billing->checkout($customer, $data);
        } catch (\Throwable $th) {
            return redirect()->route('transaksi.nota-tagihan.keranjang', $customer)
                ->withInput()
                ->with('error', 'Terdapat kesalahan!! '.($th instanceof ValidationException ? collect($th->errors())->flatten()->first() : $th->getMessage()));
        }

        return redirect()->route('transaksi.nota-tagihan', $customer)->with('success', 'Berhasil menyimpan data!!');
    }

    public function keranjang_tagihan_export(Customer $customer, Request $request)
    {
        $req = $request->validate([
            'rute_id' => 'nullable|exists:rutes,id',
            'filter_date' => 'nullable|required_if:tanggal_filter,!=, null|in:tanggal_muat,tanggal_bongkar,tanggal',
            'tanggal_filter' => 'nullable|required_if:filter_date,tanggal_muat,tanggal_bongkar,tanggal',
        ]);

        $rute_id = $req['rute_id'] ?? null;
        $filter_date = $req['filter_date'] ?? null;
        $tanggal_filter = $req['tanggal_filter'] ?? null;

        $data = Transaksi::getKeranjangTagihanData($customer->id, $rute_id, $filter_date, $tanggal_filter);

        // get latest data from month before current month
        // dd($bulan);
        if (Auth::user()->role == 'admin') {
            $pdf = Pdf::loadview('billing.transaksi.tagihan.export-admin', [
                'data' => $data,
                'customer' => $customer,
            ])->setPaper('a4', 'landscape');
        } else {
            $pdf = Pdf::loadview('billing.transaksi.tagihan.export', [
                'data' => $data,
                'customer' => $customer,
            ])->setPaper('a4', 'landscape');
        }

        return $pdf->stream('Nota Tagihan '.$customer->singkatan.'.pdf');
    }

    public function keranjang_tagihan_delete(Customer $customer, Transaksi $transaksi, CustomerBilling $billing)
    {
        try {
            $billing->removeFromCart($customer, $transaksi->id);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menghapus data: '.$e->getMessage());
        }

        return redirect()->route('transaksi.nota-tagihan.keranjang', $customer)
            ->with('success', 'Berhasil mengeluarkan data dari keranjang!!');
    }

    public function keranjang_tagihan_detail(Customer $customer, InvoiceAdditional $invoiceAdditional)
    {
        $data = $invoiceAdditional->load('details.transaksi');

        $stringJenis = TransaksiAdditional::JENIS[$invoiceAdditional->jenis] ?? $invoiceAdditional->jenis;

        return view('billing.transaksi.tagihan.keranjang.detail-komponen', [
            'customer' => $customer,
            'data' => $data,
            'stringJenis' => $stringJenis,
            'jenis' => $invoiceAdditional->jenis,
        ]);
    }

    public function keranjang_tagihan_detail_back(Customer $customer, InvoiceAdditional $invoiceAdditional)
    {
        $stringJenis = TransaksiAdditional::JENIS[$invoiceAdditional->jenis] ?? $invoiceAdditional->jenis;

        // OPTIMASI 1: Gunakan exists() karena kita hanya butuh nilai boolean (true/false)
        $hasPendingCart = InvoiceAdditional::where('jenis', $invoiceAdditional->jenis)
            ->where('customer_id', $customer->id)
            ->where('status', 0)
            ->where('is_finished', 0)
            ->exists();

        if ($hasPendingCart) {
            return redirect()->back()->with('error', 'Terdapat data keranjang pada '.$stringJenis.'. Silahkan hapus keranjang tersebut terlebih dahulu!!');
        }

        // Load relasi detail
        $invoiceAdditional->load('details');
        $dataIds = $invoiceAdditional->details->pluck('transaksi_additional_id')->toArray();

        // OPTIMASI 2: Gunakan Transaction agar jika salah satu gagal, semua di-rollback (dibatalkan)
        try {
            DB::beginTransaction();

            $invoiceAdditional->update([
                'status' => 0,
            ]);

            // OPTIMASI 3: Hanya jalankan query update jika array $dataIds tidak kosong
            if (! empty($dataIds)) {
                TransaksiAdditional::whereIn('id', $dataIds)->update([
                    'status' => 1,
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat memproses data: '.$th->getMessage());
        }

        return redirect()->route('transaksi.nota-tagihan.keranjang', ['customer' => $customer])->with('success', 'Berhasil mengembalikan data '.$stringJenis.' ke keranjang!!');
    }

    public function invoice_tagihan_detail_export(InvoiceTagihan $invoice, Customer $customer)
    {
        $data = $invoice->invoice_tagihan_detail;

        // get latest data from month before current month
        // dd($bulan);
        $pdf = Pdf::loadview('billing.transaksi.tagihan.export-detail', [
            'data' => $data,
            'invoice' => $invoice,
            'invoice_id' => $invoice->id,
            'customer' => $customer,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Invoice Tagihan '.$invoice->customer->singkatan.'.pdf');
    }

    public function nota_bayar(Vendor $vendor)
    {
        if (Auth::user()->isVendorOwner() && ($vendor->id !== Auth::user()->vendor_id)) {
            return redirect()->back()->with('error', 'Anda tidak punya wewenang untuk melihat vendor ini!!');
        }

        $cartTransaksiIds = KeranjangNotaBayar::where('user_id', Auth::id())
            ->where('vendor_id', $vendor->id)
            ->pluck('transaksi_id')
            ->toArray();

        $cartCount = count($cartTransaksiIds);

        $dataTersedia = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
            ->select('transaksis.*')
            ->where('transaksis.status', 3)
            ->where('transaksis.void', 0)
            ->where('transaksis.bayar', 0)
            ->where('kuj.vendor_id', $vendor->id)
            ->when(! empty($cartTransaksiIds), function ($query) use ($cartTransaksiIds) {
                return $query->whereNotIn('transaksis.id', $cartTransaksiIds);
            })
            ->get();

        return view('billing.transaksi.bayar.index', [
            'dataTersedia' => $dataTersedia,
            'vendor' => $vendor,
            'cartCount' => $cartCount,
        ]);
    }

    // Halaman Khusus Keranjang Nota Bayar
    public function nota_bayar_keranjang(Vendor $vendor, VendorPaymentCart $paymentCart)
    {
        if (Auth::user()->isVendorOwner() && ($vendor->id !== Auth::user()->vendor_id)) {
            return redirect()->back()->with('error', 'Anda tidak punya wewenang untuk melihat vendor ini!!');
        }

        $cartTransaksiIds = KeranjangNotaBayar::where('user_id', Auth::id())
            ->where('vendor_id', $vendor->id)
            ->pluck('transaksi_id')
            ->toArray();

        // PROTEKSI: Cegah akses jika keranjang kosong
        if (empty($cartTransaksiIds)) {
            return redirect()->route('transaksi.nota-bayar', $vendor)
                ->with('error', 'Keranjang masih kosong! Silakan pilih transaksi terlebih dahulu.');
        }

        $dataKeranjang = $paymentCart->notes(Auth::user(), $vendor);

        $hariJatuhTempo = $vendor->jatuh_tempo_hari ?? 0;
        $defaultJatuhTempo = Carbon::now()->addDays($hariJatuhTempo)->format('Y-m-d');

        return view('billing.transaksi.bayar.keranjang', [
            'data' => $dataKeranjang,
            'quote' => $paymentCart->quote($vendor, $dataKeranjang),
            'vendor' => $vendor,
            'defaultJatuhTempo' => $defaultJatuhTempo,
            'hariJatuhTempo' => $hariJatuhTempo,
        ]);
    }

    public function nota_bayar_masuk_keranjang(Request $request, Vendor $vendor, VendorPaymentCart $paymentCart): RedirectResponse
    {
        $data = $request->validate([
            'transaksi_ids' => ['required', 'array'],
            'transaksi_ids.*' => ['required', 'integer'],
        ]);

        try {
            $count = $paymentCart->add($request->user(), $vendor, $data['transaksi_ids']);
        } catch (ValidationException $exception) {
            return redirect()->back()->with('error', $exception->validator->errors()->first());
        }

        return redirect()->route('transaksi.nota-bayar', $vendor)->with('success', $count.' transaksi berhasil dimasukkan ke keranjang.');
    }

    public function nota_bayar_keranjang_semua(Vendor $vendor, VendorPaymentCart $paymentCart): RedirectResponse
    {
        $paymentCart->add(Auth::user(), $vendor);

        return redirect()->route('transaksi.nota-bayar.keranjang', $vendor)->with('success', 'Semua transaksi berhasil dimasukkan ke keranjang.');
    }

    public function nota_bayar_keluar_keranjang(Request $request, Vendor $vendor, VendorPaymentCart $paymentCart): RedirectResponse
    {
        $data = $request->validate([
            'transaksi_ids' => ['required', 'array'],
            'transaksi_ids.*' => ['required', 'integer'],
        ]);

        $paymentCart->remove($request->user(), $vendor, $data['transaksi_ids']);

        return redirect()->back()->with('success', 'Transaksi berhasil dikeluarkan dari keranjang.');
    }

    public function nota_bayar_kosongkan_keranjang(Vendor $vendor, VendorPaymentCart $paymentCart): RedirectResponse
    {
        $paymentCart->remove(Auth::user(), $vendor);

        return redirect()->route('transaksi.nota-bayar', $vendor)->with('success', 'Keranjang berhasil dikosongkan.');
    }

    public function nota_bayar_lanjut(Request $request, Vendor $vendor, VendorPaymentCart $paymentCart): RedirectResponse
    {
        $data = $request->validate([
            'tempo' => ['required', 'date'],
            'fingerprint' => ['required', 'string', 'size:64'],
        ]);

        try {
            $paymentCart->checkout($request->user(), $vendor, $data['tempo'], $data['fingerprint']);
        } catch (ValidationException $exception) {
            return redirect()->back()->with('error', $exception->validator->errors()->first());
        }

        return redirect()->route('transaksi.nota-bayar', $vendor)->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_bonus(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $db = new Transaksi;

        $dataTahun = $db->dataTahun();

        $sponsorId = $request->sponsor_id;
        $sponsor = Sponsor::find($sponsorId);
        $data = $db->notaBonus($sponsorId, $bulan, $tahun);

        return view('billing.transaksi.bonus.index', [
            'data' => $data,
            'sponsor' => $sponsor,
            'bulan' => $bulan,
            'dataTahun' => $dataTahun,
            'tahun' => $tahun,
        ]);
    }

    public function nota_bonus_lanjut(Request $request, Sponsor $sponsor)
    {
        $data = $request->validate([
            'total_bonus' => 'required|numeric',
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:1901,2155'],
        ]);

        $db = new Transaksi;

        $bonus = $db->getIdNotaBonus($sponsor->id, $data['bulan'], $data['tahun']);

        $period = ['sponsor_id' => $sponsor->id, 'bulan' => $data['bulan'], 'tahun' => $data['tahun']];

        if ($bonus->isEmpty()) {
            return redirect()->route('transaksi.nota-bonus', $period)->with('error', 'Tidak ada transaksi bonus yang belum diproses pada periode ini.');
        }

        unset($data['bulan']);
        unset($data['tahun']);

        $data['tanggal'] = date('Y-m-d');
        // no_invoice from invoice tagihan where customer_id = $customer->id and max no_invoice
        $data['no_invoice'] = InvoiceBonus::where('sponsor_id', $sponsor->id)->max('no_invoice') + 1;
        $data['sponsor_id'] = $sponsor->id;
        $data['total_bayar'] = 0;
        $data['sisa_bonus'] = $data['total_bonus'];
        $data['lunas'] = 0;
        $data['periode'] = 'Periode '.$data['no_invoice'];

        DB::beginTransaction();

        $invoice = InvoiceBonus::create($data);

        foreach ($bonus as $key => $value) {
            $value->update([
                'bonus' => 1,
            ]);

            InvoiceBonusDetail::create([
                'invoice_bonus_id' => $invoice->id,
                'transaksi_id' => $value->id,
            ]);

        }

        DB::commit();

        return redirect()->route('transaksi.nota-bonus', $period)->with('success', 'Nota bonus berhasil disimpan.');
    }

    public function nota_csr(Request $request)
    {

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $db = new Transaksi;

        $dataTahun = $db->dataTahun();

        $customerId = $request->customer_id;

        $data = $db->getNotaCsrNew($customerId, $bulan, $tahun);

        return view('billing.transaksi.csr.index', [
            'data' => $data,
            'customer' => Customer::find($customerId),
            'bulan' => $bulan,
            'dataTahun' => $dataTahun,
            'tahun' => $tahun,
        ]);

    }

    public function nota_csr_lanjut(Request $request)
    {
        $data = $request->validate([
            'total_csr' => 'required',
            'customer_id' => 'required|exists:customers,id',
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        $csr = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
            ->where('kuj.customer_id', $data['customer_id'])
            ->whereMonth('transaksis.tanggal_bongkar', $data['bulan'])
            ->whereYear('transaksis.tanggal_bongkar', $data['tahun'])
            ->where('transaksis.status', 3)
            ->where('transaksis.void', 0)
            ->where('csr', 0)
            ->where('nominal_csr', '>', 0)
            ->select('transaksis.id')
            ->get();

        $d['tanggal'] = date('Y-m-d');
        $d['no_invoice'] = InvoiceCsr::where('customer_id', $data['customer_id'])->max('no_invoice') + 1;
        $d['customer_id'] = $data['customer_id'];
        $d['total_csr'] = $data['total_csr'];
        $d['periode'] = 'Periode '.$d['no_invoice'];
        $d['lunas'] = 0;

        $invoice = InvoiceCsr::create($d);

        foreach ($csr as $c) {
            $c->update([
                'csr' => 1,
            ]);

            InvoiceCsrDetail::create([
                'invoice_csr_id' => $invoice->id,
                'transaksi_id' => $c->id,
            ]);
        }

        return redirect()->route('billing.transaksi.index')->with('success', 'Berhasil menyimpan data!!');
    }

    public function sales_order(Request $request) {}
}
