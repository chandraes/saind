<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    // has many rute through CustomerRute
    public function rute()
    {
        return $this->belongsToMany(Rute::class, 'customer_rute', 'customer_id', 'rute_id');
    }

    public function document()
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function customer_tagihan()
    {
        return $this->hasMany(CustomerTagihan::class);
    }

    public function tagihanInvoice()
    {
        $customer = $this->where('status', 1)->get();

        $data = [];

        foreach ($customer as $c) {
            $transaksi = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                ->where('kuj.customer_id', $c->id)
                ->where('transaksis.status', 3)
                ->where('transaksis.void', 0)
                ->where('transaksis.tagihan', 0)
                ->select('transaksis.*')
                ->first();

            $invoice = InvoiceTagihan::where('customer_id', $c->id)
                ->where('lunas', 0)
                ->first();

            if ($transaksi || $invoice) {
                $customerData = [
                    'customer' => $c->singkatan,
                    'total_transaksi' => 0,
                    'tanggal_awal' => null,
                    'tanggal_akhir' => null,
                    'invoices' => [],
                    'total_invoice' => 0,
                ];

                if ($transaksi) {
                    $transaksiRecords = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                        ->where('kuj.customer_id', $c->id)
                        ->where('transaksis.status', 3)
                        ->where('transaksis.void', 0)
                        ->where('transaksis.tagihan', 0)
                        ->select('transaksis.*', 'kuj.tanggal as tanggal_kuj')
                        ->orderBy('transaksis.id')
                        ->get();

                    $customerData['total_transaksi'] = $transaksiRecords->sum('nominal_tagihan') * 0.98;

                    if ($transaksiRecords->isNotEmpty()) {
                        $tAwal = $transaksiRecords->first();
                        $tAkhir = $transaksiRecords->last();

                        $customerData['tanggal_awal'] = Carbon::parse($tAwal->tanggal_kuj)->translatedFormat('d-m-Y');
                        $customerData['tanggal_akhir'] = Carbon::parse($tAkhir->tanggal_kuj)->translatedFormat('d-m-Y');
                    }
                }

                if ($invoice) {
                    $inv = InvoiceTagihan::where('customer_id', $c->id)
                        ->where('lunas', 0)
                        ->orderBy('no_invoice', 'asc')
                        ->get();

                    foreach ($inv as $i) {
                        $customerData['invoices'][] = [
                            'periode' => $i->no_invoice,
                            'tanggal_submit_softcopy' => Carbon::parse($i->tanggal)->translatedFormat('d-m-Y'),
                            'tanggal_hardcopy' => $i->tanggal_hardcopy ? Carbon::parse($i->tanggal_hardcopy)->translatedFormat('d-m-Y') : '-',
                            'estimasi_pembayaran' => $i->estimasi_pembayaran ? Carbon::parse($i->estimasi_pembayaran)->translatedFormat('d-m-Y') : '-',
                            'penyesuaian' => $i->penyesuaian,
                            'penalty' => $i->penalty + $i->pinalty_akhir,
                            'ppn' => $i->ppn,
                            'pph' => $i->pph,
                            'tagihan_awal' => $i->total_awal,
                            'total_tagihan' => $i->total_tagihan,
                            'no_resi' => $i->no_resi,
                            'no_validasi' => $i->no_validasi,
                        ];
                    }

                    $customerData['total_invoice'] = $inv->sum('total_tagihan');
                }

                $data[] = $customerData;
            }
        }

        return $data;
    }
}
