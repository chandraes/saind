@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Gaji Direksi & Staff</u></h1>
            <h1>{{$month}} {{date('Y')}}</h1>
        </div>
    </div>
    @include('swal')
    <div style="font-size:12px">
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th rowspan="2" class="text-center align-middle">NIK</th>
                    <th rowspan="2" class="text-center align-middle">Nama</th>
                    <th rowspan="2" class="text-center align-middle">Jabatan</th>
                    <th rowspan="2" class="text-center align-middle">Gaji Pokok</th>
                    <th colspan="4" class="text-center align-middle">Tunjangan</th>
                    <th rowspan="2" class="text-center align-middle">Potongan BPJS-TK (2%)</th>
                    <th rowspan="2" class="text-center align-middle">Potongan BPJS-Kesehatan (1%)</th>
                    <th rowspan="2" class="text-center align-middle">Total Pendapatan Kotor</th>
                    <th rowspan="2" class="text-center align-middle">Total Pendapatan Bersih</th>
                    <th rowspan="2" class="text-center align-middle">Kasbon</th>
                    <th rowspan="2" class="text-center align-middle">Sisa Gaji Dibayar</th>
                </tr>
                <tr>
                    <th class="text-center align-middle">Jabatan</th>
                    <th class="text-center align-middle">Keluarga</th>
                    <th class="text-center align-middle">BPJS-TK (4,89%)</th>
                    <th class="text-center align-middle">BPJS-Kesehatan (4%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($direksi as $dir)
                @php
                    $bpjs_tk_direksi = $dir->gaji_pokok * 0.049;
                    $bpjs_k_direksi = $dir->gaji_pokok * 0.04;
                    $potongan_bpjs_tk_direksi = $dir->gaji_pokok * 0.02;
                    $potongan_bpjs_kesehatan_direksi = $dir->gaji_pokok * 0.01;
                    $pendapatan_kotor_direksi = $dir->gaji_pokok + $dir->tunjangan_jabatan + $dir->tunjangan_keluarga + $bpjs_tk_direksi + $bpjs_k_direksi;
                    $pendapatan_bersih_direksi = $dir->gaji_pokok + $dir->tunjangan_jabatan + $dir->tunjangan_keluarga - $dir->potongan_bpjs_tk - $dir->potongan_bpjs_kesehatan;
                @endphp
                <tr>
                    <td class="text-center align-middle">Direksi</td>
                    <td class="text-center align-middle">{{$dir->nama}}</td>
                    <td class="text-center align-middle">{{$dir->jabatan}}</td>
                    <td class="text-center align-middle">{{number_format($dir->gaji_pokok, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($dir->tunjangan_jabatan, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($dir->tunjangan_keluarga, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($bpjs_tk_direksi, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($bpjs_k_direksi, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($potongan_bpjs_tk_direksi, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($potongan_bpjs_kesehatan_direksi, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($pendapatan_kotor_direksi, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($pendapatan_bersih_direksi, 0, ',','.')}}</td>
                    <td class="text-center align-middle">-</td>
                    <td class="text-center align-middle">{{number_format($pendapatan_bersih_direksi, 0, ',','.')}}</td>
                </tr>
                @endforeach
                @foreach ($data as $i)
                @php
                    $bpjs_tk = $i->gaji_pokok * 0.049;
                    $bpjs_k = $i->gaji_pokok * 0.04;
                    $potongan_bpjs_tk = $i->gaji_pokok * 0.02;
                    $potongan_bpjs_kesehatan = $i->gaji_pokok * 0.01;
                    $pendapatan_kotor = $i->gaji_pokok + $i->tunjangan_jabatan + $i->tunjangan_keluarga + $bpjs_tk + $bpjs_k;
                    $pendapatan_bersih = $i->gaji_pokok + $i->tunjangan_jabatan + $i->tunjangan_keluarga - $i->potongan_bpjs_tk - $i->potongan_bpjs_kesehatan;
                @endphp
                <tr>
                    <td class="text-center align-middle">{{$i->kode}}{{sprintf("%03d",$i->nomor)}}</td>
                    <td class="text-center align-middle">{{$i->nama}}</td>
                    <td class="text-center align-middle">{{$i->jabatan->nama}}</td>
                    <td class="text-center align-middle">{{number_format($i->gaji_pokok, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($i->tunjangan_jabatan, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($i->tunjangan_keluarga, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($bpjs_tk, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($bpjs_k, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($potongan_bpjs_tk, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($potongan_bpjs_kesehatan, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($pendapatan_kotor, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($pendapatan_bersih, 0, ',','.')}}</td>
                    <td class="text-center align-middle">
                        @if ($i->kas_bon->where('cicilan', 1)->where('lunas', 0)->count() > 0)
                        @php
                            $kasbon_cicil = $i->kas_bon->where('cicilan', 1)->where('lunas', 0)->sum('cicilan_nominal');
                            $kasbon = $i->kas_bon->where('cicilan', 0)->where('lunas', 0)->sum('nominal');
                            $total_kasbon = $kasbon_cicil + $kasbon;
                        @endphp
                            {{number_format($total_kasbon, 0, ',','.')}}
                        @else
                            @php
                                $total_kasbon = $i->kas_bon->where('cicilan', 0)->where('lunas', 0)->sum('nominal');
                            @endphp
                            {{number_format($total_kasbon, 0, ',','.')}}
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        @php
                            $sisa_gaji_dibayar = $pendapatan_bersih - $total_kasbon;
                        @endphp
                        {{number_format($sisa_gaji_dibayar, 0, ',','.')}}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="container-fluid mt-3 mb-3">
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
            <form action="#" method="post" id="lanjutForm">
                @csrf
                <input type="hidden" name="total_bonus" value="">
                <button class="btn btn-primary me-md-3 btn-lg" type="submit">Lanjutkan</button>
            </form>
            {{-- <a class="btn btn-success btn-lg" href="#">Export</a> --}}
          </div>
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
            $('#nominal_transaksi').maskMoney();
            $('#rekapTable').DataTable({
                "paging": false,
                "ordering": false,
                "scrollCollapse": true,
                "scrollY": "550px",
            });
        });
        // masukForm on submit, sweetalert confirm
        $('#masukForm').submit(function(e){
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
