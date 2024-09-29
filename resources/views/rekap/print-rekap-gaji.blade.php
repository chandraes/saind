@extends('layouts.doc-nologo-2')
@section('content')
<div class="container-fluid">
    <center>
        <h2>REKAP GAJI KARYAWAN</h2>
        <h2>{{$bulan}} {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-hover table-bordered table-pdf text-pdf">
            <thead class="table-pdf text-pdf table-success">
                <tr class="table-pdf text-pdf">
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">NIK</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">Nama</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">Jabatan</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">Gaji Pokok</th>
                    <th colspan="4" class="table-pdf text-pdf text-center align-middle">Tunjangan</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">Potongan BPJS-TK (2%)</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">Potongan BPJS-Kesehatan (1%)</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">Total Pendapatan Kotor</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">Total Pendapatan Bersih</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">Kasbon</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">Sisa Gaji Dibayar</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">Rekening</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">Nama Rekening</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">Bank</th>
                </tr>
                <tr>
                    <th class="table-pdf text-pdf text-center align-middle">Jabatan</th>
                    <th class="table-pdf text-pdf text-center align-middle">Keluarga</th>
                    <th class="table-pdf text-pdf text-center align-middle">BPJS-TK (4,89%)</th>
                    <th class="table-pdf text-pdf text-center align-middle">BPJS-Kesehatan (4%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $dir)
                <tr>
                    <td class="table-pdf text-pdf text-center align-middle">{{$dir->nik}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$dir->nama}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$dir->jabatan}}</td>
                    <td class="table-pdf text-pdf text-end align-middle">{{number_format($dir->gaji_pokok, 0, ',','.')}}</td>
                    <td class="table-pdf text-pdf text-end align-middle">{{number_format($dir->tunjangan_jabatan, 0, ',','.')}}</td>
                    <td class="table-pdf text-pdf text-end align-middle">{{number_format($dir->tunjangan_keluarga, 0, ',','.')}}</td>
                    <td class="table-pdf text-pdf text-end align-middle">{{number_format($dir->bpjs_tk, 0, ',','.')}}</td>
                    <td class="table-pdf text-pdf text-end align-middle">{{number_format($dir->bpjs_k, 0, ',','.')}}</td>
                    <td class="table-pdf text-pdf text-end align-middle">{{number_format($dir->potongan_bpjs_tk, 0, ',','.')}}</td>
                    <td class="table-pdf text-pdf text-end align-middle">{{number_format($dir->potongan_bpjs_kesehatan, 0, ',','.')}}</td>
                    <td class="table-pdf text-pdf text-end align-middle">{{number_format($dir->pendapatan_kotor, 0, ',','.')}}</td>
                    <td class="table-pdf text-pdf text-end align-middle">{{number_format($dir->pendapatan_bersih, 0, ',','.')}}</td>
                    <td class="table-pdf text-pdf text-end align-middle">{{number_format($dir->kasbon, 0, ',','.')}}</td>
                    <td class="table-pdf text-pdf text-end align-middle">{{number_format($dir->sisa_gaji_dibayar, 0, ',','.')}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$dir->no_rekening}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$dir->transfer_ke}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$dir->bank}}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle table-pdf text-pdf" colspan="3">Grand Total : </th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('gaji_pokok'), 0, ',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('tunjangan_jabatan'), 0, ',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('tunjangan_keluarga'), 0, ',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('bpjs_tk'), 0, ',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('bpjs_k'), 0, ',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('potongan_bpjs_tk'), 0, ',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('potongan_bpjs_kesehatan'), 0, ',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('pendapatan_kotor'), 0, ',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('pendapatan_bersih'), 0, ',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('kasbon'), 0, ',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('sisa_gaji_dibayar'), 0, ',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf" colspan="3"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
