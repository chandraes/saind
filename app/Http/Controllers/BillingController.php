<?php

namespace App\Http\Controllers;

use App\Models\CostOperational;
use App\Models\Customer;
use App\Models\db\Kreditor;
use App\Models\GroupWa;
use App\Models\InvoiceAdditional;
use App\Models\InvoiceAddVendor;
use App\Models\InvoiceBayar;
use App\Models\InvoiceBonus;
use App\Models\InvoiceCsr;
use App\Models\InvoiceTagihan;
use App\Models\KasBesar;
use App\Models\RekapGaji;
use App\Models\Rekening;
use App\Models\Sponsor;
use App\Models\Transaksi;
use App\Models\TransaksiAdditional;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index()
    {
        $check = RekapGaji::orderBy('id', 'desc')->first();

        if (!$check) {
            $bulan = date('m');
            $tahun = date('Y');
        } else {
            $bulan = $check->bulan + 1 == 13 ? 1 : $check->bulan + 1;
            $tahun = $check->bulan + 1 == 13 ? $check->tahun + 1 : $check->tahun;
        }

        $customer = Customer::all();

        $invoice = InvoiceTagihan::where('lunas', 0)->count();
        $bayar = InvoiceBayar::where('lunas', 0)->count();
        $bonus = InvoiceBonus::where('lunas', 0)->count();
        $invoice_csr = InvoiceCsr::where('lunas', 0)->count();

        // $data = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
        //         ->leftJoin('vehicles as v', 'kuj.vehicle_id', 'v.id')
        //         ->select('transaksis.*', 'kuj.customer_id as customer_id', 'v.vendor_id as vendor_id')
        //         ->where('transaksis.void', 0)->get();

        // $vendor = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
        //                 ->where('status', 3)
        //                 ->where('transaksis.bayar', 0)
        //                 ->where('transaksis.void', 0)
        //                 ->get()->unique('vendor_id');

        $vendor = Vendor::select('id','nama')->where('status', 'aktif')->get();

        $sponsor = Sponsor::select('nama', 'id')->get();

        // $sponsor = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
        //                 ->join('vendors as v', 'kuj.vendor_id', 'v.id')
        //                 ->join('sponsors as s', 'v.sponsor_id', 's.id')
        //                 ->where('transaksis.bonus', 0)
        //                 ->where('transaksis.status', 3)
        //                 ->where('transaksis.void', 0)
        //                 ->get()->unique('sponsor_id');

        // $csr = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
        //                 ->join('customers as c', 'kuj.customer_id', 'c.id')
        //                 ->where('transaksis.csr', 0)
        //                 ->where('transaksis.status', 3)
        //                 ->where('transaksis.void', 0)
        //                 ->where('c.csr', 1)
        //                 ->get()->unique('customer_id');

        return view('billing.index',
        [
            'bulan' => $bulan,
            'tahun' => $tahun,
            // 'data' => $data,
            'customer' => $customer,
            'vendor' => $vendor,
            'sponsor' => $sponsor,
            'invoice' => $invoice,
            'bayar' => $bayar,
            'bonus' => $bonus,
            // 'csr' => $csr,
            'invoice_csr' => $invoice_csr,
        ]);
    }

    public function form_cost_operational()
    {
        $check = RekapGaji::orderBy('id', 'desc')->first();

        $bulan = $check->bulan + 1 == 13 ? 1 : $check->bulan + 1;
        $tahun = $check->bulan + 1 == 13 ? $check->tahun + 1 : $check->tahun;

        return view('billing.form-cost-operational.index',
            [
                'bulan' => $bulan,
                'tahun' => $tahun,
            ]);
    }

    public function cost_operational()
    {
        $data = CostOperational::all();

        if($data->isEmpty()) {
            return redirect()->route('database.cost-operational')->with('error', 'Data cost operational kosong, silahkan tambahkan data cost operational terlebih dahulu');
        }

        return view('billing.form-cost-operational.form-operational.index', [
            'data' => $data,
        ]);
    }

    public function cost_operational_store(Request $request)
    {
        $data = $request->validate([
                    'nominal_transaksi' => 'required',
                    'cost_operational_id' => 'required|exists:cost_operationals,id',
                    'transfer_ke' => 'required',
                    'no_rekening' => 'required',
                    'bank' => 'required',
                ]);


        $db = new KasBesar();

        $res = $db->cost_operational($data);

        return redirect()->route('billing.form-cost-operational')->with($res['status'], $res['message']);

    }

    public function cost_operational_masuk()
    {
        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        return view('billing.form-cost-operational.form-operational.masuk', [
            'rekening' => $rekening,
        ]);
    }

    public function cost_operational_masuk_store(Request $request)
    {
        $data = $request->validate([
            'uraian' => 'required',
            'nominal_transaksi' => 'required',
        ]);

        $db = new KasBesar();

        $res = $db->cost_operational_masuk($data);

        return redirect()->route('billing.form-cost-operational')->with($res['status'], $res['message']);
    }

    public function bunga_investor(Request $request)
    {

        $kreditor = Kreditor::where('is_active', 1)->get();

        if($kreditor->isEmpty()) {
            return redirect()->route('database.kreditor')->with('error', 'Data kreditor kosong, silahkan tambahkan data kreditor terlebih dahulu');
        }
        $db = new KasBesar();
        $modal = $db->modalInvestorTerakhir() < 0 ? $db->modalInvestorTerakhir() * -1 : 0;

        return view('billing.form-bunga-investor.index', [
            'kreditor' => $kreditor,
            'modal' => $modal,
        ]);
    }

    public function bunga_investor_store(Request $request)
    {
        $data = $request->validate([
            'kreditor_id' => 'required|exists:kreditors,id',
            'nominal_transaksi' => 'required',
            'transfer_ke' => 'required',
            'no_rekening' => 'required',
            'bank' => 'required',
        ]);

        $db = new KasBesar();

        $res = $db->bunga_investor($data);

        return redirect()->route('billing.index')->with($res['status'], $res['message']);

    }

    public function form_setor_pph_masuk()
    {
        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        return view('billing.form-setor-pph.masuk', [
            'rekening' => $rekening,
        ]);
    }

    public function form_setor_pph_masuk_store(Request $request)
    {
        $data = $request->validate([
            'uraian' => 'required',
            'nominal_transaksi' => 'required',
        ]);

        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $data['no_rekening'] = $rekening->nomor_rekening;
        $data['bank'] = $rekening->nama_bank;

        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['jenis_transaksi_id'] = 1;
        $data['tanggal'] = date('Y-m-d');
        $data['form_pph'] = 1;

         // Saldo terakhir
        $last = KasBesar::latest()->orderBy('id', 'desc')->first();
        if($last == null){
            $data['modal_investor_terakhir']= 0;
            $data['saldo'] = $data['nominal_transaksi'];
        }else{
            $data['saldo'] = $last->saldo + $data['nominal_transaksi'];
            $data['modal_investor_terakhir']= $last->modal_investor_terakhir;
        }

        $store = KasBesar::create($data);

         // check if store success
         if(!$store){
            return redirect()->back()->with('error', 'Data gagal disimpan');
        }

        $dbWa = new GroupWa();

        $group = $dbWa->where('untuk', 'kas-besar')->first();
        $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                "*Form Setor PPh (Dana Masuk)*\n".
                 "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                 "Uraian :  ".$data['uraian']."\n".
                 "Nilai :  *Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.')."*\n\n".
                 "Ditransfer ke rek:\n\n".
                "Bank     : ".$data['bank']."\n".
                "Nama    : ".$data['transfer_ke']."\n".
                "No. Rek : ".$data['no_rekening']."\n\n".
                "==========================\n".
                "Sisa Saldo Kas Besar : \n".
                "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                "Total Modal Investor : \n".
                "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                "Terima kasih 🙏🙏🙏\n";

        $send = $dbWa->sendWa($group->nama_group, $pesan);

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');
    }

    public function form_setor_pph_keluar()
    {
        return view('billing.form-setor-pph.keluar');
    }

    public function form_setor_pph_keluar_store(Request $request)
    {
        $data = $request->validate([
            'uraian' => 'required',
            'nominal_transaksi' => 'required',
            'transfer_ke' => 'required',
            'bank' => 'required',
            'no_rekening' => 'required',
        ]);

        $data['transfer_ke'] = substr($data['transfer_ke'], 0, 15);

        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['jenis_transaksi_id'] = 2;
        $data['tanggal'] = date('Y-m-d');

        $data['form_pph'] = 1;

         // Saldo terakhir
        $last = KasBesar::latest()->orderBy('id', 'desc')->first();

        if($last == null){
            $data['modal_investor_terakhir']= 0;
            $data['saldo'] = $data['nominal_transaksi'];
        }else{

            if ($last->saldo < $data['nominal_transaksi']) {
                return redirect()->back()->with('error', 'Saldo tidak cukup');
            }

            $data['saldo'] = $last->saldo - $data['nominal_transaksi'];
            $data['modal_investor_terakhir']= $last->modal_investor_terakhir;
        }

        $store = KasBesar::create($data);

         // check if store success
         if(!$store){
            return redirect()->back()->with('error', 'Data gagal disimpan');
        }

        $dbWa = new GroupWa();

        $group = $dbWa->where('untuk', 'kas-besar')->first();
        $pesan ="🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                "*Form Setor PPh (Dana Keluar)*\n".
                 "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                 "Uraian :  ".$data['uraian']."\n".
                 "Nilai :  *Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.')."*\n\n".
                 "Ditransfer ke rek:\n\n".
                "Bank     : ".$data['bank']."\n".
                "Nama    : ".$data['transfer_ke']."\n".
                "No. Rek : ".$data['no_rekening']."\n\n".
                "==========================\n".
                "Sisa Saldo Kas Besar : \n".
                "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                "Total Modal Investor : \n".
                "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                "Terima kasih 🙏🙏🙏\n";

        $send = $dbWa->sendWa($group->nama_group, $pesan);

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');
    }

    public function nota_tagihan(Customer $customer)
    {
        // Optimasi: Hanya lakukan 1 query ringan ke database (agregasi)
        // Pluck akan menghasilkan array seperti: ['kompensasi_jr' => 5, 'penyesuaian_bbm' => 0]
        $counts = TransaksiAdditional::where('customer_id', $customer->id)
            ->where('status', '<', 2)
            ->selectRaw('jenis, count(*) as total')
            ->groupBy('jenis')
            ->pluck('total', 'jenis');

        return view('billing.nota-tagihan.index', [
            'customer' => $customer,
            'kompensasi_jr' => $customer->is_kompensasi_jr ? ($counts['kompensasi_jr'] ?? 0) : null,
            'penyesuaian_bbm' => $customer->is_penyesuaian_bbm ? ($counts['penyesuaian_bbm'] ?? 0) : null,
            'achievement' => $customer->is_achievement ? ($counts['achievement'] ?? 0) : null,
        ]);
    }

    public function nota_tagihan_detail_by_jenis(Request $request, Customer $customer, $jenis)
    {
        $req = $request->validate([
            'rute_id' => 'nullable|exists:rutes,id',
            'filter_date' => 'nullable|required_if:tanggal_filter,!=, null|in:tanggal_muat,tanggal_bongkar,tanggal',
            'tanggal_filter' => 'nullable|required_if:filter_date,tanggal_muat,tanggal_bongkar,tanggal',
        ]);

        $rute_id = $req['rute_id'] ?? null;
        $filter_date = $req['filter_date'] ?? null;
        $tanggal_filter = $req['tanggal_filter'] ?? null;

        /** @var \Illuminate\Routing\UrlGenerator */
        $url = url();

        // Store current URL in session
        session(['previous_url' => $url->full()]);

        $rute = $customer->rute;
        $db = new TransaksiAdditional;
        $dbTransaksi = new Transaksi;

        $pendingTransaksiIds = $db->select('transaksi_id')->where('customer_id', $customer->id)->where('jenis', $jenis)->where('status', 0)->get()->toArray();
        // $keranjangTransaksiIds = $db->select('transaksi_id')->where('jenis', $jenis)->where('status', 1)->get()->toArray();
        $keranjang = $db->with(['transaksi'])
            ->where('customer_id', $customer->id)
            ->where('jenis', $jenis)
            ->where('status', 1)
            ->count();

        $data = $dbTransaksi->getTransaksiAdditionals($customer->id, $pendingTransaksiIds, $rute_id, $filter_date, $tanggal_filter);

        // $keranjang = Transaksi::getKeranjangTagihanData($customer->id)->count();
        // $keranjang = $dbTransaksi->getTransaksiAdditionals($customer->id, $keranjangTransaksiIds, $rute_id, $filter_date, $tanggal_filter);
        // dd($data);

        $stringJenis = TransaksiAdditional::JENIS[$jenis] ?? $jenis;

        return view('billing.nota-tagihan.detail', [
            'jenis' => $jenis,
            'stringJenis' => $stringJenis,
            'data' => $data,
            'pendingTransaksiIds' => $pendingTransaksiIds,
            'customer' => $customer,
            'rute' => $rute,
            'rute_id' => $rute_id,
            'filter_date' => $req['filter_date'] ?? null,
            'tanggal_filter' => $req['tanggal_filter'] ?? null,
            'keranjang' => $keranjang,
        ]);
    }

    public function nota_tagihan_detail_by_jenis_lanjut(Request $request, Customer $customer, $jenis)
    {
        $req = $request->validate([
            'dpp' => 'required',
        ]);

        // 1. Sanitasi input DPP & Penentuan kolom muatan
        $dpp = (float) str_replace('.', '', $req['dpp']);
        $tagihan_dari = $customer->tagihan_dari == 1 ? 'tonase' : 'timbangan_bongkar';

        // 2. Eager Loading untuk efisiensi
        $rekapJenis = TransaksiAdditional::with('transaksi')
            ->where('jenis', $jenis)
            ->where('customer_id', $customer->id)
            ->where('status', 0)
            ->get();

        if ($rekapJenis->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data transaksi yang tersedia.');
        }

        // 3. Kalkulasi Total (dilakukan sebelum transaksi agar DB tidak terkunci terlalu lama)
        $totalKeseluruhan = $rekapJenis->groupBy('rute_id')->reduce(function ($carry, $group) use ($tagihan_dari, $dpp) {
            $jarak = $group->first()->jarak ?? 0;
            $sumMuatan = $group->sum(fn($item) => $item->transaksi->{$tagihan_dari} ?? 0);
            return $carry + ($jarak * $sumMuatan * $dpp);
        }, 0);

        $totalKeseluruhan = round($totalKeseluruhan);

        // 4. Mulai Transaksi Database
        DB::beginTransaction();

        try {
            $invoice = InvoiceAdditional::where('customer_id', $customer->id)
                ->where('jenis', $jenis)
                ->where('status', 0)
                ->lockForUpdate() // Mencegah race condition
                ->first();

            if ($invoice) {
                // Validasi kecocokan DPP
                if ((float)$invoice->dpp !== $dpp) {
                    // Lempar exception agar masuk ke blok catch (otomatis rollback)
                    throw new \Exception('DPP berbeda dengan yang sudah ada di keranjang. Silahkan gunakan DPP yang sama / Selesaikan Transaksi Sebelumnya.');
                }
                $invoice->increment('nominal', $totalKeseluruhan);
            } else {
                // Buat Invoice baru
                $invoice = InvoiceAdditional::create([
                    'customer_id' => $customer->id,
                    'jenis'       => $jenis,
                    'nominal'     => $totalKeseluruhan,
                    'dpp'         => $dpp,
                    'status'      => 0,
                    'is_finished' => false,
                ]);
            }

            // 5. Simpan Detail Invoice (DRY: Cukup satu kali panggil)
            $invoice->details()->createMany($rekapJenis->map(fn($item) => [
                'transaksi_additional_id' => $item->id,
                'transaksi_id'            => $item->transaksi_id,
                'jenis'                   => $item->jenis,
            ])->toArray());

            // 6. Update Status Transaksi Additional
            TransaksiAdditional::whereIn('id', $rekapJenis->pluck('id'))->update(['status' => 1]);

            DB::commit();

            return redirect()->back()->with('success', 'Perhitungan berhasil disimpan. Total: Rp ' . number_format($totalKeseluruhan, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();

            // Log error jika diperlukan untuk debugging dev
            // Log::error("Gagal menyimpan nota tagihan detail: " . $e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function nota_tagihan_detail_by_jenis_keranjang(Customer $customer, $jenis)
    {
        $db = new TransaksiAdditional;

        $data = $db->with(['transaksi'])
            ->where('customer_id', $customer->id)
            ->where('jenis', $jenis)
            ->where('status', 1)
            ->get();

        $invoice = InvoiceAdditional::where('customer_id', $customer->id)
            ->where('jenis', $jenis)
            ->where('status', 0)
            ->first();

        if (!$invoice) {
            return redirect()->back()->with('error', 'Tidak ada data di keranjang untuk jenis ini. Silahkan tambahkan transaksi terlebih dahulu.');
        }

        $tagihan_dari = $customer->tagihan_dari == 1 ? 'tonase' : 'timbangan_bongkar';
        $dpp = $invoice->dpp;
        
        $ruteGrouped = $data->groupBy(function($item) {
            return $item->rute->nama ?? 'Rute Tidak Ditemukan';
        })->map(function ($group) use ($tagihan_dari, $dpp) {
            // Ambil jarak dari record pertama di group rute tersebut
            $jarak = $group->first()->jarak ?? 0;
            $totalMuatan = $group->sum(fn($item) => $item->transaksi->{$tagihan_dari} ?? 0);
            $subtotal = $jarak * $totalMuatan * $dpp;

            return [
                'jarak' => $jarak,
                'total_muatan' => $totalMuatan,
                'dpp' => $dpp,
                'subtotal' => $subtotal,
            ];
        });


        $stringJenis = TransaksiAdditional::JENIS[$jenis] ?? $jenis;

        return view('billing.nota-tagihan.keranjang', [
            'data' => $data,
            'customer' => $customer,
            'jenis' => $jenis,
            'stringJenis' => $stringJenis,
            'invoice' => $invoice,
            'ruteGrouped' => $ruteGrouped,
        ]);
    }

    public function nota_tagihan_detail_by_jenis_keranjang_lanjut(Customer $customer, $jenis, InvoiceAdditional $invoice)
    {
        // Validasi bahwa invoice yang dimaksud benar-benar milik customer dan jenis yang sesuai
        if ($invoice->customer_id !== $customer->id || $invoice->jenis !== $jenis || $invoice->status !== 0) {
            return redirect()->back()->with('error', 'Invoice tidak valid untuk keranjang ini.');
        }

        $check = InvoiceAdditional::where('customer_id', $customer->id)
            ->where('jenis', $jenis)
            ->where('status', 1) // Pastikan ada transaksi di keranjang
            ->where('is_finished', false) // Pastikan belum selesai
            ->exists();

        if ($check) {
            return redirect()->back()->with('error', 'Terdapat komponen yang sudah di cut off dan belum di selesaikan. Silahkan selesaikan transaksi di keranjang HPP terlebih dahulu.');
        }

        $detailsId = $invoice->details()->pluck('transaksi_additional_id')->toArray();
        // Update status invoice menjadi selesai
        $invoice->update([
            'status' => 1,
        ]);

        TransaksiAdditional::whereIn('id', $detailsId)->update(['status' => 2]);

        return redirect()->route('billing.nota-tagihan.detail-jenis', ['customer' => $customer->id, 'jenis' => $jenis])->with('success', 'Transaksi berhasil diselesaikan dan dipindahkan dari keranjang.');
    }

    public function nota_tagihan_detail_by_jenis_keranjang_back(Customer $customer, $jenis, InvoiceAdditional $invoice)
    {

        $detailsId = $invoice->details()->pluck('transaksi_additional_id')->toArray();

        TransaksiAdditional::whereIn('id', $detailsId)->update(['status' => 0]);

        $invoice->delete();

        return redirect()->route('billing.nota-tagihan.detail-jenis', ['customer' => $customer->id, 'jenis' => $jenis])->with('success', 'Transaksi berhasil dikembalikan ke tahap sebelumnya.');
    }

    public function nota_bayar(Vendor $vendor)
    {

        $counts = TransaksiAdditional::where('vendor_id', $vendor->id)
            ->whereIn('status', [3,4])
            ->selectRaw('jenis, count(*) as total')
            ->groupBy('jenis')
            ->pluck('total', 'jenis');


        return view('billing.nota-bayar.index', [
            'vendor' => $vendor,
            'kompensasi_jr' => $counts['kompensasi_jr'] ?? 0,
            'penyesuaian_bbm' => $counts['penyesuaian_bbm'] ?? 0,
            'achievement' => $counts['achievement'] ?? 0,
        ]);
    }

    public function nota_bayar_detail_jenis(Request $request, Vendor $vendor, $jenis)
    {

        $db = new TransaksiAdditional;

        $keranjang = $db->with(['transaksi'])
            ->where('vendor_id', $vendor->id)
            ->where('jenis', $jenis)
            ->where('status', 4)
            ->count();

        $data = $db->with(['transaksi'])
                ->where('vendor_id', $vendor->id)
                ->where('jenis', $jenis)
                ->where('status', 3)
                ->get();


        $stringJenis = TransaksiAdditional::JENIS[$jenis] ?? $jenis;

        return view('billing.nota-bayar.detail', [
            'jenis' => $jenis,
            'stringJenis' => $stringJenis,
            'data' => $data,
            'vendor' => $vendor,
            'keranjang' => $keranjang,
        ]);
    }

    public function nota_bayar_detail_by_jenis_keranjang(Vendor $vendor, $jenis)
    {
        $db = new TransaksiAdditional;

        $data = $db->with(['transaksi.kas_uang_jalan.vendor','transaksi.kas_uang_jalan.rute','transaksi.kas_uang_jalan.vehicle', 'rute', 'customer'])
            ->where('vendor_id', $vendor->id)
            ->where('jenis', $jenis)
            ->where('status', 4)
            ->get();

        $invoice = InvoiceAddVendor::with('details')->where('vendor_id', $vendor->id)
            ->where('jenis', $jenis)
            ->where('status', 0)
            ->first();

        if (!$invoice) {
            return redirect()->back()->with('error', 'Tidak ada data di keranjang untuk jenis ini. Silahkan tambahkan transaksi terlebih dahulu.');
        }

        $dpp = $invoice->dpp;

        $groupedData = [];
        $totalKeseluruhan = 0;

        foreach ($data as $item) {
            // ... (Kode grouping Anda tetap tidak berubah sampai bagian bawah foreach)
            $customerName = $item->customer->nama ?? 'Tidak Diketahui';
            $ruteName = $item->rute->nama ?? 'Rute Lain';
            $tagihan_dari = $item->customer->tagihan_dari == 1 ? 'tonase' : 'timbangan_bongkar';
            $jarak = (float) ($item->jarak ?? 0);
            $muatan = (float) ($item->transaksi->{$tagihan_dari} ?? 0);

            if (!isset($groupedData[$customerName])) {
                $groupedData[$customerName] = [
                    'rutes' => [],
                    'subtotal_customer' => 0
                ];
            }

            if (!isset($groupedData[$customerName]['rutes'][$ruteName])) {
                $groupedData[$customerName]['rutes'][$ruteName] = [
                    'jarak' => $jarak,
                    'total_muatan' => 0,
                    'jumlah_trx' => 0
                ];
            }

            $groupedData[$customerName]['rutes'][$ruteName]['total_muatan'] += $muatan;
            $groupedData[$customerName]['rutes'][$ruteName]['jumlah_trx']++;
        }

        foreach ($groupedData as $customerName => &$customerData) {
            foreach ($customerData['rutes'] as $ruteName => &$ruteData) {
                $ruteData['subtotal'] = (int) round($ruteData['jarak'] * $ruteData['total_muatan'] * $dpp);
                $customerData['subtotal_customer'] += $ruteData['subtotal'];
            }
            $totalKeseluruhan += $customerData['subtotal_customer'];
        }
        unset($customerData, $ruteData);

        // === TAMBAHAN PERHITUNGAN PAJAK UNTUK TAMPILAN ===
        $ppn = $vendor->ppn == 1 ? (int) round($totalKeseluruhan * 0.11) : 0;
        $pph = $vendor->pph == 1 ? (int) round($totalKeseluruhan * ($vendor->pph_val / 100)) : 0;
        $totalAkhir = $totalKeseluruhan + $ppn - $pph;
        // ==================================================

        $stringJenis = TransaksiAdditional::JENIS[$jenis] ?? $jenis;

        return view('billing.nota-bayar.keranjang', [
            'data' => $data,
            'vendor' => $vendor,
            'jenis' => $jenis,
            'stringJenis' => $stringJenis,
            'invoice' => $invoice,
            'totalKeseluruhan' => $totalKeseluruhan,
            'groupedData' => $groupedData,
            // Kirim variabel baru ke blade
            'ppn' => $ppn,
            'pph' => $pph,
            'totalAkhir' => $totalAkhir,
        ]);
    }

    public function nota_bayar_detail_by_jenis_lanjut(Request $request, Vendor $vendor, $jenis)
    {
        $req = $request->validate([
            'dpp' => 'required',
        ]);

        // 1. Sanitasi input DPP
        $dpp = (float) str_replace(['.', ','], ['', '.'], $req['dpp']);

        $rekapJenis = TransaksiAdditional::with(['transaksi', 'customer'])
            ->where('jenis', $jenis)
            ->where('vendor_id', $vendor->id)
            ->where('status', 3)
            ->get();

        $rekapIds = $rekapJenis->pluck('id');

        $maxDpp = null;
        if ($rekapIds->isNotEmpty()) {
            $maxDpp = InvoiceAdditional::whereHas('details', function($query) use ($rekapIds) {
                        $query->whereIn('transaksi_additional_id', $rekapIds);
                    })->max('dpp');
        }

        // 4. Validasi DPP Input terhadap Max DPP dari Database
        if (!is_null($maxDpp)) {
            // Gunakan (float) untuk memastikan perbandingan angka presisi
            if ($dpp > (float) $maxDpp) {
                return redirect()->back()
                    ->withInput() // Agar user tidak perlu mengetik ulang nominal jika gagal
                    ->with('error', 'DPP vendor (Rp '.number_format($dpp, 0, ',', '.').') tidak boleh lebih besar dari DPP tagihan (Rp '.number_format($maxDpp, 0, ',', '.').')!');
            }
        }
        // 2. Mulai Transaksi Database LEbih AWAL (untuk lock yang efektif)
        DB::beginTransaction();

        try {
            // 3. Lock untuk mencegah race condition
            $existingInvoice = InvoiceAddVendor::where('vendor_id', $vendor->id)
                ->where('jenis', $jenis)
                ->where('status', 0)
                ->lockForUpdate()
                ->first();

            // 4. Ambil data transaksi

            if ($rekapJenis->isEmpty()) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Tidak ada data transaksi yang tersedia.');
            }

            // 5. Validasi duplikasi
            $existingDetailIds = DB::table('invoice_add_vendor_details')
                ->whereIn('transaksi_additional_id', $rekapJenis->pluck('id'))
                ->pluck('transaksi_additional_id')
                ->toArray();

            if (!empty($existingDetailIds)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Beberapa transaksi sudah ada di invoice lain.');
            }

            // 6. Kalkulasi Total DPP
            $totalKeseluruhan = $rekapJenis->sum(function ($item) use ($dpp) {
                if (!$item->customer || !$item->transaksi) return 0;

                $tagihan_dari = $item->customer->tagihan_dari == 1 ? 'tonase' : 'timbangan_bongkar';
                if (empty($tagihan_dari)) return 0;

                $jarak = (float) ($item->jarak ?? 0);
                $muatan = (float) ($item->transaksi->$tagihan_dari ?? 0);

                return $jarak * $muatan * $dpp;
            });
            $totalKeseluruhan = (int) round($totalKeseluruhan);

            // === TAMBAHAN PERHITUNGAN PAJAK ===
            $ppn = $vendor->ppn == 1 ? (int) round($totalKeseluruhan * 0.11) : 0;
            $pph = $vendor->pph == 1 ? (int) round($totalKeseluruhan * ($vendor->pph_val / 100)) : 0;
            $totalAkhir = $totalKeseluruhan + $ppn - $pph;
            // =================================

            // 7. Proses Invoice
            if ($existingInvoice) {
                if (bccomp((string) $existingInvoice->dpp, (string) $dpp, 4) !== 0) {
                    throw new \Exception('DPP berbeda dengan yang sudah ada di keranjang. Silahkan gunakan DPP yang sama atau selesaikan transaksi sebelumnya.');
                }

                // Increment DPP, PPN, dan PPH
                $existingInvoice->increment('nominal', $totalKeseluruhan);
                $existingInvoice->increment('ppn', $ppn);
                $existingInvoice->increment('pph', $pph);

                // Update Total Akhir
                $existingInvoice->update([
                    'total' => $existingInvoice->nominal + $existingInvoice->ppn - $existingInvoice->pph
                ]);

                $invoice = $existingInvoice;
            } else {
                $invoice = InvoiceAddVendor::create([
                    'vendor_id'   => $vendor->id,
                    'jenis'       => $jenis,
                    'nominal'     => $totalKeseluruhan,
                    'dpp'         => $dpp,
                    'ppn'         => $ppn,
                    'pph'         => $pph,
                    'total'       => $totalAkhir,
                    'status'      => 0,
                    'is_finished' => false,
                ]);
            }

            // 8. Insert Detail (Tidak berubah)
            $detailData = $rekapJenis->map(fn($item) => [
                'invoice_add_vendor_id'   => $invoice->id,
                'transaksi_additional_id' => $item->id,
                'transaksi_id'            => $item->transaksi_id,
                'created_at'              => now(),
                'updated_at'              => now(),
            ])->toArray();

            DB::table('invoice_add_vendor_details')->insert($detailData);

            // 9. Update Status (Tidak berubah)
            TransaksiAdditional::where('jenis', $jenis)
                ->where('vendor_id', $vendor->id)
                ->where('status', 3)
                ->update(['status' => 4]);

            DB::commit();

            return redirect()->back()->with('success', 'Perhitungan berhasil disimpan. Total: Rp ' . number_format($totalAkhir, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function nota_bayar_detail_by_jenis_keranjang_back(Vendor $vendor, $jenis, InvoiceAddVendor $invoice)
    {

        $detailsId = $invoice->details()->pluck('transaksi_additional_id')->toArray();

        TransaksiAdditional::whereIn('id', $detailsId)->update(['status' => 3]);

        $invoice->delete();

        return redirect()->route('billing.nota-bayar.detail-jenis', ['vendor' => $vendor->id, 'jenis' => $jenis])->with('success', 'Transaksi berhasil dikembalikan ke tahap sebelumnya.');
    }



    public function nota_bayar_detail_by_jenis_keranjang_lanjut(Vendor $vendor, $jenis, InvoiceAddVendor $invoice)
    {
        // Validasi bahwa invoice yang dimaksud benar-benar milik customer dan jenis yang sesuai
        if ($invoice->vendor_id !== $vendor->id || $invoice->jenis !== $jenis || $invoice->status !== 0) {
            return redirect()->back()->with('error', 'Invoice tidak valid untuk keranjang ini.');
        }

        try {
            // 2. Database Transaction: Wajib digunakan jika ada lebih dari satu operasi UPDATE/DELETE
            // Ini memastikan jika satu gagal, semua dibatalkan (mencegah data "nanggung")
            DB::beginTransaction();

            // 3. Ambil ID detail transaksi
            $detailsId = $invoice->details()->pluck('transaksi_additional_id');

            // 4. Update status Invoice (Hapus update status => 1 karena langsung ditimpa status => 5)
            $invoice->update(['status' => 1]);

            // 5. Bulk Update untuk TransaksiAdditional
            if ($detailsId->isNotEmpty()) {
                TransaksiAdditional::whereIn('id', $detailsId)->update(['status' => 5]);
            }

            DB::commit();

            return redirect()
                ->route('billing.nota-bayar.detail-jenis', ['vendor' => $vendor->id, 'jenis' => $jenis])
                ->with('success', 'Transaksi berhasil diselesaikan menjadi invoice.');

        } catch (\Exception $e) {
            // Jika terjadi error (DB mati, kolom hilang, dll), batalkan semua perubahan
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
