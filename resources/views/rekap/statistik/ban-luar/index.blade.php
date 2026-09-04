@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Statistik Ban Luar</h3>
            <p class="text-muted mb-0">Kelola dan pantau pemakaian ban unit kendaraan</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <img src="{{ asset('images/dashboard.svg') }}" width="18" alt="Dashboard"> Dashboard
            </a>
            @if (auth()->user()->role != 'asisten-user')
            <a href="{{ route('rekap.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <img src="{{ asset('images/rekap.svg') }}" width="18" alt="Rekap"> Rekap
            </a>
            <a href="{{ route('statisik.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <img src="{{ asset('images/statistik.svg') }}" width="18" alt="Statistik"> Statistik
            </a>
            @endif
        </div>
    </div>

    @include('swal')
    {{-- @include('rekap.statistik.ban-luar.tambah') --}}

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
        <strong class="d-block mb-1">Terjadi kesalahan validation:</strong>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Card Informasi Kendaraan -->
    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body">
            <div class="row align-items-center text-center text-md-start">
                <div class="col-md-4 border-end-md mb-2 mb-md-0">
                    <span class="text-muted fs-7 d-block">Nomor Lambung</span>
                    <span class="fw-bold fs-5 text-primary">SAI{{ $vehicle->nomor_lambung }}</span>
                </div>
                <div class="col-md-4 border-end-md mb-2 mb-md-0">
                    <span class="text-muted fs-7 d-block">Nama Driver</span>
                    <span class="fw-bold fs-6">{{ $vehicle->nama_driver ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted fs-7 d-block">Pengurus</span>
                    <span class="fw-bold fs-6">{{ $vehicle->pengurus ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Data Ban -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="rekapTable">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center" style="width: 50px;">NO</th>
                            <th>POSISI BAN</th>
                            <th class="text-center">MEREK</th>
                            <th class="text-center">NO. SERI BAN</th>
                            <th class="text-center">JENIS BAN</th>
                            <th class="text-center">KONDISI</th>
                            <th class="text-center">RITASE</th>
                            <th class="text-center">TGL GANTI BAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ban as $d)
                        <tr>
                            <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                            <td class="fw-semibold">
                                @if (auth()->user()->role == 'admin' || auth()->user()->role == 'su')
                                {{-- <a href="#" class="text-decoration-none text-primary" data-bs-toggle="modal" data-bs-target="#tambahModal" onclick="tambah({{ $d }})"> --}}
                                    <i class="fa fa-plus-circle me-1"></i>{{ $d->nama }}
                                {{-- </a> --}}
                                @else
                                {{ $d->nama }}
                                @endif
                            </td>
                            <td class="text-center">{{ $d->banLog['merk'] ?? '-' }}</td>
                            <td class="text-center">{{ $d->banLog['no_seri'] ?? '-' }}</td>
                            <td class="text-center"><span class="badge bg-secondary opacity-75">{{ $d->jenis }}</span></td>
                            <td class="text-center">
                                @if ($d->banLog)
                                    @php
                                        $kondisi = (int)$d->banLog['kondisi'];
                                        $badgeColor = $kondisi > 70 ? 'bg-success' : ($kondisi > 40 ? 'bg-warning text-dark' : 'bg-danger');
                                    @endphp
                                    <span class="badge {{ $badgeColor }}">{{ $kondisi }}%</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center font-monospace fw-bold">
                                @if (isset($d->banLog['ritase']))
                                    <a href="javascript:void(0)" class="text-decoration-underline text-primary show-transaksi" data-id="{{ $d->banLog['id'] }}">
                                        {{ number_format($d->banLog['ritase'], 1, ',', '.') }}
                                    </a>
                                @else
                                    0
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($d->banLog)
                                <a href="{{ route('statistik.ban-luar.histori', ['vehicle' => $vehicle->id, 'posisi' => $d->id]) }}" class="fw-bold px-2 py-1">
                                    {{ $d->banLog['tanggal_ganti'] }}
                                </a>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi Ritase -->
    <div class="modal fade" id="modalDetailRitase" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fa fa-list me-2"></i> Detail Histori Transaksi Ritase</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0 text-center">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>TANGGAL</th>
                                    <th>NO. UJ</th>
                                    <th>RUTE</th>
                                    <th>JARAK (KM)</th>
                                    <th>+ RITASE</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDetailRitase">
                                <!-- Data akan diisi via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
@endpush

@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script>
    function tambah(data) {
        document.getElementById('posisi_ban_id').value = data.id;
        document.getElementById('tambahTitle').innerHTML = "Ganti Ban - " + data.nama;
    }

    $(document).ready(function(){

        $(document).on('click', '.show-transaksi', function() {
            var banLogId = $(this).data('id');
            var tbody = $('#tbodyDetailRitase');

            tbody.html('<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><br>Mengambil data...</td></tr>');
            $('#modalDetailRitase').modal('show');

            $.ajax({
                url: "/statistik/ban-luar/transaksi-ritase/" + banLogId, // Sesuaikan jika ada prefix URL
                type: "GET",
                success: function(response) {
                    if(response.length === 0) {
                        tbody.html('<tr><td colspan="5" class="text-center text-muted py-3">Belum ada histori transaksi untuk ban ini.</td></tr>');
                        return;
                    }

                    var html = '';
                    $.each(response, function(index, trx) {
                        // Format tanggal sederhana
                        var date = new Date(trx.tanggal);
                        var dmy = ("0" + date.getDate()).slice(-2) + "-" + ("0"+(date.getMonth()+1)).slice(-2) + "-" + date.getFullYear();

                        html += '<tr>';
                        html += '  <td>' + dmy + '</td>';
                        html += '  <td class="fw-bold text-primary">UJ' + String(trx.nomor_uang_jalan).padStart(2, '0') + '</td>';
                        html += '  <td>' + trx.rute + '</td>';
                        html += '  <td>' + trx.jarak + '</td>';
                        html += '  <td class="fw-bold text-success">+' + parseFloat(trx.nilai_ritase).toFixed(1) + '</td>';
                        html += '</tr>';
                    });
                    tbody.html(html);
                },
                error: function() {
                    tbody.html('<tr><td colspan="5" class="text-center text-danger py-3">Gagal mengambil data. Silakan coba lagi.</td></tr>');
                }
            });
        });


        $('#rekapTable').DataTable({
            "searching": true,
            "info": false,
            "responsive": true,
            "paging": false,
            "ordering": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });

        $('#createForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            });
        });
    });
</script>
@endpush
