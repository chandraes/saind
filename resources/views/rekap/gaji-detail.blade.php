@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>REKAP GAJI KARYAWAN</u></h1>
            <h1>{{$bulan}} {{$tahun}}</h1>
        </div>
    </div>
    @include('swal')
    <form action="{{route('print-rekap-gaji')}}" method="get" target="_blank">
        <input type="hidden" name="bulan" value="{{$bulan_angka}}">
        <input type="hidden" name="tahun" value="{{$tahun}}">
        <button type="submit" class="btn btn-success mb-3">Cetak Rekap gaji Karyawan</button>
    </form>
    <div style="font-size:12px">
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th rowspan="2" class="text-center align-middle">No</th>
                    <th rowspan="2" class="text-center align-middle">NIK</th>
                    <th rowspan="2" class="text-center align-middle">Nama</th>
                    <th rowspan="2" class="text-center align-middle">Jabatan</th>
                    <th rowspan="2" class="text-center align-middle">Gaji Pokok</th>
                    <th colspan="2" class="text-center align-middle">Tunjangan</th>
                    <th rowspan="2" class="text-center align-middle">BPJS-TK (4,89%)</th>
                    <th rowspan="2" class="text-center align-middle">BPJS-Kesehatan (4%)</th>
                    <th rowspan="2" class="text-center align-middle">Potongan BPJS-TK (2%)</th>
                    <th rowspan="2" class="text-center align-middle">Potongan BPJS-Kesehatan (1%)</th>
                    <th rowspan="2" class="text-center align-middle">Total Pendapatan Kotor</th>
                    <th rowspan="2" class="text-center align-middle">Total Pendapatan Bersih</th>
                    <th rowspan="2" class="text-center align-middle">Kasbon</th>
                    <th rowspan="2" class="text-center align-middle">Sisa Gaji Dibayar</th>
                    <th rowspan="2" class="text-center align-middle">Rekening</th>
                    <th rowspan="2" class="text-center align-middle">Nama Rekening</th>
                    <th rowspan="2" class="text-center align-middle">Bank</th>
                </tr>
                <tr>
                    <th class="text-center align-middle">Jabatan</th>
                    <th class="text-center align-middle">Keluarga</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($data->rekap_gaji_detail as $dir)
                <tr>
                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                    <td class="text-center align-middle">{{$dir->nik}}</td>
                    <td class="text-start align-middle">
                        <a href="{{ route('print-slip-gaji', $dir->id) }}" class="btn btn-sm btn-outline-primary text-start" target="_blank">
                        <i class="fas fa-file-pdf"></i> {{$dir->nama}}
                    </a>

                    </td>
                    <td class="text-center align-middle">{{$dir->jabatan}}</td>
                    <td class="text-end align-middle">{{number_format($dir->gaji_pokok, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($dir->tunjangan_jabatan, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($dir->tunjangan_keluarga, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($dir->bpjs_tk, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($dir->bpjs_k, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($dir->potongan_bpjs_tk, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($dir->potongan_bpjs_kesehatan, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($dir->pendapatan_kotor, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($dir->pendapatan_bersih, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($dir->kasbon, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($dir->sisa_gaji_dibayar, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{$dir->no_rekening}}</td>
                    <td class="text-center align-middle">{{$dir->transfer_ke}}</td>
                    <td class="text-center align-middle">{{$dir->bank}}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="4">Grand Total : </th>
                    <th class="text-end align-middle">{{number_format($data->rekap_gaji_detail->sum('gaji_pokok'), 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->rekap_gaji_detail->sum('tunjangan_jabatan'), 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->rekap_gaji_detail->sum('tunjangan_keluarga'), 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->rekap_gaji_detail->sum('bpjs_tk'), 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->rekap_gaji_detail->sum('bpjs_k'), 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->rekap_gaji_detail->sum('potongan_bpjs_tk'), 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->rekap_gaji_detail->sum('potongan_bpjs_kesehatan'), 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->rekap_gaji_detail->sum('pendapatan_kotor'), 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->rekap_gaji_detail->sum('pendapatan_bersih'), 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->rekap_gaji_detail->sum('kasbon'), 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->rekap_gaji_detail->sum('sisa_gaji_dibayar'), 0, ',','.')}}</th>
                    <th class="text-end align-middle" colspan="3"></th>
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
