@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Kas Vendor</u></h1>
            <h1>{{$vendor->nama}}</h1>
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
                    <td><a href="{{route('rekap.index')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen"
                                width="30"> REKAP</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container-fluid mt-5">
    <form action="{{route('rekap.kas-vendor')}}" method="get">
        <div class="row">
            <input type="hidden" name="vendor" value="{{$vendor->id}}">
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
                <a href="{{route('rekap.kas-vendor.preview', ['bulan' => $bulan, 'tahun' => $tahun, 'vendor' => $vendor])}}" target="_blank"
                    class="btn btn-secondary form-control" id="btn-cari">Print Preview</a>
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
                    <th class="text-center align-middle">Nomor Lambung</th>
                    <th class="text-center align-middle">Pinjaman/Pelunasan</th>
                    <th class="text-center align-middle">Bayar</th>
                    <th class="text-center align-middle">Sisa</th>
                    <th class="text-center align-middle">Action</th>
                </tr>
                <tr class="table-warning">

                    <td colspan="3" class="text-center align-middle">
                        {{$stringBulan}} {{$tahunSebelumnya}}</td>
                    <td class="text-center align-middle"></td>
                    <td></td>
                    <td>
                        Rp. {{$dataSebelumnya ?
                            number_format($dataSebelumnya->sisa, 0,',','.') : ''}}
                    </td>
                    <td class="text-center align-middle"></td>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal}}</td>
                    <td class="text-center align-middle">{{$d->uraian}}</td>
                    <td class="text-center align-middle">{{$d->vehicle_id ? $d->vehicle->nomor_lambung : ''}}</td>
                    <td class="text-center align-middle">{{number_format($d->pinjaman, 0, ',', '.')}}</td>
                    <td class="text-center align-middle">{{number_format($d->bayar, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($d->sisa, 0, ',','.')}}</td>
                    <td class="text-center align-middle">
                        @if ($d->storing == 1 && $d->void == 0 || $d->jasa == 1 && $d->void == 0)
                        {{-- button edit --}}
                        {{-- button void --}}
                        <button class="btn btn-danger" data-bs-toggle="modal"
                            data-bs-target="#void-{{$d->id}}">Void</button>

                        <div class="modal fade" id="void-{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
                            data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm"
                                role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalTitleId">Void Storing</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form action="{{route('rekap.kas-vendor.void', $d)}}" method="post">
                                        @csrf
                                        <div class="modal-body">
                                            <input type="password" class="form-control" id="password" name="password"
                                                placeholder="Password" aria-label="Password" aria-describedby="password"
                                                required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Tutup</button>
                                            <button type="submit" class="btn btn-primary">Lanjutkan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    <td class="text-center align-middle"><strong>Grand Total</strong> </td>
                    <td class="text-center align-middle">{{number_format($data->sum('pinjaman'), 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($data->sum('bayar'), 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($data->sum('pinjaman') - $data->sum('bayar'),
                        0, ',','.')}}</td>
                    <td></td>
                </tr>
                {{-- <tr>
                    <td colspan="3" class="text-center align-middle"><strong>GRAND TOTAL</strong></td>
                    <td class="text-center align-middle"><strong>{{number_format($data->where('jenis_transaksi_id',
                            1)->sum('nominal_transaksi'), 0, ',', '.')}}</strong></td>
                    <td class="text-center align-middle text-danger">
                        <strong>{{number_format($data->where('jenis_transaksi_id',
                            2)->sum('nominal_transaksi'), 0, ',', '.')}}</strong></td>
                    <td class="text-center align-middle">
                        <strong>
                            {{$data->last() ? number_format($data->last()->saldo, 0, ',', '.') : ''}}
                        </strong>
                    </td>
                    <td></td>
                    <td></td>
                </tr> --}}
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
