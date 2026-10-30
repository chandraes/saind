@extends('layouts.app')
@section('content')
<div class="container-fluid payroll-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><div class="small text-success fw-bold text-uppercase mb-1">Billing · Penggajian</div><h2 class="fw-bold mb-1">Form Gaji Direksi & Staff</h2><p class="text-muted mb-0">Tinjau pendapatan dan pantau potongan absensi sampai penarikan terakhir sebelum mengesahkan gaji.</p></div>
        <div class="d-flex align-items-center gap-2"><span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-2">{{ $monthName }} {{ $tahun }}</span><a href="{{ route('billing.index') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Billing</a></div>
    </div>
    @unless($payrollDateReady)<div class="alert alert-info" role="status"><strong>Preview periode berikutnya.</strong> Gaji baru dapat diproses mulai {{ $processingStartsAt->copy()->locale('id')->translatedFormat('d F Y') }} pukul 00.00 WIB. Nilai masih dapat berubah selama periode berjalan.</div>@endunless
    @include('database.bpjs-health-payroll-status', ['bpjsYear' => $tahun])
    @include('billing.gaji.attendance')
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="card payroll-summary h-100"><div class="card-body p-3 p-lg-4"><div class="small text-muted mb-2">Penerima gaji</div><div class="fs-3 fw-bold">{{ $peopleCount }} <span class="fs-6 fw-normal text-muted">orang</span></div><div class="small text-muted mt-1">{{ $direksi->count() }} direksi · {{ $data->count() }} staff</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="card payroll-summary h-100"><div class="card-body p-3 p-lg-4"><div class="small text-muted mb-2">Pendapatan bersih</div><div class="summary-money fw-bold">Rp {{ number_format($gt['pbersih'], 0, ',', '.') }}</div><div class="small text-muted mt-1">Setelah potongan BPJS</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="card payroll-summary h-100"><div class="card-body p-3 p-lg-4"><div class="small text-muted mb-2">Potongan kasbon</div><div class="summary-money fw-bold text-danger">Rp {{ number_format($gt['kasbon'], 0, ',', '.') }}</div><div class="small text-muted mt-1">Sesuai periode bulan gaji</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="card payroll-summary payroll-total h-100"><div class="card-body p-3 p-lg-4"><div class="small mb-2">Total gaji dibayarkan</div><div class="summary-money fw-bold">Rp {{ number_format($gt['total'], 0, ',', '.') }}</div><div class="small mt-1 opacity-75">Seluruh penerima pada periode ini</div></div></div></div>
    </div>
    <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div><h5 class="fw-bold mb-1">Rincian Gaji <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1 small">Pratinjau</span></h5><p class="small text-muted mb-0">Gulir tabel untuk memuat penerima berikutnya. Total tetap mencakup seluruh periode.</p></div>
            <div class="d-flex flex-wrap gap-2"><a href="{{ route('billing.gaji.preview-pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-outline-danger" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o me-1"></i> Download PDF</a><a href="{{ route('billing.gaji.preview-excel', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-outline-success"><i class="fa fa-file-excel-o me-1"></i> Download Excel</a></div>
        </div>
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
            <div class="payroll-search"><label for="payroll-search" class="form-label small fw-bold">Cari penerima</label><div class="input-group"><span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span><input id="payroll-search" type="search" class="form-control" maxlength="100" placeholder="Nama atau jabatan…" aria-controls="rekapTable"><button id="reset-search" type="button" class="btn btn-outline-secondary" aria-label="Bersihkan pencarian"><i class="fa fa-times"></i></button></div></div>
            <label class="small text-muted d-flex align-items-center gap-2"><input id="payroll-deductions-only" type="checkbox"> Hanya pegawai dengan potongan absensi</label><div class="small text-muted"><i class="fa fa-calendar-check-o me-1"></i> Periode {{ $monthName }} {{ $tahun }}</div>
        </div>
        <div class="table-responsive">
        <table class="table table-hover align-middle w-100" id="rekapTable">
            <colgroup>
                @foreach([3, 12, 9, 7, 6, 5, 6, 6, 6, 6, 8, 8, 7, 8, 9] as $columnWidth)
                    <col style="width: {{ $columnWidth }}%">
                @endforeach
            </colgroup>
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
                    <th rowspan="2" class="text-center align-middle">Potongan Absensi</th>
                    <th rowspan="2" class="text-center align-middle">Sisa Gaji Dibayar</th>
                </tr>
                <tr>
                    <th class="text-center">Jabatan</th>
                    <th class="text-center">Keluarga</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot class="table-light fw-bold text-end">
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
                    <td class="text-danger text-end">{{ number_format($gt['potongan_absensi'],0,',','.') }}</td>
                    <td class="bg-success text-white text-end">{{ number_format($gt['total'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    </div></div>
    <div class="card border-0 bg-light rounded-4 mb-4"><div class="card-body p-3 p-md-4 d-flex gap-3"><i class="fa fa-info-circle text-success mt-1"></i><div class="small text-muted"><div class="fw-bold text-dark mb-1">Periksa sebelum menyimpan</div>Kasbon dihitung sampai akhir {{ $monthName }} {{ $tahun }}. Tanggal transaksi Kas Besar mengikuti tanggal penyimpanan. Nominal gaji dan status pegawai mengacu pada data master saat ini; tinjau kembali jika ada perubahan sejak periode yang dipilih.</div></div></div>
    @if($negativeCount > 0)<p class="small text-danger fw-bold"><i class="fa fa-exclamation-circle me-1"></i> {{ $negativeCount }} penerima memiliki sisa gaji negatif. Periksa potongan kasbon sebelum menyimpan.</p>@endif
    <div class="payroll-actions card border-0 shadow-sm rounded-4"><div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><div class="small text-muted">Total dibayarkan · {{ $peopleCount }} penerima</div><div class="fs-5 fw-bold text-success">Rp {{ number_format($gt['total'], 0, ',', '.') }}</div></div>
        <div class="d-flex flex-wrap gap-2 align-items-center"><a href="{{ route('billing.index') }}" class="btn btn-outline-secondary">Batal</a><button id="retry-table" type="button" class="btn btn-outline-success d-none"><i class="fa fa-refresh me-1"></i> Muat Ulang Data</button><form action="{{ route('billing.gaji.store') }}" method="post" id="lanjutForm">@csrf<input type="hidden" name="bulan" value="{{ $bulan }}"><input type="hidden" name="tahun" value="{{ $tahun }}"><input type="hidden" name="payroll_revision" value="{{ $payrollRevision }}"><button id="save-payroll" class="btn btn-success" type="submit" disabled><i class="fa fa-check-circle me-1"></i> Konfirmasi & Simpan Gaji</button></form></div>
    </div></div>
</div>
@endsection
@push('css')
<link href="{{ asset('assets/css/payroll-attendance-detail.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
<link href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.bootstrap5.min.css" rel="stylesheet">
<style>
.attendance-select-field{position:relative;min-width:0}.attendance-select-field>.select2-container.select2{width:100%!important}.payroll-detail-text{white-space:pre-line;text-align:left;font-size:.9rem}.payroll-page{max-width:1800px;padding-bottom:2rem}.payroll-summary{border:0;border-radius:1rem;box-shadow:0 3px 16px rgba(35,70,50,.05)}.payroll-total{background:#18754e;color:#fff}.summary-money{font-size:clamp(1rem,1.5vw,1.5rem);font-variant-numeric:tabular-nums}.payroll-search{width:360px;max-width:100%}
.payroll-page table{table-layout:fixed;width:100%!important;min-width:1180px;font-size:clamp(.625rem,.78vw,.78rem)}.payroll-page th{padding:.7rem .25rem;white-space:normal;overflow-wrap:break-word;vertical-align:middle!important;line-height:1.35}.payroll-page td{padding:.8rem .25rem;white-space:nowrap;font-variant-numeric:tabular-nums;overflow:hidden;text-overflow:ellipsis}.payroll-page tbody tr{height:64px}.payroll-page .payroll-person{white-space:normal;overflow-wrap:anywhere;text-overflow:clip;line-height:1.35}.payroll-page .payroll-person-text{display:block;min-height:2.7em}.payroll-page tfoot td{font-size:.95em}.payroll-page .payroll-deduction{background:#fffaf0!important}.payroll-page .payroll-net{background:#edf7f1!important;color:#186340;font-weight:700}.payroll-page tfoot .payroll-net{background:#18754e!important;color:#fff!important}.payroll-page .dataTables_scrollBody{border:1px solid #e4ece7;border-radius:.5rem}.payroll-page .dataTables_info{font-size:.8rem;color:#6c757d;padding-top:1rem}.payroll-actions{position:sticky;bottom:1rem;z-index:10;border:1px solid #e4ece7!important}
@media(max-width:575px){.payroll-actions{bottom:.5rem}.payroll-actions .card-body>div{width:100%}.payroll-actions form{flex-grow:1}.payroll-actions form button{width:100%}}
</style>
@endpush
@push('js')
<script src="{{ asset('assets/js/payroll-attendance-detail.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
<script>
$(function () {
    $('#adjust-employee').select2({width:'100%',dropdownParent:$('#adjust-employee').parent()});
    const canSave=@json($payrollDateReady && $peopleCount > 0 && $negativeCount === 0 && $bpjsSetting !== null && (!$attendanceEnabled || ($attendanceSnapshot['ready'] ?? false)));
    @php($formattedTotal = 'Rp '.number_format($gt['total'], 0, ',', '.'))
    const period=@json($monthName.' '.$tahun), total=@json($formattedTotal), peopleCount=@json($peopleCount);
    let ready=false, busy=false, confirming=false;
    const money=(value,type) => type === 'display' ? new Intl.NumberFormat('id-ID',{maximumFractionDigits:0}).format(value) : value;
    const text=$.fn.dataTable.render.text();
    const personText=(value,type) => type === 'display' ? '<span class="payroll-person-text">'+text.display(value)+'</span>' : value;
    const attendanceDeduction=(value,type) => {
        const amount=Number(value) || 0;
        if(type !== 'display') return amount;
        return amount > 0
            ? '<button type="button" class="btn btn-link text-danger p-0 fw-semibold text-decoration-underline payroll-attendance-detail" aria-label="Lihat detail potongan absensi">'+money(amount,'display')+'</button>'
            : '<span class="text-muted">0</span>';
    };
    const table=$('#rekapTable').DataTable({
        processing:true,serverSide:true,deferRender:true,autoWidth:false,ordering:false,dom:'rti',pageLength:25,scrollX:true,scrollY:'460px',scrollCollapse:true,scroller:{displayBuffer:3,serverWait:350,rowHeight:64},
        ajax:{url:@json(route('billing.gaji.index', ['bulan' => $bulan, 'tahun' => $tahun])),data:request=>{request.deductions_only=$('#payroll-deductions-only').prop('checked') ? 1 : 0;},error:xhr => {
            ready=false; $('#save-payroll').prop('disabled',true); $('#retry-table').removeClass('d-none');
            Swal.fire({icon:'error',title:'Data belum dapat dimuat',text:xhr.status === 419 ? 'Sesi berakhir. Muat ulang halaman.' : xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('\n') : 'Periksa koneksi lalu coba muat ulang data.'});
        }},
        columns:[{data:'number',className:'text-center',render:text},{data:'nama',className:'payroll-person',render:personText,createdCell:(cell,value) => {cell.title=value;}},{data:'jabatan',className:'payroll-person',render:personText,createdCell:(cell,value) => {cell.title=value;}},...['gp','tj','tk','btk','bk','ptk','pk','pkotor','pbersih','kasbon'].map(data => ({data,render:money,className:'text-end'+(data === 'kasbon' ? ' text-danger payroll-deduction' : '')})),{data:'potongan_absensi',defaultContent:0,render:attendanceDeduction,className:'text-end payroll-deduction'},{data:'total',render:money,className:'text-end payroll-net'}],
        language:{processing:'Memuat rincian gaji…',emptyTable:'Belum ada penerima gaji pada periode ini.',zeroRecords:'Tidak ada nama atau jabatan yang sesuai pencarian.',info:'Menampilkan _START_–_END_ dari _TOTAL_ penerima',infoEmpty:'Tidak ada penerima yang ditampilkan',infoFiltered:'(disaring dari _MAX_ penerima)'},
        drawCallback:function(){ready=true; $('#save-payroll').prop('disabled',!canSave || busy); $('#retry-table').addClass('d-none');}
    });
    $('#rekapTable').on('click','.payroll-attendance-detail',function(){
        const row=table.row($(this).closest('tr')).data();
        if(!row || !(Number(row.potongan_absensi) > 0)) return;
        window.showPayrollAttendanceDetail(row.nama,period,row.attendance_detail,{{ \Illuminate\Support\Js::from(route('billing.gaji.attendance.pdf', ['employee' => '__EMPLOYEE__', 'bulan' => $bulan, 'tahun' => $tahun])) }}.replace('__EMPLOYEE__',encodeURIComponent(row.employee_id)));
    });
    $('[data-payroll-operation]').on('submit',async function(event){
        event.preventDefault(); if(busy || confirming) return; confirming=true;
        const result=await Swal.fire({icon:'question',title:'Terapkan perubahan?',text:'Perhitungan draf gaji dapat berubah. Tinjau kembali sebelum mengesahkan pembayaran.',showCancelButton:true,confirmButtonText:'Ya, lanjutkan',cancelButtonText:'Batal',confirmButtonColor:'#18754e'});
        confirming=false; if(!result.isConfirmed) return;
        busy=true; $('button').prop('disabled',true);
        Swal.fire({title:'Memproses…',text:'Mohon tunggu sampai proses selesai.',allowOutsideClick:false,allowEscapeKey:false,didOpen:()=>Swal.showLoading()});
        this.submit();
    });
    let searchTimer;
    $('#payroll-search').on('input',function(){clearTimeout(searchTimer);const search=this.value;searchTimer=setTimeout(() => table.search(search).draw(),400);});
    $('#reset-search').on('click',function(){clearTimeout(searchTimer);$('#payroll-search').val('').trigger('focus');table.search('').draw();});
    $('#payroll-deductions-only').on('change',()=>table.ajax.reload());
    $('#retry-table').on('click',() => table.ajax.reload());
    $('#lanjutForm').on('submit',async function(event){
        event.preventDefault(); if(busy || confirming || !ready || !canSave) return;
        confirming=true;
        const result=await Swal.fire({icon:'question',title:'Simpan gaji '+period+'?',text:'Total '+total+' untuk '+peopleCount+' penerima. Seluruh penerima pada periode ini akan diproses, termasuk yang tidak tampil dalam pencarian.',showCancelButton:true,confirmButtonText:'Ya, simpan gaji',cancelButtonText:'Periksa kembali',confirmButtonColor:'#18754e'});
        confirming=false; if(!result.isConfirmed) return;
        busy=true; Swal.fire({title:'Menyimpan gaji…',text:'Memeriksa dan mengunci data absensi. Jangan tutup halaman.',allowOutsideClick:false,allowEscapeKey:false,didOpen:()=>Swal.showLoading()}); $('#save-payroll').prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span> Menyimpan…'); this.submit();
    });
    @if(session('success')) Swal.fire({icon:'success',title:'Berhasil',text:@json(session('success'))}); @endif
    @if(session('error')) Swal.fire({icon:'error',title:'Tidak berhasil',text:@json(session('error'))}); @endif
    @if($errors->any()) Swal.fire({icon:'error',title:'Gaji belum dapat disimpan',text:(@json($errors->all())).join('\n')}); @endif
});
</script>
@endpush
