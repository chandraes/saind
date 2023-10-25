@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Rekap Barang</u></h1>
            <h1>{{$stringBulanNow}} {{$tahun}}</h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap.index')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen" width="30"> REKAP</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container-fluid mt-5">
    <form action="{{route('rekap.stock-barang')}}" method="get">
        <div class="row">

            <div class="col-md-3 mb-3">
                <label for="bulan" class="form-label">Bulan</label>
                <select class="form-select" name="bulan" id="bulan">
                    <option value="1" {{$bulan=='01' ? 'selected' : '' }}>Januari</option>
                    <option value="2" {{$bulan=='02' ? 'selected' : '' }}>Februari</option>
                    <option value="3" {{$bulan=='03' ? 'selected' : '' }}>Maret</option>
                    <option value="4" {{$bulan=='04' ? 'selected' : '' }}>April</option>
                    <option value="5" {{$bulan=='05' ? 'selected' : '' }}>Mei</option>
                    <option value="6" {{$bulan=='06' ? 'selected' : '' }}>Juni</option>
                    <option value="7" {{$bulan=='07' ? 'selected' : '' }}>Juli</option>
                    <option value="8" {{$bulan=='08' ? 'selected' : '' }}>Agustus</option>
                    <option value="9" {{$bulan=='09' ? 'selected' : '' }}>September</option>
                    <option value="10" {{$bulan=='10' ? 'selected' : '' }}>Oktober</option>
                    <option value="11" {{$bulan=='11' ? 'selected' : '' }}>November</option>
                    <option value="12" {{$bulan=='12' ? 'selected' : '' }}>Desember</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="tahun" class="form-label">Tahun</label>
                <select class="form-select" name="tahun" id="tahun">
                    @foreach ($dataTahun as $d)
                    <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="tahun" class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
            </div>
            <div class="col-md-3 mb-3">
                <label for="showPrint" class="form-label">&nbsp;</label>
                {{-- <a href="{{route('rekap.kas-kecil.preview', ['bulan' => $bulan, 'tahun' => $tahun])}}" target="_blank" class="btn btn-secondary form-control" id="btn-cari">Print Preview</a> --}}
            </div>
        </div>
    </form>
</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class=" table-success">
            <tr>
                <th class="text-center align-middle">Tanggal</th>
                <th class="text-center align-middle">Uraian</th>
                <th class="text-center align-middle">Satuan</th>
                <th class="text-center align-middle">Harga Beli</th>
                <th class="text-center align-middle">Harga Jual</th>
                <th class="text-center align-middle">Total Harga Beli</th>
                <th class="text-center align-middle">Total Harga Jual</th>
                <th class="text-center align-middle">Profit</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal}}</td>
                    <td class="text-center align-middle">{{$d->jenis_transaksi == 1 ? "Beli ".$d->barang->kategori_barang->nama.' '.$d->nama_barang : "Jual ".$d->barang->kategori_barang->nama.' '.$d->nama_barang}}</td>
                    <td class="text-center align-middle">
                        {{$d->jumlah}}
                    </td>
                    <td class="text-center align-middle">
                        {{number_format($d->harga_satuan, 0, ',', '.')}}
                    </td>
                    <td class="text-center align-middle">
                        {{number_format($d->barang->harga_jual, 0, ',', '.')}}
                    </td>
                    <td class="text-center align-middle">
                        {{number_format($d->total, 0, ',', '.')}}
                    </td>
                    <td class="text-center align-middle">
                        {{number_format($d->barang->harga_jual * $d->jumlah, 0, ',', '.')}}
                    </td>
                    <td class="text-center align-middle">
                        {{number_format(($d->barang->harga_jual * $d->jumlah) - $d->total, 0, ',', '.')}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-center align-middle">

                    </td>
                    <td class="text-center align-middle">

                    </td>
                    <td class="text-center align-middle text-danger">

                    </td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    // hide alert after 5 seconds
    setTimeout(function() {
        $('#alert').fadeOut('slow');
    }, 5000);

    $(document).ready(function() {
        $('#rekapTable').DataTable({
            "paging": false,
            "ordering": false,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });

    });

</script>
@endpush
