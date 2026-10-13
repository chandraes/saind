@extends('layouts.app')
@section('content')
<div class="container attendance-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><div class="small text-success fw-bold text-uppercase mb-1">Manajemen pegawai</div><h2 class="fw-bold mb-1">Integrasi Absensi</h2><p class="text-muted mb-0">Pantau pengiriman data pegawai dan baca hasil absensi dalam satu tempat.</p></div>
        <a href="{{ route('karyawan.index') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Kembali ke Pegawai</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-lg-6"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4 d-flex gap-3 align-items-center"><div class="status-icon {{ $ready && $configured ? 'text-success bg-success' : 'text-warning bg-warning' }} bg-opacity-10"><i class="fa fa-plug"></i></div><div><div class="small text-muted mb-1">Status integrasi</div><h5 class="fw-bold mb-1">{{ $ready && $configured ? 'Penghubung aktif' : 'Penghubung belum siap' }}</h5><div class="small text-muted">{{ $ready && $configured ? 'Data pegawai siap dikirim ke aplikasi absensi.' : 'Periksa migrasi, URL HTTPS, dan token pada konfigurasi integrasi.' }}</div></div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4"><div class="small text-muted">Tersinkron</div><div class="fs-2 fw-bold text-success">{{ number_format($synced, 0, ',', '.') }}</div><div class="small text-muted">Data pegawai sudah terkirim</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4"><div class="small text-muted">Menunggu pengiriman</div><div class="fs-2 fw-bold {{ $pending ? 'text-warning' : 'text-success' }}">{{ number_format($pending, 0, ',', '.') }}</div><div class="small text-muted">Perubahan belum terkirim</div></div></div></div>
    </div>
    <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3"><div><h5 class="fw-bold mb-1">Pengiriman Data Pegawai</h5><p class="small text-muted mb-0">Perubahan dikirim setelah disimpan. Pengiriman yang gagal akan dicoba ulang secara otomatis.</p></div><form id="sync-form" method="post" action="{{ route('attendance-integration.sync') }}">@csrf<button class="btn btn-success" @disabled(!$ready || !$configured)><i class="fa fa-refresh me-1"></i> Sinkronkan Sekarang</button></form></div>
        <div class="table-responsive"><table id="sync-table" class="table table-hover align-middle w-100"><thead class="table-success"><tr><th>No</th><th>Pegawai</th><th>Status Pegawai</th><th>Pengiriman</th><th>Terakhir Berhasil</th><th>Keterangan</th></tr></thead><tbody>
            @foreach($syncs ?? [] as $sync)<tr><td>{{ $loop->iteration }}</td><td>{{ $sync->payload['employee_number'] ?? $sync->source_employee_id }} · {{ $sync->payload['name'] ?? '—' }}</td><td>{{ ($sync->payload['status'] ?? '') === 'active' ? 'Aktif' : 'Nonaktif' }}</td><td>{{ $sync->source_version === $sync->delivered_version ? 'Tersinkron' : 'Menunggu' }}</td><td>{{ $sync->last_synced_at?->format('d/m/Y H:i') ?? '—' }}</td><td>{{ $sync->last_error ?? '—' }}</td></tr>@endforeach
        </tbody></table></div>
        <p class="small text-muted mb-0 mt-3"><i class="fa fa-info-circle me-1"></i> Gulir tabel untuk memuat data berikutnya. Pegawai yang dihapus dikirim sebagai nonaktif agar riwayat absensinya tetap tersimpan.</p>
    </div></div>
    <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-3 p-md-4">
        <h5 class="fw-bold mb-1">Hasil dari Aplikasi Absensi</h5><p class="small text-muted mb-4">Pilih jenis data dan periode yang ingin ditampilkan. Pengolahan gaji tetap mengikuti aturan SAIND.</p>
        <form id="result-form" method="get" action="{{ route('attendance-integration.index') }}" class="row g-3 mb-4">
            <div class="col-md-4"><label for="resource" class="form-label small fw-bold">Jenis data</label><select id="resource" name="resource" class="form-select"><option value="attendances" @selected(old('resource', $resource) === 'attendances')>Absensi</option><option value="leaves" @selected(old('resource', $resource) === 'leaves')>Cuti / izin</option><option value="leave-accounts" @selected(old('resource', $resource) === 'leave-accounts')>Hak dan pemakaian cuti</option></select></div>
            <div class="col-md-4"><label for="source-employee" class="form-label small fw-bold">ID pegawai SAIND <span class="text-muted fw-normal">(opsional)</span></label><input id="source-employee" name="source_employee_id" class="form-control" maxlength="100" value="{{ old('source_employee_id', request('source_employee_id')) }}" placeholder="Kosongkan untuk semua pegawai"></div>
            <div class="col-md-4 d-flex align-items-end gap-2"><button class="btn btn-success flex-grow-1" @disabled(!$ready || !$configured)><i class="fa fa-search me-1"></i> Tampilkan Hasil</button><a href="{{ route('attendance-integration.index') }}" class="btn btn-outline-secondary">Reset</a></div>
            <div class="col-md-4 attendance-dates"><label for="result-from" class="form-label small fw-bold">Dari tanggal</label><input id="result-from" type="date" name="from" class="form-control" value="{{ old('from', request('from')) }}"></div>
            <div class="col-md-4 attendance-dates"><label for="result-to" class="form-label small fw-bold">Sampai tanggal</label><input id="result-to" type="date" name="to" class="form-control" value="{{ old('to', request('to')) }}"></div>
        </form>
        @if($results)
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3"><span class="badge bg-success bg-opacity-10 text-success px-3 py-2">{{ number_format($results['total'], 0, ',', '.') }} catatan ditemukan</span><span class="small text-muted">Gulir untuk memuat hasil berikutnya.</span></div>
            <div id="results-scroll" class="table-responsive results-scroll" tabindex="0" aria-label="Daftar hasil absensi, gulir untuk memuat data berikutnya"><table id="results-table" class="table table-hover align-middle w-100"><thead class="table-success"><tr><th>ID Pegawai</th><th>Nama</th><th>Tanggal / Tahun</th><th>Status / Pemakaian</th><th>Rincian</th></tr></thead><tbody></tbody></table></div>
            <div class="d-flex align-items-center justify-content-between mt-3"><span id="results-progress" class="small text-muted" role="status" aria-live="polite"></span><button id="retry-results" type="button" class="btn btn-sm btn-outline-success d-none">Coba Lagi</button></div>
        @else
            <div class="text-center bg-light rounded-3 py-5 px-3"><i class="fa fa-calendar-check-o fa-2x text-success mb-3"></i><h6 class="fw-bold">{{ $resultError ? 'Hasil belum dapat ditampilkan' : 'Mulai dengan memilih filter' }}</h6><p class="small text-muted mb-0">{{ $resultError ?? 'Data absensi, cuti, dan hak cuti akan muncul di sini.' }}</p></div>
        @endif
    </div></div>
    <div class="small text-muted px-1 mb-4"><i class="fa fa-lock me-1"></i> Data identitas dikelola di SAIND. Akun mobile, jadwal, lokasi, dan cuti dikelola di aplikasi absensi. Password dan data keuangan tidak disinkronkan.</div>
</div>
@endsection
@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
<link href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.bootstrap5.min.css" rel="stylesheet">
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet">
<style>
.attendance-page .status-icon{width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0}
.attendance-page .table th{padding:1rem .75rem;white-space:nowrap}.attendance-page .table td{padding:.85rem .75rem;white-space:nowrap}
.attendance-page .dataTables_scrollBody,.attendance-page .results-scroll{border:1px solid #e7eee9;border-radius:.5rem}.attendance-page .results-scroll{max-height:440px}
.attendance-page .results-scroll thead th{position:sticky;top:0;z-index:1;background:#d1e7dd}.attendance-page .select2-selection--single{min-height:38px;border-color:#ced4da!important}.attendance-page .select2-selection__rendered{line-height:36px!important}.attendance-page .select2-selection__arrow{height:36px!important}
</style>
@endpush
@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
$(function () {
    const showError = xhr => Swal.fire({icon:'error',title:'Tidak berhasil',text:xhr.status === 419 ? 'Sesi berakhir. Muat ulang halaman.' : xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('\n') : xhr.responseJSON?.message || 'Data belum dapat dimuat. Periksa koneksi lalu coba kembali.'});
    const textRenderer = $.fn.dataTable.render.text();
    $('#sync-table').DataTable({
        processing:true,serverSide:true,deferRender:true,ordering:false,searching:false,dom:'rti',pageLength:25,scrollY:'400px',scrollX:true,scrollCollapse:true,scroller:{displayBuffer:3,serverWait:350},
        ajax:{url:@json(route('attendance-integration.index')),data:data => {data.table='syncs';},error:showError},
        columns:[{data:'number',render:textRenderer},{data:'employee',render:textRenderer},{data:'status',render:value => '<span class="badge rounded-pill '+(value === 'Aktif' ? 'bg-success' : 'bg-secondary')+' bg-opacity-10 '+(value === 'Aktif' ? 'text-success' : 'text-secondary')+' px-3 py-2">'+(value === 'Aktif' ? 'Aktif' : 'Nonaktif')+'</span>'},{data:'delivery',render:value => '<span class="badge rounded-pill '+(value === 'Tersinkron' ? 'bg-success text-success' : 'bg-warning text-dark')+' bg-opacity-10 px-3 py-2">'+(value === 'Tersinkron' ? 'Tersinkron' : 'Menunggu')+'</span>'},{data:'last_synced',render:textRenderer},{data:'error',render:textRenderer}],
        language:{processing:'Memuat status pengiriman…',emptyTable:'Belum ada catatan sinkronisasi. Jalankan sinkronisasi awal.',info:'Menampilkan _START_–_END_ dari _TOTAL_ pegawai',infoEmpty:'Belum ada catatan sinkronisasi'}
    });
    $('#resource').select2({width:'100%'}).on('change', function () {
        const attendance = this.value === 'attendances';
        $('.attendance-dates').toggle(attendance).find('input').prop('disabled',!attendance);
    }).trigger('change');
    $('#sync-form').on('submit', async function (event) {
        event.preventDefault(); const form=this; if(form.dataset.busy) return;
        const result=await Swal.fire({icon:'question',title:'Sinkronkan data pegawai?',text:'Data identitas pegawai akan dikirim dan pengiriman yang tertunda akan dicoba kembali.',showCancelButton:true,confirmButtonText:'Ya, sinkronkan',cancelButtonText:'Batal',confirmButtonColor:'#198754'});
        if(!result.isConfirmed || form.dataset.busy) return;
        form.dataset.busy='1'; $(form).find('button').prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-2"></span> Mengirim…'); form.submit();
    });
    $('#result-form').on('submit', function(event) {
        if($('#resource').val() === 'attendances' && $('#result-from').val() && $('#result-to').val() && $('#result-to').val() < $('#result-from').val()) {
            event.preventDefault(); Swal.fire({icon:'warning',title:'Periksa periode',text:'Tanggal sampai harus sama atau setelah tanggal dari.'}); return;
        }
        $(this).find('button').prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-2"></span> Memuat…');
    });
    @if(session('success')) Swal.fire({icon:'success',title:'Berhasil',text:@json(session('success'))}); @endif
    @if(session('error')) Swal.fire({icon:'error',title:'Tidak berhasil',text:@json(session('error'))}); @endif
    @if($errors->any()) Swal.fire({icon:'error',title:'Periksa filter',text:(@json($errors->all())).join('\n')}); @endif
    @if($resultError) Swal.fire({icon:'error',title:'Hasil belum dapat dibaca',text:@json($resultError)}); @endif
    @if($results)
    const initial=@json($results), resource=@json($resource);
    const calendarDate = value => {
        if(!value) return '—';
        const match=String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
        return match ? match[3]+'/'+match[2]+'/'+match[1] : String(value);
    };
    const dateTime = value => {
        if(!value) return '—';
        const date=new Date(value);
        return Number.isNaN(date.getTime()) ? String(value) : new Intl.DateTimeFormat('id-ID',{timeZone:@json(config('app.timezone')),day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit',hourCycle:'h23'}).format(date);
    };
    const statusLabels={complete:'Lengkap',corrected:'Dikoreksi',incomplete:'Belum lengkap',pending:'Menunggu',approved:'Disetujui',rejected:'Ditolak',cancelled:'Dibatalkan'};
    const rows = data => data.map(row => ({
        employee:row.employee?.source_employee_id ?? '—',name:row.employee?.name ?? '—',
        date:calendarDate(row.work_date ?? row.start_date ?? row.year),
        status:row.status ? (statusLabels[row.status] ?? row.status) : 'Terpakai '+(Number(row.used_grant ?? 0)+Number(row.used_carry ?? 0))+' hari',
        detail:resource === 'attendances' ? 'Masuk: '+dateTime(row.check_in_at)+' · Pulang: '+dateTime(row.check_out_at)+' · Terlambat '+(row.late_minutes ?? 0)+' menit'
            : resource === 'leaves' ? (row.type ?? '—')+' · '+(row.days ?? 0)+' hari · sampai '+calendarDate(row.end_date)
            : 'Hak '+(row.granted_days ?? 0)+' hari · carry-over '+(row.carried_days ?? 0)+' hari'
    }));
    const resultsTable=$('#results-table').DataTable({data:rows(initial.data),paging:false,ordering:false,searching:false,info:false,deferRender:true,dom:'t',columns:['employee','name','date','status','detail'].map(data => ({data,render:textRenderer})),language:{emptyTable:'Belum ada hasil untuk filter ini.'}});
    let page=initial.current_page, busy=false, finished=page >= initial.last_page, failed=false;
    const progress=() => $('#results-progress').text('Dimuat '+resultsTable.rows().count()+' dari '+initial.total+' catatan'+(busy ? ' · Memuat…' : finished ? ' · Semua hasil telah dimuat' : ''));
    const loadNext=() => {
        if(busy || finished) return;
        busy=true; failed=false; $('#retry-results').addClass('d-none'); progress();
        const params=new URLSearchParams(window.location.search); params.set('result_page',page+1); params.set('table','results');
        $.ajax({url:@json(route('attendance-integration.index'))+'?'+params.toString(),headers:{Accept:'application/json'}})
            .done(response => {resultsTable.rows.add(rows(response.data)).draw(false); page=response.current_page; finished=page >= response.last_page || !response.data.length;})
            .fail(xhr => {failed=true; $('#retry-results').removeClass('d-none'); showError(xhr);})
            .always(() => {busy=false; progress(); if(!failed && !finished && document.getElementById('results-scroll').scrollHeight <= document.getElementById('results-scroll').clientHeight) requestAnimationFrame(loadNext);});
    };
    $('#results-scroll').on('scroll', function() {if(!failed && this.scrollHeight-this.scrollTop-this.clientHeight < 80) loadNext();});
    $('#retry-results').on('click',loadNext); progress();
    if(!finished && document.getElementById('results-scroll').scrollHeight <= document.getElementById('results-scroll').clientHeight) requestAnimationFrame(loadNext);
    @endif
});
</script>
@endpush
