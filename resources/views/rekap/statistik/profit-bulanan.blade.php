@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u></u></h1>
            <h1>{{$nama_bulan}} {{$tahun}}</h1>
        </div>
    </div>
    @include('swal')
    <form action="{{route('print-rekap-gaji')}}" method="get">
        <input type="hidden" name="bulan" value="{{$bulan_angka}}">
        <input type="hidden" name="tahun" value="{{$tahun}}">
        {{-- <button type="submit" class="btn btn-success mb-3">Cetak Rekap gaji Karyawan</button> --}}
    </form>
    <div style="font-size:12px">
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <td class="text-center align-middle">Tanggal</td>
                    @foreach ($vehicle as $v)
                    <td class="text-center align-middle">{{$v->nomor_lambung}}</td>
                    @endforeach
                </tr>
            </thead>
            <tbody>

                @for ($i = 1; $i <= $date; $i++)
                <tr>
                    <td class="text-center align-middle">{{$i}}</td>
                        @foreach ($vehicle as $v)
                        <td class="text-center align-middle">
                            @php
                                $profit = $data->where('nomor_lambung', $v->nomor_lambung)->where('tanggal', date('Y-m-d', strtotime($i.'-'.$bulan_angka.'-'.$tahun)))->first()->profit ?? 0;
                            @endphp
                            {{number_format($profit, 0, ',', '.')}}
                        </td>
                        @endforeach
                </tr>
                @endfor
                <tr>
                    <td class="text-center align-middle">Total</td>
                    @foreach ($vehicle as $v)
                    @php
                    $total = $data->where('nomor_lambung', $v->nomor_lambung)->sum('profit') ;
                    @endphp
                    <td class="text-center align-middle">
                        <strong>{{number_format($total, 0, ',', '.')}}</strong>
                    </td>
                    @endforeach
                </tr>
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
                    this.submit();
                }
            })
        });
</script>
@endpush
