@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Page -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Riwayat Aki</h3>
            <p class="text-muted mb-0">Posisi: <strong class="text-primary">{{ $posisi->nama }}</strong> | Unit: <strong class="text-primary">{{ $vehicle->nomor_lambung }}</strong></p>
        </div>
        <div>
            <form action="{{ route('statistik.aki') }}" method="get">
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
                    <h5 class="modal-title">Ubah Tanggal Ganti Aki</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="updateForm">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label for="created_at" class="form-label font-weight-bold">Tanggal Ganti Aki</label>
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

    <div class="alert alert-light border shadow-sm mb-4"><i class="fa fa-info-circle text-primary me-2"></i>Umur aki dihitung dari tanggal pemasangan sampai penggantian berikutnya. Untuk aki yang masih digunakan, umur dihitung sampai hari ini.</div>
    <!-- Tabel Histori Data -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white p-3"><h5 class="fw-bold mb-1">Riwayat penggantian</h5><p class="text-muted small mb-0">Urutan terbaru ditampilkan terlebih dahulu.</p></div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="rekapTable" style="width: 100%;">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center">MEREK</th>
                            <th class="text-center">KONDISI AKI</th>
                            <th class="text-center">TANGGAL GANTI</th>
                            <th class="text-center">UMUR AKI</th>
                            <th class="text-center">STATUS / AKHIR PEMAKAIAN</th>
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
        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            $('#itemId').val(id);
            $('#passwordForm').attr('action', "{{ route('statistik.aki.histori-destroy', ['histori' => ':id']) }}".replace(':id', id));
        });

        $(document).on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            var createdAt = $(this).data('created-at');
            $('#updateForm').attr('action', "{{ route('statistik.aki.histori-update', ['histori' => ':id']) }}".replace(':id', id));
            $('#created_at').val(createdAt);

            flatpickr("#created_at", {
                enableTime: false,
                dateFormat: "d-m-Y",
            });
        });

        var userRole = "{{ auth()->user()->role }}";

        var columns = [
            { data: 'merk', name: 'merk', class: "text-center text-uppercase", render: $.fn.dataTable.render.text() },
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
                data: 'created_at',
                name: 'created_at',
                class: "text-center",
                render: function (data) {
                    return moment(data).format('DD-MM-YYYY');
                }
            },
            { data: 'umur_hari', class: 'text-center text-nowrap fw-semibold', orderable: false, searchable: false,
                render: function (data) { return data + ' hari'; } },
            { data: 'tanggal_selesai', class: 'text-center text-nowrap', orderable: false, searchable: false,
                render: function (data) {
                    return data ? '<span class="text-muted">Diganti ' + moment(data).format('DD-MM-YYYY') + '</span>' : '<span class="badge bg-primary">Masih digunakan</span>';
                } }
        ];

        if (userRole === 'admin' || userRole === 'su') {
            columns.push({
                data: null,
                name: 'ACT',
                orderable: false,
                searchable: false,
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
                'url': "{{ route('statistik.aki.histori-data') }}",
                'data': {
                    'vehicle': '{{ $vehicle->id }}',
                    'posisi': '{{ $posisi->id }}'
                },
                'type': 'GET',
            },
            'order': [[2, 'desc']],
            'language': { search: 'Cari merek:', lengthMenu: 'Tampilkan _MENU_ data', info: '_START_–_END_ dari _TOTAL_ riwayat', infoEmpty: 'Belum ada riwayat', infoFiltered: '(dari _MAX_ riwayat)', zeroRecords: 'Riwayat tidak ditemukan', emptyTable: 'Belum ada riwayat penggantian aki', processing: 'Memuat riwayat...', paginate: { next: 'Berikutnya', previous: 'Sebelumnya' } },
            'columns': columns
        });
    });
</script>
@endpush
