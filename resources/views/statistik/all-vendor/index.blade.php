@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header Judul --}}
    <div class="row justify-content-center mb-4">
        <div class="col-md-12 text-center">
            <h2 class="fw-bold"><u>PERFORM UNIT</u></h2>
            {{-- Menampilkan Nama Bulan dan Tahun dari Controller --}}
            <h3>BULAN {{ Str::upper($nama_bulan) ?? '' }} {{ $tahun }}</h3>
        </div>
    </div>

    @include('swal')

    {{-- Navigasi & Filter Utama --}}
    <div class="row justify-content-between mt-3">
        {{-- Bagian Kiri: Menu --}}
        <div class="col-md-5">
            <div class="d-flex gap-3 align-items-center">
                <a href="{{ route('home') }}" class="text-decoration-none text-dark">
                    <img src="{{ asset('images/dashboard.svg') }}" alt="dashboard" width="30" class="me-1"> Dashboard
                </a>
                @if (auth()->user()->role != 'operasional')
                <a href="{{ route('statisik.index') }}" class="text-decoration-none text-dark">
                    <img src="{{ asset('images/statistik.svg') }}" alt="dokumen" width="30" class="me-1"> STATISTIK
                </a>
                @endif

                 <a href="{{ route('statistik.perform-unit.all-vendor.pdf', ['bulan' => $bulan_angka, 'tahun' => $tahun, 'vendor' => isset($vendor) ? $vendor : '']) }}" target="_blank" class="text-decoration-none text-dark">
                    <img src="{{ asset('images/document.svg') }}" alt="dokumen" width="30" class="me-1"> PDF
                </a>
            </div>
        </div>

        {{-- Bagian Kanan: Filter Bulan & Tahun --}}
        <div class="col-md-7">
            <form action="{{ url()->current() }}" method="get">
                <div class="row g-2 justify-content-end">
                    <div class="col-md-4">
                        <select class="form-select" name="bulan" id="bulan">
                            @foreach([
                            '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                            '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                            '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                            ] as $key => $val)
                            <option value="{{ $key }}" {{ (isset($bulan_angka) && $bulan_angka==$key) ? 'selected' : ''
                                }}>
                                {{ $val }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="tahun" id="tahun">
                            @foreach ($dataTahun as $d)
                            <option value="{{ $d->tahun }}" {{ $d->tahun == $tahun ? 'selected' : '' }}>
                                {{ $d->tahun }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Input Hidden untuk menjaga filter vendor saat ganti bulan --}}
                    @if(isset($vendor) && $vendor != '')
                    <input type="hidden" name="vendor" value="{{ $vendor }}">
                    @endif

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100" id="btn-cari">
                            <i class="fa fa-search"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <hr>

    {{-- Filter Khusus Vendor --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <form action="{{ url()->current() }}" method="get">
                <label for="vendor" class="form-label fw-bold">Filter Vendor</label>
                <div class="input-group">
                    {{-- Bawa serta filter Bulan/Tahun agar tidak reset saat ganti vendor --}}
                    <input type="hidden" name="bulan" value="{{ $bulan_angka }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <select class="form-select" name="vendor" id="vendor">
                        <option value="">-- Semua Vendor --</option>
                        @foreach ($vendors as $v)
                        <option value="{{ $v->id }}" {{ (isset($vendor) && $v->id == $vendor) ? 'selected' : '' }}>
                            {{ $v->nama }}
                        </option>
                        @endforeach
                    </select>
                    <button class="btn btn-success" type="submit">Filter</button>
                    <a href="{{ route('statistik.perform-unit.all-vendor', ['bulan' => $bulan_angka, 'tahun' => $tahun]) }}"
                        class="btn btn-outline-secondary" type="button">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <div style="font-size: 12px" class="table-responsive shadow-sm p-2 mb-5 bg-body rounded">
        <table class="table table-bordered table-hover align-middle" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center" width="5%">No</th>
                    <th class="text-center">Vendor</th>
                    <th class="text-center">Vehicle (Lambung)</th>
                    <th class="text-center">Rute Pendek<br><small>(< 50 km)</small>
                    </th>
                    <th class="text-center">Rute Panjang<br><small>(≥ 50 km)</small></th>
                    <th class="text-center">Total Rute</th>
                </tr>
            </thead>
            <tbody>
                @php
                $grandTotalPendek = 0;
                $grandTotalPanjang = 0;
                $grandTotalAll = 0;
                $previousVendor = null;
                @endphp

                {{-- HANYA LOOP JIKA ADA DATA --}}
                @if(count($data) > 0)
                @foreach ($data as $row)
                @php
                // Logic CSS Class Vendor Baru
                $isNewVendor = ($previousVendor !== null && $previousVendor !== $row->vendor_name);
                $previousVendor = $row->vendor_name;

                // Hitungan
                $totalRow = $row->total_rute_pendek + $row->total_rute_panjang;
                $grandTotalPendek += $row->total_rute_pendek;
                $grandTotalPanjang += $row->total_rute_panjang;
                $grandTotalAll += $totalRow;

                $textClass = ($totalRow == 0) ? 'text-danger fw-bold' : 'text-dark';
                $rowClass = $isNewVendor ? 'vendor-separator' : '';
                @endphp

                <tr class="{{ $textClass }} {{ $rowClass }}">
                    <td class="text-center {{ $textClass }}">{{ $loop->iteration }}</td>
                    <td class="fw-bold {{ $textClass }}">{{ $row->vendor_name }}</td>
                    <td class="text-center {{ $textClass }}">{{ $row->nomor_lambung }}</td>
                    <td class="text-center {{ $textClass }}">{{ number_format($row->total_rute_pendek, 0, ',', '.') }}
                    </td>
                    <td class="text-center {{ $textClass }}">{{ number_format($row->total_rute_panjang, 0, ',', '.') }}
                    </td>
                    <td class="text-center fw-bold bg-light bg-opacity-50 {{ $textClass }}">
                        {{ number_format($totalRow, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
                @endif
                {{-- JIKA KOSONG, JANGAN TULIS APAPUN DI SINI (Biarkan DataTables yang handle) --}}
            </tbody>

            {{-- Footer hanya muncul jika ada data --}}
            @if(count($data) > 0)
            <tfoot class="table-secondary fw-bold border-top-2">
                <tr>
                    <td colspan="3" class="text-end text-uppercase px-3">Grand Total Keseluruhan</td>
                    <td class="text-center">{{ number_format($grandTotalPendek, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($grandTotalPanjang, 0, ',', '.') }}</td>
                    <td class="text-center bg-warning bg-opacity-25 text-dark">
                        {{ number_format($grandTotalAll, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection

@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
<style>
    /* Style tabel dasar */
    #rekapTable thead th {
        vertical-align: middle;
        font-weight: 600;
    }

    /* --- MAGIC CLASS UNTUK PEMISAH VENDOR --- */
    /* Ini memberikan border tebal di atas baris saat vendor berganti */
    tr.vendor-separator>td {
        border-top: 20px solid #e9ecef;
        /* Jarak kosong warna putih */

        /* OPSI LAIN: Jika ingin garis abu-abu tebal sebagai pemisah: */
        /* border-top: 20px solid #e9ecef; */

        position: relative;
        /* Agar border render dengan benar */
    }

    /* Memastikan border tabel default tidak bentrok */
    .table-bordered> :not(caption)>*>* {
        border-width: 0 1px;
        /* Reset border atas bawah default */
    }

    .table-bordered>tbody>tr>td {
        border-bottom: 1px solid #dee2e6;
        /* Kembalikan border bawah tipis */
    }
</style>
@endpush

@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script>
    $(document).ready(function() {
            // Simpan URL gambar dari Blade ke variabel JS agar bisa dipakai di string
            var imgUrl = "{{ asset('images/statistik.svg') }}";

            // Buat HTML pesan kosong
            var emptyMessage = `
                <div class="text-center py-5 text-muted">
                    <img src="${imgUrl}" width="50" class="mb-3 opacity-50"><br>
                    <i>Tidak ada data transaksi ditemukan untuk periode ini.</i>
                </div>
            `;

            if ($.fn.DataTable.isDataTable('#rekapTable')) {
                $('#rekapTable').DataTable().destroy();
            }

            $('#rekapTable').DataTable({
                "searching": false,
                "paging": false,
                "info": true,
                "ordering": false,
                "scrollCollapse": true,
                "scrollY": "550px",
                "responsive": true,
                "dom": 't',
                "language": {
                    // Masukkan HTML pesan kosong di sini
                    "emptyTable": emptyMessage,
                    "zeroRecords": emptyMessage,
                    "info": "Menampilkan _TOTAL_ data unit vehicle",
                    "infoEmpty": "Tidak ada data"
                }
            });
        });
</script>
@endpush
