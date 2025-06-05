@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>REKAP KASBON</u></h1>
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
    <form action="{{route('rekap.kas-bon')}}" method="get">
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
                <a href="{{route('rekap.kas-bon.preview', ['bulan' => $bulan, 'tahun' => $tahun])}}" target="_blank"
                    class="btn btn-secondary form-control" id="btn-cari">Print Preview</a>
            </div>
        </div>
    </form>
</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <div class="text-center">
            <h1><u>REKAP KASBON POTONG GAJI</u></h1>
        </div>
        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class=" table-success">
            <tr>
                <th class="text-center align-middle">Tanggal</th>
                <th class="text-center align-middle">Nama Karyawan</th>
                <th class="text-center align-middle">Nominal</th>
                <th class="text-center align-middle">Status</th>
                <th class="text-center align-middle">Keterangan</th>
                <th class="text-center align-middle">Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal}}</td>
                    <td class="text-center align-middle">{{$d->karyawan->nama}}</td>
                    <td class="text-end align-middle">
                        @if ($d->cicilan == 1)
                            {{number_format($d->sisa_kas, 0,',','.')}}
                        @else
                            {{number_format($d->nominal, 0,',','.')}}
                        @endif

                    </td>
                    <td class="text-center align-middle">
                        <h5><span class="badge bg-primary badge-xl">Potong Gaji</span></h5>
                    <td class="text-center align-middle">
                        @if ($d->lunas == 1)
                        <span class="badge bg-success">Lunas</span>
                        @else
                        <span class="badge bg-danger">Belum Lunas</span>
                        @endif
                    </td>
                    <td>
                        @if ($d->void == 0)
                        <div class="text-center">
                            <button class="btn btn-danger" data-bs-toggle="modal"
                            data-bs-target="#void-{{$d->id}}">Void</button>
                        </div>
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
                                    <form action="{{route('rekap.kas-bon.void', $d)}}" method="post">
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
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-center align-middle" colspan="2">Total</td>
                    <th class="text-end align-middle">{{number_format($dataCicilan->sum('total_bayar'),0,',','.')}}</th>
                    <td class="text-center align-middle" colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="row mt-5">
        <div class="text-center">
            <h1><u>REKAP CICILAN KASBON</u></h1>
        </div>
        <table class="table table-bordered table-hover" id="tableCicilan">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Nama Karyawan</th>
                    <th class="text-center align-middle">Nominal Kasbon</th>
                    <th class="text-center align-middle">Nominal Cicilan / bulan</th>
                    <th class="text-center align-middle">Total Bayar</th>
                    <th class="text-center align-middle">Sisa Kasbon</th>
                    <th class="text-center align-middle">Mulai</th>
                    <th class="text-center align-middle">Selesai</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dataCicilan as $c)
                <tr>
                    <td class="text-center align-middle">{{$c->id_tanggal}}</td>
                    <td class="text-start align-middle">{{$c->karyawan->nama}}</td>
                    <td class="text-end align-middle">{{number_format($c->nominal, 0,',','.')}}</td>
                    <td class="text-end align-middle">{{$c->nf_cicilan_nominal}}</td>
                    <td class="text-end align-middle">{{number_format($c->total_bayar, 0,',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($c->sisa_kas, 0,',','.')}}</td>
                    <td class="text-center align-middle">{{$c->tanggal_mulai}}</td>
                    <td class="text-center align-middle">{{$c->tanggal_selesai}}</td>
                    <td>
                        @if ($c->void == 0)
                        <div class="text-center">
                            <button class="btn btn-danger" data-bs-toggle="modal"
                            data-bs-target="#void-{{$c->id}}">Void</button>
                        </div>
                        <div class="modal fade" id="void-{{$c->id}}" tabindex="-1" data-bs-backdrop="static"
                            data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm"
                                role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalTitleId">Void Storing</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form action="{{route('billing.kasbon.kas-bon-cicil.void', $c)}}" method="post">
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
                    <th class="text-center align-middle" colspan="2">Total</th>
                    <th class="text-end align-middle">{{number_format($dataCicilan->sum('nominal'),0,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($dataCicilan->sum('cicilan_nominal'),0,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($dataCicilan->sum('total_bayar'),0,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($dataCicilan->sum('sisa_kas'),0,',','.')}}</th>
                    <th class="text-center align-middle" colspan="3"></th>
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

    $(document).ready(function() {

        $('#rekapTable').DataTable({
            "paging": false,
            "info": false,
            "ordering": true,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });

        $('#tableCicilan').DataTable({
            "paging": false,
            "info": false,
            "ordering": true,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });

    } );

</script>
@endpush
