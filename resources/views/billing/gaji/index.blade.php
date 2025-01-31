@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Gaji Direksi & Staff</u></h1>
            <h1>{{$month}} {{date('Y')}}</h1>
        </div>
    </div>
    @php
        $total = 0;
        $grandTotalPotonganBpjsTk = 0;
        $grandTotalPotonganBpjsKesehatan = 0;
        $grandTotalPendapatanKotor = 0;
        $grandTotalPendapatanBersih = 0;
        $grandTotalKasbon = 0;
        $gtPotonganBpjsTk = 0;
        $gtPotonganBpjsKesehatan = 0;
        $gtTunjanganKeluarga = 0;
        $gtTunjanganJabatan = 0;
        $gtGajiPokok = 0;
        $gtBpjsTk = 0;
        $gtBpjsKesehatan = 0;
        $no = 1;
    @endphp
    @include('swal')
    <div style="font-size:12px">
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th rowspan="2" class="text-center align-middle">No</th>
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
                    $bpjs_tk_direksi = $dir->apa_bpjs_tk == 1 ? $dir->gaji_pokok * 0.049 : 0;
                    $bpjs_k_direksi = $dir->apa_bpjs_kesehatan == 1 ? $dir->gaji_pokok * 0.04 : 0;
                    $potongan_bpjs_tk_direksi = $dir->apa_bpjs_tk == 1 ? $dir->gaji_pokok * 0.02 : 0;
                    $potongan_bpjs_kesehatan_direksi = $dir->apa_bpjs_kesehatan == 1 ? $dir->gaji_pokok * 0.01 : 0;

                    $grandTotalPotonganBpjsTk = $grandTotalPotonganBpjsTk + $potongan_bpjs_tk_direksi;
                    $gtPotonganBpjsKesehatan += $potongan_bpjs_kesehatan_direksi;
                    $gtPotonganBpjsTk += $potongan_bpjs_tk_direksi;
                    $gtTunjanganKeluarga += $dir->tunjangan_keluarga;
                    $gtTunjanganJabatan += $dir->tunjangan_jabatan;
                    $gtBpjsTk += $bpjs_tk_direksi;
                    $gtBpjsKesehatan += $bpjs_k_direksi;
                    $gtGajiPokok += $dir->gaji_pokok;
                    $grandTotalPotonganBpjsKesehatan = $grandTotalPotonganBpjsKesehatan + $potongan_bpjs_kesehatan_direksi;
                    $pendapatan_kotor_direksi = $dir->gaji_pokok + $dir->tunjangan_jabatan + $dir->tunjangan_keluarga + $bpjs_tk_direksi + $bpjs_k_direksi;
                    $grandTotalPendapatanKotor = $grandTotalPendapatanKotor + $pendapatan_kotor_direksi;
                    $pendapatan_bersih_direksi = $dir->gaji_pokok + $dir->tunjangan_jabatan + $dir->tunjangan_keluarga - $potongan_bpjs_tk_direksi - $potongan_bpjs_kesehatan_direksi;
                    $grandTotalPendapatanBersih = $grandTotalPendapatanBersih + $pendapatan_bersih_direksi;
                    $total = $pendapatan_bersih_direksi + $total;
                @endphp
                <tr>
                    <td class="text-center align-middle">{{$no++}}</td>
                    <td class="text-center align-middle">Direksi</td>
                    <td class="text-center align-middle">{{$dir->nama}}</td>
                    <td class="text-center align-middle">{{$dir->jabatan}}</td>
                    <td class="text-end align-middle">{{number_format($dir->gaji_pokok, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($dir->tunjangan_jabatan, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($dir->tunjangan_keluarga, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($bpjs_tk_direksi, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($bpjs_k_direksi, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($potongan_bpjs_tk_direksi, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($potongan_bpjs_kesehatan_direksi, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($pendapatan_kotor_direksi, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($pendapatan_bersih_direksi, 0, ',','.')}}</td>
                    <td class="text-end align-middle">-</td>
                    <td class="text-end align-middle">{{number_format($pendapatan_bersih_direksi, 0, ',','.')}}</td>
                </tr>
                @endforeach
                @foreach ($data as $i)
                @php
                    $bpjs_tk = $i->apa_bpjs_tk == 1 ? $i->gaji_pokok * 0.049 : 0;
                    $potongan_bpjs_tk = $i->apa_bpjs_tk == 1 ? $i->gaji_pokok * 0.02 : 0;
                    $bpjs_k = $i->apa_bpjs_kesehatan == 1 ? $i->gaji_pokok * 0.04 : 0;
                    $potongan_bpjs_kesehatan = $i->apa_bpjs_kesehatan == 1 ? $i->gaji_pokok * 0.01 : 0;

                    $grandTotalPotonganBpjsTk = $grandTotalPotonganBpjsTk + $potongan_bpjs_tk;
                    $gtPotonganBpjsKesehatan += $potongan_bpjs_kesehatan;
                    $gtPotonganBpjsTk += $potongan_bpjs_tk;
                    $gtTunjanganKeluarga += $i->tunjangan_keluarga;
                    $gtTunjanganJabatan += $i->tunjangan_jabatan;
                    $gtGajiPokok += $i->gaji_pokok;
                    $gtBpjsTk += $bpjs_tk;
                    $gtBpjsKesehatan += $bpjs_k;
                    $grandTotalPotonganBpjsKesehatan = $grandTotalPotonganBpjsKesehatan + $potongan_bpjs_kesehatan;
                    $pendapatan_kotor = $i->gaji_pokok + $i->tunjangan_jabatan + $i->tunjangan_keluarga + $bpjs_tk + $bpjs_k;
                    $grandTotalPendapatanKotor = $grandTotalPendapatanKotor + $pendapatan_kotor;
                    $pendapatan_bersih = $i->gaji_pokok + $i->tunjangan_jabatan + $i->tunjangan_keluarga - $potongan_bpjs_tk - $potongan_bpjs_kesehatan;
                    $grandTotalPendapatanBersih = $grandTotalPendapatanBersih + $pendapatan_bersih;
                @endphp
                <tr>
                    <td class="text-center align-middle">{{$no++}}</td>
                    <td class="text-center align-middle">{{$i->kode}}{{sprintf("%03d",$i->nomor)}}</td>
                    <td class="text-center align-middle">{{$i->nama}}</td>
                    <td class="text-center align-middle">{{$i->jabatan->nama}}</td>
                    <td class="text-end align-middle">{{number_format($i->gaji_pokok, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($i->tunjangan_jabatan, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($i->tunjangan_keluarga, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($bpjs_tk, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($bpjs_k, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($potongan_bpjs_tk, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($potongan_bpjs_kesehatan, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($pendapatan_kotor, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{number_format($pendapatan_bersih, 0, ',','.')}}</td>
                    <td class="text-end align-middle">
                        @php
                        $cicilan = $i->kas_bon_cicilan->where('lunas', 0)->first();
                        $kasbon_cicil = 0;

                        if ($cicilan) {
                            // Create tanggal from $cicilan->mulai_bulan and $cicilan->mulai_tahun
                            $mulai = Carbon\Carbon::createFromDate($cicilan->mulai_tahun, $cicilan->mulai_bulan, 1)->timestamp;
                            // Create current date from $tahun and $bulan
                            $now = Carbon\Carbon::createFromDate($tahun, $bulan, 1)->timestamp;

                            // Check if the current month and year is the same as the installment start month and year
                            if ($now >= $mulai) {
                                $kasbon_cicil = $cicilan->cicilan_nominal;
                            }


                        }

                        $kasbon = $i->kas_bon->where('lunas', 0)->sum('nominal');
                        $total_kasbon = $kasbon_cicil + $kasbon;
                    @endphp

                    {{ number_format($total_kasbon, 0, ',', '.') }}
                    </td>
                    <td class="text-end align-middle">
                        @php
                            $sisa_gaji_dibayar = $pendapatan_bersih - $total_kasbon;
                            $total = $total + $sisa_gaji_dibayar;
                            $grandTotalKasbon = $grandTotalKasbon + $total_kasbon;
                        @endphp
                        {{number_format($sisa_gaji_dibayar, 0, ',','.')}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end align-middle">Grand Total : </th>
                    <th class="text-end align-middle">{{number_format($gtGajiPokok, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($gtTunjanganJabatan, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($gtTunjanganKeluarga, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($gtBpjsTk, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($gtBpjsKesehatan, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($grandTotalPotonganBpjsTk, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($grandTotalPotonganBpjsKesehatan, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($grandTotalPendapatanKotor, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($grandTotalPendapatanBersih, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($grandTotalKasbon, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($total, 0, ',','.')}}</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="container-fluid mt-3 mb-3">
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
            <form action="{{route('billing.gaji.store')}}" method="post" id="lanjutForm">
                @csrf
                <input type="hidden" name="bulan" value="{{$bulan}}">
                <input type="hidden" name="total" value="{{$total}}">
                <input type="hidden" name="tahun" value="{{$tahun}}">
                <button class="btn btn-primary me-md-3 btn-lg" type="submit">Lanjutkan</button>
            </form>
            <a href="{{route('billing.form-cost-operational')}}" class="btn btn-secondary btn-lg">Batalkan</a>
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
