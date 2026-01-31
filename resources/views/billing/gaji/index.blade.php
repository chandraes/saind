@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="text-center mb-5">
        <h1><u>Form Gaji Direksi & Staff</u></h1>
        <h1>{{ $monthName }} {{ $tahun }}</h1>
    </div>

    @php
        $gt = ['gp' => 0, 'tj' => 0, 'tk' => 0, 'btk' => 0, 'bk' => 0, 'ptk' => 0, 'pk' => 0, 'pkotor' => 0, 'pbersih' => 0, 'kasbon' => 0, 'total' => 0];
    @endphp
    <div class="mb-3 d-flex justify-content-end gap-2">
        <a href="{{ route('billing.gaji.preview-pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
        class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
        <a href="{{ route('billing.gaji.preview-excel', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
        class="btn btn-success">
            <i class="fas fa-file-excel"></i> Download Excel
        </a>
    </div>
    <div class="table-responsive" style="font-size:12px">
        <table class="table table-bordered" id="rekapTable">
            <thead class="table-success text-center align-middle">
                <tr>
                    <th class="text-center" rowspan="2">No</th>
                    <th class="text-center" rowspan="2">Nama</th>
                    <th class="text-center" rowspan="2">Jabatan</th>
                    <th class="text-center" rowspan="2">Gaji Pokok</th>
                    <th class="text-center" colspan="2">Tunjangan</th>
                    <th rowspan="2"  class="text-center align-middle">BPJS-TK (4,89%)</th>
                    <th rowspan="2" class="text-center align-middle">BPJS-Kesehatan (4%)</th>
                    <th rowspan="2" class="text-center align-middle">Potongan BPJS-TK (2%)</th>
                    <th rowspan="2" class="text-center align-middle">Potongan BPJS-Kesehatan (1%)</th>
                    <th rowspan="2" class="text-center align-middle">Total Pendapatan Kotor</th>
                    <th rowspan="2" class="text-center align-middle">Total Pendapatan Bersih</th>
                    <th rowspan="2" class="text-center align-middle">Kasbon</th>
                    <th rowspan="2" class="text-center align-middle">Sisa Gaji Dibayar</th>
                </tr>
                <tr>
                    <th class="text-center">Jabatan</th>
                    <th class="text-center">Keluarga</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_merge($direksi->all(), $data->all()) as $index => $item)
                    @php
                        $res = $payroll->calculateComponent($item);
                        $kasbon = isset($item->kode) ? $payroll->calculateKasbon($item, $bulan, $tahun) : 0;
                        $sisa = $res['pendapatan_bersih'] - $kasbon;

                        // Akumulasi Grand Total
                        $gt['gp'] += $item->gaji_pokok; $gt['tj'] += $item->tunjangan_jabatan;
                        $gt['tk'] += $item->tunjangan_keluarga; $gt['btk'] += $res['bpjs_tk'];
                        $gt['bk'] += $res['bpjs_k']; $gt['ptk'] += $res['pot_tk'];
                        $gt['pk'] += $res['pot_k']; $gt['pkotor'] += $res['pendapatan_kotor'];
                        $gt['pbersih'] += $res['pendapatan_bersih']; $gt['kasbon'] += $kasbon;
                        $gt['total'] += $sisa;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>{{ $item->jabatan->nama ?? $item->jabatan }}</td>
                        <td class="text-end">{{ number_format($item->gaji_pokok, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($item->tunjangan_jabatan, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($item->tunjangan_keluarga, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($res['bpjs_tk'], 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($res['bpjs_k'], 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($res['pot_tk'], 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($res['pot_k'], 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($res['pendapatan_kotor'], 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($res['pendapatan_bersih'], 0, ',', '.') }}</td>
                        <td class="text-end text-danger">{{ number_format($kasbon, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold">{{ number_format($sisa, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-secondary fw-bold text-end">
                <tr>
                    <td colspan="3">GRAND TOTAL</td>
                    <td class="text-end">{{ number_format($gt['gp'], 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($gt['tj'], 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($gt['tk'], 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($gt['btk'], 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($gt['bk'], 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($gt['ptk'], 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($gt['pk'], 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($gt['pkotor'], 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($gt['pbersih'], 0, ',', '.') }}</td>
                    <td class="text-danger text-end">{{ number_format($gt['kasbon'], 0, ',', '.') }}</td>
                    <td class="bg-primary text-white text-end">{{ number_format($gt['total'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="mt-4 d-flex justify-content-center gap-2">
        <form action="{{ route('billing.gaji.store') }}" method="post" id="lanjutForm">
            @csrf
            <input type="hidden" name="bulan" value="{{$bulan}}">
            <input type="hidden" name="tahun" value="{{$tahun}}">
            <button class="btn btn-primary btn-lg" type="submit">Konfirmasi & Simpan Gaji</button>
        </form>
        <a href="{{ route('billing.index') }}" class="btn btn-secondary btn-lg">Batal</a>
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
                precision: 0,
                allowZero: true,
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
