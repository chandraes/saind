@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Tagihan & Invoice</u></h1>
            <h1>{{date('d-m-Y')}}</h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap.index')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen"
                                width="30"> Rekap</a></td>

                </tr>
            </table>
        </div>
        {{-- <div class="col-md-6">
            <form action="{{route('statistik.profit-harian')}}" method="get">
                <div class="row">
                    <div class="col-md-4 my-3">
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
                    <div class="col-md-4 my-3">
                        <select class="form-select" name="tahun" id="tahun">
                            @foreach ($dataTahun as $d)
                            <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 my-3">
                        <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
                    </div>
                </div>
            </form>
        </div> --}}
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover" >
            <thead class="table-primary">
                <tr>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">Tambang</th>
                    <th class="text-center align-middle">Dr Tgl Tagihan</th>
                    <th class="text-center align-middle">Smpai Tgl Tagihan</th>
                    <th class="text-center align-middle">Nominal Tagihan</th>
                    <th class="text-center align-middle">Estimasi Nominal Invoice</th>
                    <th class="text-center align-middle">Penyesuaian</th>
                    <th class="text-center align-middle">Penalti</th>
                    <th class="text-center align-middle">Pembayaran Invoice</th>
                    <th class="text-center align-middle">Periode</th>
                    <th class="text-center align-middle">Tgl Submit Softcopy</th>
                    <th class="text-center align-middle">Tgl Submit Hardcopy</th>
                    <th class="text-center align-middle">Estimasi Tgl Pembayaran</th>
                    <th class="text-center align-middle">No Resi</th>
                    <th class="text-center align-middle">No Validasi</th>
                </tr>
            </thead>
            <tbody>
                @if (count($data) > 0)
                    @php
                        $no = 1;
                        $totalTagihan = 0;
                        $totalInvoice = 0;
                        $grandTotal = 0;
                    @endphp
                    @foreach ($data as $d)
                        @php
                            $span = count($d['invoices']);
                        @endphp
                        <tr>
                            <td rowspan="{{ $span }}" class="text-center align-middle">{{ $no++ }}</td>
                            <td rowspan="{{ $span }}" class="text-center align-middle">{{ $d['customer'] }}</td>
                            <td rowspan="{{ $span }}" class="text-center align-middle">{{ $d['tanggal_awal'] }}</td>
                            <td rowspan="{{ $span }}" class="text-center align-middle">{{ $d['tanggal_akhir'] }}</td>
                            <td rowspan="{{ $span }}" class="text-end align-middle">{{ number_format($d['total_transaksi'], 0,',','.') }}</td>
                            @php
                                $totalTagihan += $d['total_transaksi'];
                                $grandTotal += $d['total_transaksi'];
                            @endphp
                            @if (count($d['invoices']) > 0)
                                @foreach ($d['invoices'] as $index => $invoice)
                                @if ($index > 0)
                                    <tr>
                                @endif
                                <td class="text-end align-middle">{{ number_format($invoice['tagihan_awal'], 0,',','.') }}</td>
                                <td class="text-end align-middle">{{ number_format($invoice['penyesuaian'], 0,',','.') }}</td>
                                <td class="text-end align-middle">{{ number_format($invoice['penalty'], 0,',','.') }}</td>
                                <td class="text-end align-middle">{{ number_format($invoice['total_tagihan'], 0,',','.') }}</td>
                                <td class="text-center align-middle">{{ $invoice['periode'] }}</td>
                                <td class="text-center align-middle">{{ $invoice['tanggal_submit_softcopy'] }}</td>
                                <td class="text-center align-middle">{{ $invoice['tanggal_hardcopy'] ?? '-' }}</td>
                                <td class="text-center align-middle">{{ $invoice['estimasi_pembayaran'] ?? '-' }}</td>
                                <td class="text-center align-middle">{{ $invoice['no_resi'] ?? '-' }}</td>
                                <td class="text-center align-middle">{{ $invoice['no_validasi'] ?? '-' }}</td>
                                @if ($index > 0)
                                    </tr>
                                @endif
                                @php
                                    $totalInvoice += $invoice['total_tagihan'];
                                    $grandTotal += $invoice['total_tagihan'];
                                @endphp
                            @endforeach
                            @else
                                <td class="text-end align-middle">0</td>
                                <td class="text-end align-middle">0</td>
                                <td class="text-end align-middle">0</td>
                                <td class="text-end align-middle">0</td>
                                <td class="text-center align-middle">-</td>
                                <td class="text-center align-middle">-</td>
                                <td class="text-center align-middle">-</td>
                                <td class="text-center align-middle">-</td>
                                <td class="text-center align-middle">-</td>
                                <td class="text-center align-middle">-</td>
                            @endif

                        </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-start align-middle">Total</th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle">{{number_format($totalTagihan, 0, ',','.')}}</th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle">{{number_format($totalInvoice, 0, ',','.')}}</th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
                </tr>
                <tr>
                    <th colspan="2" class="text-start align-middle">Grand Total</th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
                    <th colspan="5" class="text-center align-middle">{{number_format($grandTotal, 0, ',','.')}}</th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
                    <th class="text-end align-middle"></th>
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
