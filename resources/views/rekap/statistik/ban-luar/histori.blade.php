@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Riwayat Ban Luar</h3>
            <p class="text-muted mb-0">Posisi: <strong class="text-primary">{{ $posisi->nama }}</strong> | Unit: <strong class="text-primary">SAI{{ $vehicle->nomor_lambung }}</strong></p>
        </div>
        <div>
            <form action="{{ route('statistik.ban-luar') }}" method="get">
                <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                <button class="btn btn-outline-secondary btn-sm" type="submit">
                    <img src="{{ asset('images/back.svg') }}" width="18" alt="Kembali"> Kembali
                </button>
            </form>
        </div>
    </div>

    @include('swal')

    <!-- Modal Delete / Password -->
    <div class="modal fade" id="passwordModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="passwordForm">
                    @csrf
                    <div class="modal-body py-4">
                        <p class="text-muted mb-3">Masukkan password Anda untuk mengonfirmasi penghapusan data ini.</p>
                        <input class="form-control" type="password" id="password" name="password" placeholder="Password Anda" required>
                        <input type="hidden" id="itemId" name="itemId">
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Tanggal -->
    <div class="modal fade" id="editModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Ubah Tanggal Ganti Ban</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="updateForm">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label for="created_at" class="form-label font-weight-bold">Tanggal Ganti Ban</label>
                            <input type="text" class="form-control" name="created_at" id="created_at" required readonly />
                        </div>
                        <div class="mb-3">
                            <label for="password_edit" class="form-label font-weight-bold">Password Konfirmasi</label>
                            <input class="form-control" type="password" id="password_edit" name="password" placeholder="Password" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabel Histori Data -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="rekapTable" style="width: 100%;">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center">MEREK</th>
                            <th class="text-center">NO. SERI BAN</th>
                            <th class="text-center">KONDISI BAN</th>
                            <th class="text-center">RITASE</th>
                            <th class="text-center">TGL GANTI BAN</th>
                            @if (auth()->user()->role == 'admin' || auth()->user()->role == 'su')
                            <th class="text-center" style="width: 150px;">ACTION</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody></tbody>
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
<link rel="stylesheet" href="{{ asset('assets/js/flatpickr/flatpickr.min.css') }}">
@endpush

@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script src="{{ asset('assets/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/flatpickr/flatpickr.js') }}"></script>
<script>
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


        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            $('#itemId').val(id);
            $('#passwordForm').attr('action', "{{ route('statistik.ban-luar.histori-destroy', ['histori' => ':id']) }}".replace(':id', id));
        });

        $(document).on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            var createdAt = $(this).data('created-at');
            $('#updateForm').attr('action', "{{ route('statistik.ban-luar.histori-update', ['histori' => ':id']) }}".replace(':id', id));
            $('#created_at').val(createdAt);

            flatpickr("#created_at", {
                enableTime: false,
                dateFormat: "d-m-Y",
            });
        });

        var userRole = "{{ auth()->user()->role }}";

        var columns = [
            { data: 'merk', name: 'merk', class: "text-center" },
            { data: 'no_seri', name: 'no_seri', class: "text-center fw-semibold" },
            {
                data: 'kondisi',
                name: 'kondisi',
                class: "text-center",
                render: function (data) {
                    var val = parseInt(data);
                    var colorClass = val > 70 ? 'bg-success' : (val > 40 ? 'bg-warning text-dark' : 'bg-danger');
                    return '<span class="badge ' + colorClass + '">' + val + '%</span>';
                }
            },
            {
                data: 'ritase',
                name: 'ritase',
                class: "text-center font-monospace fw-bold",
                render: function (data, type, row) {
                    // row.id mengambil ID ban_logs dari server response
                    var formatted = data ? parseFloat(data).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) : '0';
                    return '<a href="javascript:void(0)" class="text-primary text-decoration-underline show-transaksi" data-id="' + row.id + '">' + formatted + '</a>';
                }
            },
            {
                data: 'created_at',
                name: 'created_at',
                class: "text-center",
                render: function (data) {
                    return moment(data).format('DD-MM-YYYY');
                }
            }
        ];

        if (userRole === 'admin' || userRole === 'su') {
            columns.push({
                data: null,
                name: 'ACT',
                class: "text-center",
                render: function (data, type, row) {
                    return '<div class="btn-group btn-group-sm" role="group">' +
                           '<button class="btn btn-outline-primary edit-btn" data-id="' + row.id + '" data-created-at="' + moment(row.created_at).format('DD-MM-YYYY') + '" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fa fa-edit"></i> Edit</button>' +
                           '<button class="btn btn-outline-danger delete-btn" data-id="' + row.id + '" data-bs-toggle="modal" data-bs-target="#passwordModal"><i class="fa fa-trash"></i> Hapus</button>' +
                           '</div>';
                }
            });
        }

        $('#rekapTable').DataTable({
            'processing': true,
            'serverSide': true,
            'searching': true,
            'ordering': true,
            'ajax': {
                'url': "{{ route('statistik.ban-luar.histori-data') }}",
                'data': {
                    'vehicle': '{{ $vehicle->id }}',
                    'posisi': '{{ $posisi->id }}'
                },
                'type': 'GET',
            },
            'columns': columns
        });
    });
</script>
@endpush
