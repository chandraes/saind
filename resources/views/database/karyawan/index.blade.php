@extends('layouts.app')
@section('content')
<div class="container employee-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><div class="small text-success fw-bold text-uppercase mb-1">Database perusahaan</div><h2 class="fw-bold mb-1">Manajemen Pegawai</h2><p class="text-muted mb-0">Kelola biodata, jabatan, dan status kepegawaian.</p></div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('database') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Database</a>
            <a href="{{ route('karyawan.create') }}" class="btn btn-success"><i class="fa fa-plus me-1"></i> Tambah Pegawai</a>
        </div>
    </div>
    @include('swal')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div><h5 class="fw-bold mb-2">Daftar Pegawai</h5><div class="d-flex flex-wrap gap-2"><button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#position-modal"><i class="fa fa-briefcase me-1"></i> Kelola Jabatan</button><a href="{{ route('attendance-integration.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-refresh me-1"></i> Integrasi Absensi</a></div></div>
                <div style="width:220px;max-width:100%"><label for="employee-status" class="form-label small fw-bold">Status pegawai</label><select id="employee-status" class="form-select">@foreach (['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'semua' => 'Semua status'] as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select></div>
            </div>
            <p class="small text-muted"><i class="fa fa-mouse-pointer me-1"></i> Gulir tabel untuk melihat pegawai lainnya. Data dimuat bertahap.</p><div class="table-responsive"><table id="karyawan-data" class="table table-hover align-middle w-100"><thead class="table-success"><tr><th>No</th><th>Nama</th><th>Nama Panggilan</th><th>Jabatan</th><th>Status</th><th class="text-center">Aksi</th></tr></thead><tbody></tbody></table></div>
        </div>
    </div>
    <div class="modal fade" id="position-modal" tabindex="-1" aria-labelledby="position-title" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg"><div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-success bg-opacity-10"><div><h5 class="modal-title fw-bold" id="position-title">Kelola Jabatan</h5><p class="small text-muted mb-0">Jabatan yang sedang digunakan tidak dapat dihapus.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body p-4">
                <form action="{{ route('karyawan.jabatan-store') }}" method="POST" class="position-form mb-4" data-confirm="Tambahkan jabatan ini?">@csrf<label for="position-name" class="form-label fw-bold">Jabatan baru</label><div class="input-group"><input id="position-name" name="nama_jabatan_tambah" class="form-control" placeholder="Nama jabatan" required maxlength="255"><button type="submit" class="btn btn-success"><i class="fa fa-plus me-1"></i> Tambah</button></div></form>
                <div class="table-responsive"><table class="table align-middle"><thead class="table-success"><tr><th>No</th><th>Nama Jabatan</th><th class="text-end">Aksi</th></tr></thead><tbody>
                    @forelse ($jabatan as $position)
                        <tr><td>{{ $loop->iteration }}</td><td><form id="position-form-{{ $position->id }}" action="{{ route('karyawan.jabatan-update', $position) }}" method="POST" class="position-form" data-confirm="Simpan perubahan jabatan?">@csrf @method('PATCH')<label for="position-{{ $position->id }}" class="visually-hidden">Nama jabatan {{ $position->nama }}</label><input id="position-{{ $position->id }}" name="nama_jabatan" class="form-control" value="{{ $position->nama }}" required maxlength="255"></form></td><td class="text-end text-nowrap"><button form="position-form-{{ $position->id }}" type="submit" class="btn btn-sm btn-outline-success" aria-label="Simpan jabatan {{ $position->nama }}"><i class="fa fa-check"></i></button><button type="button" class="btn btn-sm btn-outline-danger position-delete" data-url="{{ route('karyawan.jabatan-destroy', $position) }}" data-name="{{ $position->nama }}" aria-label="Hapus jabatan {{ $position->nama }}"><i class="fa fa-trash-o"></i></button></td></tr>
                    @empty<tr><td colspan="3" class="text-center text-muted py-4">Belum ada jabatan. Tambahkan jabatan pertama.</td></tr>@endforelse
                </tbody></table></div>
            </div>
        </div></div>
    </div>
</div>
@endsection
@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
<link href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.bootstrap5.min.css" rel="stylesheet">
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet">
<style>.employee-page .table th {white-space:nowrap;padding:1rem .75rem}.employee-page .table td{padding:.85rem .75rem;white-space:nowrap}.employee-page .dataTables_scrollBody{border:1px solid #e7eee9;border-radius:.5rem}.employee-page .select2-selection--single{min-height:38px;border-color:#ced4da!important}.employee-page .select2-selection__rendered{line-height:36px!important}.employee-page .select2-selection__arrow{height:36px!important}</style>
@endpush
@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
$(function () {
    const csrf = $('meta[name="csrf-token"]').attr('content');
    const errorMessage = xhr => xhr.status === 419 ? 'Sesi berakhir. Muat ulang halaman lalu coba kembali.' : xhr.status >= 500 ? 'Terjadi gangguan server. Silakan coba kembali.' : xhr.responseJSON && xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors).flat().join('\n') : xhr.responseJSON && xhr.responseJSON.message || 'Periksa koneksi Anda lalu coba kembali.';
    const showError = xhr => Swal.fire({icon:'error', title:'Tidak berhasil', text:errorMessage(xhr)});
    const table = $('#karyawan-data').DataTable({
        processing:true, serverSide:true, deferRender:true, searchDelay:500, pageLength:25, dom:'frti', scrollY:'480px', scrollX:true, scrollCollapse:true, scroller:{displayBuffer:3,serverWait:350}, order:[[1,'asc']],
        ajax:{url:@json(route('karyawan.index')), data: data => { data.status = $('#employee-status').val(); }, error:showError},
        columns:[{data:'DT_RowIndex',orderable:false,searchable:false,className:'text-center'},{data:'nama'},{data:'nickname'},{data:'jabatan',defaultContent:'—'},{data:'status',render: value => '<span class="badge rounded-pill ' + (value === 'aktif' ? 'bg-success' : 'bg-secondary') + ' bg-opacity-10 ' + (value === 'aktif' ? 'text-success' : 'text-secondary') + ' px-3 py-2">' + (value === 'aktif' ? 'Aktif' : 'Nonaktif') + '</span>'},{data:'action',orderable:false,searchable:false}],
        language:{processing:'Memuat pegawai…',search:'Cari:',searchPlaceholder:'Nama atau jabatan…',lengthMenu:'Tampilkan _MENU_ pegawai',info:'Menampilkan _START_–_END_ dari _TOTAL_ pegawai',infoEmpty:'Belum ada pegawai',infoFiltered:'(disaring dari _MAX_ pegawai)',emptyTable:'Belum ada pegawai untuk status ini.',zeroRecords:'Tidak ada pegawai yang sesuai pencarian.',paginate:{previous:'Sebelumnya',next:'Berikutnya'}}
    });
    $('#employee-status').select2({width:'100%'}).on('change', () => {
        const url = new URL(window.location.href); url.searchParams.set('status', $('#employee-status').val()); window.history.replaceState(null, '', url);
        table.ajax.reload();
    });
    $(document).on('click', '.employee-delete, .position-delete', async function () {
        const button = $(this); const isPosition = button.hasClass('position-delete');
        const result = await Swal.fire({icon:'warning',title:isPosition ? 'Hapus jabatan?' : 'Hapus pegawai?',text:'Hapus "' + button.data('name') + '"? Tindakan ini tidak dapat dibatalkan.',showCancelButton:true,confirmButtonText:'Ya, hapus',cancelButtonText:'Batal',confirmButtonColor:'#dc3545'});
        if (!result.isConfirmed) return;
        button.prop('disabled',true);
        $.ajax({url:button.data('url'),method:'DELETE',headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json'}})
            .done(response => {
                Swal.fire({icon:'success',title:'Berhasil',text:response.message}).then(() => {
                    if(isPosition) window.location.reload();
                    else {table.ajax.reload();}
                });
            }).fail(showError).always(() => button.prop('disabled',false));
    });
    $('.position-form').on('submit', async function (event) {
        event.preventDefault(); const form = this; if($(form).data('busy')) return;
        const result = await Swal.fire({icon:'question',title:$(form).data('confirm'),showCancelButton:true,confirmButtonText:'Ya, simpan',cancelButtonText:'Batal',confirmButtonColor:'#198754'});
        if(!result.isConfirmed || $(form).data('busy')) return;
        $(form).data('busy',true);
        $.ajax({url:form.action,method:'POST',data:$(form).serialize(),headers:{Accept:'application/json'}})
            .done(response => Swal.fire({icon:'success',title:'Berhasil',text:response.message}).then(() => window.location.reload()))
            .fail(showError).always(() => $(form).data('busy',false));
    });
});
</script>
@endpush
