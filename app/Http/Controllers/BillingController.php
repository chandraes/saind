<?php

namespace App\Http\Controllers;

use App\Models\CostOperational;
use App\Models\Customer;
use App\Models\db\Kreditor;
use App\Models\GroupWa;
use App\Models\InvoiceBayar;
use App\Models\InvoiceBonus;
use App\Models\InvoiceCsr;
use App\Models\InvoiceTagihan;
use App\Models\KasBesar;
use App\Models\RekapGaji;
use App\Models\Rekening;
use App\Models\Sponsor;
use App\Models\Transaksi;
use App\Models\Vendor;
use Illuminate\Http\Request;

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
}
