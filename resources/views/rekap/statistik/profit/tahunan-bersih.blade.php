@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Grand Total Tahunan (Bersih)</u></h1>
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
                        <form target="_blank" action="{{route('statistik.profit.tahunan-bersih.pdf')}}" method="get">
                            {{-- <input type="hidden" name="tahun" value="{{$tahun}}"> --}}
                            <button class="btn" type="submit">
                                <img src="{{asset('images/document.svg')}}" alt="dokumen" width="30"> Print Rekap
                            </button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            {{-- <form action="{{route('statistik.profit-tahunan-bersih')}}" method="get">
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
            </form> --}}
        </div>
    </div>

    <div>
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tahun</th>
                    @foreach ($nama_bulan as $item => $value)
                    <th class="text-center align-middle">{{$value}}</th>
                    @endforeach
                    <th>Grand Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statistics as $s => $su)
                <tr>
                    <td class="text-center">{{$s}}</td>
                    @foreach ($su['data'] as $item => $value)
                    <td class="text-end">{{number_format($value['bersih'], 0, ',', '.')}}</td>
                    @endforeach
                    <th class="text-end">{{number_format($su['total'], 0, ',', '.')}}</th>
                </tr>

                @endforeach
            </tbody>

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
                "scrollY": "500px",
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
