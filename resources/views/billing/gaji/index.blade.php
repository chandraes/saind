@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Gaji Direksi & Staff</u></h1>
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
                    <th rowspan="2" class="text-center align-middle">PPH 21</th>
                    <th rowspan="2" class="text-center align-middle">Pendapatan Bersih setelah Pajak</th>
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
                <tr>
                    <td class="text-center align-middle">{{$dir->nik}}</td>
                    <td class="text-center align-middle">{{$dir->nama}}</td>
                    <td class="text-center align-middle">{{$dir->jabatan}}</td>
                    <td class="text-center align-middle">{{number_format($dir->gaji_pokok, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($dir->tunjangan_jabatan, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($dir->tunjangan_keluarga, 0, ',','.')}}</td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle">-</td>
                    <td class="text-center align-middle"></td>
                </tr>
                @endforeach
                @foreach ($data as $i)
                @php
                    $bpjs_tk = $i->gaji_pokok * 0.049;
                @endphp
                <tr>
                    <td class="text-center align-middle">{{$i->nik}}</td>
                    <td class="text-center align-middle">{{$i->nama}}</td>
                    <td class="text-center align-middle">{{$i->jabatan->nama}}</td>
                    <td class="text-center align-middle">{{number_format($i->gaji_pokok, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($i->tunjangan_jabatan, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($i->tunjangan_keluarga, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($bpjs_tk, 0, ',','.')}}</td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle"></td>
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
                            $total_pendapatan_kotor = $i->gaji_pokok + $i->tunjangan_jabatan + $i->tunjangan_keluarga;
                            $total_pendapatan_bersih = $total_pendapatan_kotor - $i->potongan_bpjs_tk - $i->potongan_bpjs_kesehatan;
                            $sisa_gaji_dibayar = $total_pendapatan_bersih - $total_kasbon;
                        @endphp
                        {{number_format($sisa_gaji_dibayar, 0, ',','.')}}
                    </td>
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
