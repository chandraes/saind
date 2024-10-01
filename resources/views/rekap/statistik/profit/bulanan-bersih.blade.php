@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Statistik Profit Bulanan (Bersih)</u></h1>
            <h1>{{$tahun}}</h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('statisik.index')}}"><img src="{{asset('images/statistik.svg')}}" alt="dokumen"
                                width="30"> STATISTIK</a></td>
                    <td>
                        <form target="_blank" action="{{route('statistik.profit-bulanan.print')}}" method="get">



                            <input type="hidden" name="tahun" value="{{$tahun}}">
                            <button class="btn" type="submit">
                                <img src="{{asset('images/document.svg')}}" alt="dokumen" width="30"> Print Rekap
                            </button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <form action="{{route('statistik.profit-tahunan-bersih')}}" method="get">
                <div class="row">

                    <div class="col-md-6 my-3">
                        <select class="form-select" name="tahun" id="tahun">
                            @foreach ($dataTahun as $d)
                            <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 my-3">
                        <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div>
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th rowspan="2" class="text-center align-middle">Bulan</th>
                    <th rowspan="2" class="text-center align-middle">Profit Kotor</th>
                    <th colspan="7" class="text-center align-middle">Pengeluaran</th>
                    <th rowspan="2" class="text-center align-middle">Profit Bersih</th>
                </tr>
                <tr>
                    <th class="text-center align-middle">Operational</th>
                    <th class="text-center align-middle">Kas Kecil</th>
                    <th class="text-center align-middle">Gaji</th>
                    <th class="text-center align-middle">CSR<br>(Tidak Tertentu)</th>
                    <th class="text-center align-middle">Bunga<br>Investor</th>
                    <th class="text-center align-middle">Penyesuaian</th>
                    <th class="text-center align-middle">Penalti</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statistics as $s)
                <tr>
                    <td class="text-center align-middle">{{$s['nama_bulan']}}</td>
                    <td class="text-end align-middle">{{number_format($s['profit'], 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($s['total_co'], 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($s['kas_kecil'], 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($s['total_gaji'], 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format(0, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($s['bunga_investor'], 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($s['penyesuaian'], 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($s['penalty'], 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($s['bersih'], 0, ',','.')}}</td>
                </tr>

                @endforeach
            </tbody>
            <tfoot>

                <tr>
                    <th><strong>Grand Total</strong></th>
                    <th class="text-end align-middle">{{number_format($grand_total_profit, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($grand_total_co, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($grand_total_kas_kecil, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($grand_total_gaji, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format(0, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($grand_total_bunga_investor, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($gt_penyesuaian, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($gt_penalty, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($grand_total_bersih, 0, ',','.')}}</th>
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
{{-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script> --}}
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function(){
            $('#nominal_transaksi').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0
            });
            $('#rekapTable').DataTable({
                "searching": false,
                "paging": false,
                "ordering": false,
                "scrollCollapse": true,
                "scrollY": "550px",
            });
        });
        // masukForm on submit, sweetalert confirm
        $('#lanjutForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });
</script>
@endpush
