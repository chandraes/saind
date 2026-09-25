@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Keranjang Nota Bayar</u></h1>
            <h2><u>{{$vendor->nama}}</u></h2>
        </div>
    </div>

    @php
    $total_tagihan = $data ? $data->sum('nominal_bayar') : 0;
    $ppn = $vendor->ppn == 1 && $data ? floor($data->sum('nominal_bayar') * 0.11) : 0;
    $pph = $vendor->pph == 1 && $data ? floor($data->sum('nominal_bayar') * ($vendor->pph_val/100)) : 0;
    $total_uang_jalan = $data ? $data->sum('kas_uang_jalan.nominal_transaksi') : 0;
    $total_netto = $data ? $total_tagihan - $data->sum('kas_uang_jalan.nominal_transaksi') : 0;
    $grant_total = $total_netto - $pph + $ppn;

    $isAdminOrSu = in_array(auth()->user()->role, ['admin', 'su']);
    @endphp

    @include('swal')

    <div class="row justify-content-between align-items-center mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                    @if (auth()->user()->role != 'asisten-user' && auth()->user()->role != 'vendor')
                    <td><a href="{{route('billing.index')}}"><img src="{{asset('images/billing.svg')}}" alt="dokumen" width="30"> Billing</a></td>
                    @endif
                    <td><a href="{{route('transaksi.nota-bayar', ['vendor' => $vendor->id])}}"><img src="{{asset('images/back.svg')}}" alt="dokumen" width="30"> Tambah Transaksi</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="container-fluid mt-2">
    <div class="card border-primary mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-shopping-cart me-2"></i> Daftar Keranjang ({{ $data->count() }} Terpilih)</h5>
            @if(auth()->user()->role !== 'vendor' && $data->count() > 0)
            <form action="{{ route('transaksi.nota-bayar.kosongkan-keranjang', $vendor) }}" method="POST" id="kosongkanForm">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash me-1"></i> Kosongkan Keranjang</button>
            </form>
            @endif
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover" id="keranjangTable">
                <thead class="table-success">
                    <tr>
                        @if(auth()->user()->role !== 'vendor')
                        <th class="text-center align-middle">Aksi</th>
                        @endif
                        <th class="text-center align-middle">No</th>
                        <th class="text-center align-middle">Tanggal UJ</th>
                        <th class="text-center align-middle">Kode</th>
                        <th class="text-center align-middle">Nomor Lambung</th>
                        <th class="text-center align-middle">Vendor</th>
                        <th class="text-center align-middle">Rute</th>
                        <th class="text-center align-middle">Jarak</th>
                        <th class="text-center align-middle">Harga</th>
                        <th class="text-center align-middle">Tanggal Muat</th>
                        <th class="text-center align-middle">Nota Muat</th>
                        <th class="text-center align-middle">Tonase Muat</th>
                        <th class="text-center align-middle">Tanggal Bongkar</th>
                        <th class="text-center align-middle">Nota Bongkar</th>
                        <th class="text-center align-middle">Tonase Bongkar</th>
                        <th class="text-center align-middle">Selisih (Ton)</th>
                        <th class="text-center align-middle">Selisih (%)</th>
                        <th class="text-center align-middle">Bruto</th>
                        <th class="text-center align-middle">Uang Jalan</th>
                        <th class="text-center align-middle">Netto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $d)
                    <tr>
                        @if(auth()->user()->role !== 'vendor')
                        <td class="text-center align-middle">
                            <form action="{{ route('transaksi.nota-bayar.keluar-keranjang', $vendor) }}" method="POST" class="form-keluar-item">
                                @csrf
                                <input type="hidden" name="transaksi_ids[]" value="{{ $d->id }}">
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Keluarkan dari keranjang">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </form>
                        </td>
                        @endif
                        <td class="text-center align-middle">{{$loop->iteration}}</td>
                        <td class="text-center align-middle">{{$d->kas_uang_jalan->tanggal}}</td>
                        <td class="text-center align-middle">
                            <strong>UJ{{sprintf("%02d", $d->kas_uang_jalan->nomor_uang_jalan)}}</strong>
                        </td>
                        <td class="text-center align-middle">{{$d->kas_uang_jalan->vehicle->nomor_lambung}}</td>
                        <td class="text-center align-middle">{{$d->kas_uang_jalan->vendor->nickname}}</td>
                        <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->nama}}</td>
                        <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->jarak}}</td>
                        <td class="text-center align-middle">{{number_format($d->harga_vendor, 0, ',', '.')}}</td>
                        <td class="text-center align-middle">{{$d->id_tanggal_muat}}</td>
                        <td class="text-center align-middle">{{$d->nota_muat}}</td>
                        <td class="text-center align-middle">{{$d->tonase}}</td>
                        <td class="text-center align-middle">{{$d->id_tanggal_bongkar}}</td>
                        <td class="text-center align-middle">{{$d->nota_bongkar}}</td>
                        <td class="text-center align-middle">{{$d->timbangan_bongkar}}</td>
                        <td class="text-center align-middle">{{number_format($d->tonase - $d->timbangan_bongkar, 2, ',','.')}}</td>
                        <td class="text-center align-middle">{{number_format(($d->tonase - $d->timbangan_bongkar)*0.1, 2, ',','.')}}</td>
                        <td class="text-center align-middle">{{number_format(($d->nominal_bayar), 0, ',', '.')}}</td>
                        <td class="text-center align-middle">{{number_format(($d->kas_uang_jalan->nominal_transaksi), 0, ',', '.')}}</td>
                        <td class="text-center align-middle">{{number_format(($d->nominal_bayar-$d->kas_uang_jalan->nominal_transaksi), 0, ',', '.')}}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role !== 'vendor' ? 20 : 19 }}" class="text-center text-muted py-4">
                            Keranjang masih kosong. <a href="{{ route('transaksi.nota-bayar', $vendor) }}">Klik di sini</a> untuk memilih transaksi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($data->count() > 0)
                <tfoot>
                    <tr>
                        <td class="text-center align-middle" colspan="{{ auth()->user()->role !== 'vendor' ? 16 : 15 }}"></td>
                        <td class="text-center align-middle"><strong>Total</strong></td>
                        <td class="align-middle text-center">{{number_format($total_tagihan, 0, ',', '.')}}</td>
                        <td class="align-middle text-center">{{number_format($total_uang_jalan, 0, ',', '.')}}</td>
                        <td class="align-middle text-end">{{number_format($total_netto, 0, ',', '.')}}</td>
                    </tr>
                    <tr>
                        <td class="text-center align-middle" colspan="{{ auth()->user()->role !== 'vendor' ? 16 : 15 }}"></td>
                        <td class="text-center align-middle"><strong>PPN</strong></td>
                        <td class="align-middle"></td>
                        <td></td>
                        <td class="text-end align-middle">{{number_format($ppn, 0, ',', '.')}}</td>
                    </tr>
                    <tr>
                        <td class="align-middle" colspan="{{ auth()->user()->role !== 'vendor' ? 16 : 15 }}"></td>
                        <td class="text-center align-middle"><strong>PPh</strong></td>
                        <td class="align-middle"></td>
                        <td></td>
                        <td class="align-middle text-end">{{number_format($pph, 0, ',', '.')}}</td>
                    </tr>
                    <tr>
                        <td class="align-middle" colspan="{{ auth()->user()->role !== 'vendor' ? 16 : 15 }}"></td>
                        <td class="text-center align-middle"><strong>Tagihan</strong></td>
                        <td class="align-middle"></td>
                        <td></td>
                        <td class="align-middle text-end"><strong>{{number_format($grant_total, 0, ',', '.')}}</strong></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@if (auth()->user()->role !== 'vendor' && $data->count() > 0)
<div class="container-fluid mb-5">
    <div class="card border-info">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fa fa-calendar-alt me-2"></i> Konfirmasi Pembayaran & Jatuh Tempo</h5>
        </div>
        <div class="card-body">
            <form action="{{route('transaksi.nota-bayar.lanjut', $vendor)}}" method="post" id="lanjutForm">
                @csrf
                <input type="hidden" name="ppn" value="{{$ppn}}">
                <input type="hidden" name="pph" value="{{$pph}}">
                <input type="hidden" name="total_bayar" value="{{$grant_total}}">

                <div class="row align-items-end justify-content-center">
                    <div class="col-md-4 mb-3">
                        <label for="tempo" class="form-label font-weight-bold">
                            Tanggal Jatuh Tempo<span class="badge bg-info text-dark ms-1"> {{ $hariJatuhTempo ?? 0 }} Hari</span>
                            @if(!$isAdminOrSu)
                                <small class="text-muted">(Akses ubah hanya untuk Admin)</small>
                            @endif
                        </label>
                        <input type="date" class="form-control" id="tempo" name="tempo"
                               value="{{ $defaultJatuhTempo }}"
                               {{ $isAdminOrSu ? '' : 'readonly' }} required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-primary btn-lg w-100" type="submit">
                            <i class="fa fa-paper-plane me-1"></i> Lanjutkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#keranjangTable').DataTable({
            "paging": false,
            "ordering": false,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "400px",
            "scrollX": true,
        });
    });

    // Konfirmasi Swal untuk Hapus Per Item
    $(document).on('submit', '.form-keluar-item', function(e){
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: 'Keluarkan Transaksi?',
            text: "Transaksi ini akan dikeluarkan dari keranjang.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, keluarkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Konfirmasi Swal untuk Kosongkan Keranjang
    $('#kosongkanForm').submit(function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Kosongkan Keranjang?',
            text: "Semua transaksi akan dikeluarkan dari keranjang.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, kosongkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Konfirmasi Swal untuk Lanjutkan Pembayaran
    $('#lanjutForm').submit(function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data keranjang akan diproses menjadi invoice pembayaran.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
</script>
@endpush
