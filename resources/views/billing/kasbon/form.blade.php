@php
    $title = $isCicilan ? 'Kasbon Cicilan' : 'Kasbon Potong Gaji';
    $percentage = $isCicilan ? 30 : 50;
    $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
@endphp
<div class="container py-3" style="max-width:1100px">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><div class="small text-success fw-bold text-uppercase mb-1">Billing · Kasbon Staff</div><h2 class="fw-bold mb-1">{{ $title }}</h2><p class="text-muted mb-0">{{ $isCicilan ? 'Atur nominal pinjaman dan jadwal angsuran pegawai.' : 'Catat pinjaman pegawai yang akan dipotong melalui penggajian.' }}</p></div>
        <a href="{{ route('billing.kasbon.kas-bon-staff') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Pilih Metode</a>
    </div>
    @include('swal')
    <form action="{{ route($isCicilan ? 'billing.kasbon.kas-bon-cicil-store' : 'billing.kasbon.store') }}" method="post" id="kasbon-form" novalidate>
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
                    <h5 class="fw-bold mb-1">Detail Pinjaman</h5><p class="small text-muted mb-4">Pilih pegawai dan isi nominal dalam rupiah.</p>
                    <div class="mb-4"><label for="tanggal-kasbon" class="form-label fw-bold">Tanggal transaksi</label><input id="tanggal-kasbon" class="form-control bg-light" value="{{ now()->format('d-m-Y') }}" readonly><div class="form-text">Tanggal mengikuti hari penyimpanan transaksi.</div></div>
                    <div class="mb-4"><label for="karyawan_id" class="form-label fw-bold">Pegawai <span class="text-danger">*</span></label><select name="karyawan_id" id="karyawan_id" class="form-select" required><option value="">Pilih pegawai</option>
                        @foreach($karyawan as $person)
                            <option value="{{ $person->id }}" data-income="{{ $person->gaji_pokok + ($person->tunjangan_jabatan ?? 0) + ($person->tunjangan_keluarga ?? 0) }}" @selected((string) old('karyawan_id') === (string) $person->id)>{{ $person->nama }}</option>
                        @endforeach
                    </select><div class="form-text">{{ $isCicilan ? 'Pegawai aktif yang tidak memiliki cicilan belum lunas.' : 'Cari nama pegawai aktif yang menerima pinjaman.' }}</div></div>
                    <div class="mb-1"><label for="nominal" class="form-label fw-bold">Nominal pinjaman <span class="text-danger">*</span></label><div class="input-group"><span class="input-group-text bg-light">Rp</span><input type="text" class="form-control" name="nominal" id="nominal" value="{{ old('nominal') }}" inputmode="numeric" autocomplete="off" placeholder="0" required></div><div class="form-text">Masukkan nominal rupiah tanpa desimal.</div></div>
                    @if($isCicilan)
                        <hr class="my-4"><h5 class="fw-bold mb-1">Jadwal Angsuran</h5><p class="small text-muted mb-3">Tentukan jumlah angsuran dan periode mulai potongan gaji.</p>
                        <div class="row g-3">
                            <div class="col-sm-4"><label for="cicil_kali" class="form-label fw-bold">Jumlah angsuran <span class="text-danger">*</span></label><div class="input-group"><input type="number" name="cicil_kali" id="cicil_kali" class="form-control" min="1" step="1" value="{{ old('cicil_kali') }}" placeholder="3" required><span class="input-group-text bg-light">kali</span></div></div>
                            <div class="col-sm-5"><label for="mulai_bulan" class="form-label fw-bold">Mulai bulan <span class="text-danger">*</span></label><select name="mulai_bulan" id="mulai_bulan" class="form-select" required><option value="">Pilih bulan</option></select></div>
                            <div class="col-sm-3"><label for="mulai_tahun" class="form-label fw-bold">Tahun <span class="text-danger">*</span></label><input type="number" name="mulai_tahun" id="mulai_tahun" class="form-control" min="{{ now()->year }}" max="2155" step="1" value="{{ old('mulai_tahun', now()->year) }}" required></div>
                        </div>
                    @endif
                    <div class="border-top pt-4 mt-4"><h5 class="fw-bold mb-2">Pinjaman Berjalan</h5><p id="loan-period" class="small text-muted">Pilih pegawai untuk melihat pinjaman dan batas pengajuan.</p><div id="running-loans" aria-live="polite"></div></div>
                </div></div>
            </div>
            <div class="col-lg-4"><div class="card border-0 rounded-4 bg-success bg-opacity-10"><div class="card-body p-4">
                <span class="badge bg-success rounded-pill mb-3">Acuan {{ $percentage }}%</span><h5 class="fw-bold">Ringkasan Pinjaman</h5>
                <div class="small text-muted mb-1 mt-4">Total penghasilan acuan</div><div id="income-summary" class="fw-bold fs-5">—</div>
                <p class="small text-muted mt-2">Gaji pokok + tunjangan jabatan + tunjangan keluarga.</p>
                <div class="small text-muted mt-3 mb-1">{{ $isCicilan ? 'Kasbon potong gaji belum lunas' : 'Angsuran berjalan pada bulan ini' }}</div><div id="deduction-summary" class="fw-bold">—</div>
                <div class="small text-muted mt-3 mb-1">Penghasilan setelah pengurang</div><div id="base-summary" class="fw-bold">—</div>
                <hr><div class="small text-muted mb-1">{{ $isCicilan ? 'Batas angsuran per bulan' : 'Batas total kasbon belum lunas' }}</div><div id="limit-summary" class="fw-bold fs-5 text-success">—</div>
                @unless($isCicilan)<div class="small text-muted mt-3 mb-1">Kasbon potong gaji belum lunas</div><div id="existing-summary" class="fw-bold">—</div><div class="small text-muted mt-3 mb-1">Sisa batas pengajuan baru</div><div id="available-summary" class="fw-bold text-success">—</div>@endunless
                <div class="small text-muted mb-1 mt-4">{{ $isCicilan ? 'Estimasi angsuran per bulan' : 'Nominal pengajuan' }}</div><div id="amount-summary" class="fw-bold fs-5">Rp 0</div>
                <p id="limit-note" class="small text-muted mt-3 mb-0" aria-live="polite">{{ $isCicilan ? '30% × (total penghasilan − kasbon potong gaji belum lunas).' : '50% × (total penghasilan − angsuran berjalan), lalu dikurangi kasbon potong gaji belum lunas.' }}</p>
            </div></div></div>
        </div>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-top mt-4 pt-4"><span class="small text-muted"><i class="fa fa-info-circle me-1"></i> Periksa data sebelum menyimpan. Dana dicatat keluar dari Kas Besar.</span><div class="d-flex gap-2"><a href="{{ route('billing.index') }}" class="btn btn-outline-secondary">Batal</a><button type="submit" id="save-kasbon" class="btn btn-success px-4" @disabled($karyawan->isEmpty())><i class="fa fa-check-circle me-1"></i> Simpan Kasbon</button></div></div>
        @if($karyawan->isEmpty())<p class="small text-warning mt-3">Belum ada pegawai yang dapat dipilih untuk metode ini.</p>@endif
    </form>
</div>
@push('css')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.bootstrap5.css') }}">
@endpush
@push('js')
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script>
$(function () {
    const isCicilan=@json($isCicilan), percentage=@json($percentage), months=@json($months);
    const currentYear=@json(now()->year), currentMonth=@json(now()->month), oldMonth=@json((string) old('mulai_bulan', ''));
    const money=value => 'Rp '+new Intl.NumberFormat('id-ID',{maximumFractionDigits:0}).format(value);
    const nominal=new Cleave('#nominal',{numeral:true,numeralThousandsGroupStyle:'thousand',delimiter:'.',numeralDecimalMark:',',numeralDecimalScale:0,numeralPositiveOnly:true});
    $('#karyawan_id').select2({theme:'bootstrap-5',width:'100%',placeholder:'Cari nama pegawai',allowClear:true});
    if(isCicilan){
        $('#mulai_bulan').select2({theme:'bootstrap-5',width:'100%',placeholder:'Pilih bulan'});
        function updateMonths(selected){
            const year=Number($('#mulai_tahun').val());
            const select=$('#mulai_bulan').empty().append(new Option('Pilih bulan',''));
            if(Number.isInteger(year) && year>=currentYear && year<=2155){
                for(let month=year===currentYear ? currentMonth : 1;month<=12;month++) select.append(new Option(months[month],month));
            }
            select.val(select.find('option').toArray().some(option => option.value===String(selected)) ? String(selected) : '').trigger('change');
        }
        updateMonths(oldMonth);
        $('#mulai_tahun').on('input',function(){updateMonths($('#mulai_bulan').val());});
    }
    let activeSummary=null, summaryRequest=null, requestVersion=0, loading=false, confirming=false, saving=false;
    const endpoint=@json(route('billing.kasbon.limit'));
    function summary(){
        const amount=Number(nominal.getRawValue()) || 0, count=Number($('#cicil_kali').val());
        $('#amount-summary').text(isCicilan ? (count>0 ? money(amount/count) : '—') : money(amount));
        const details=activeSummary;
        $('#income-summary').text(details ? money(details.income) : '—');
        const deduction=details ? (isCicilan ? details.ordinary_due : details.installment_due) : 0;
        $('#deduction-summary').text(details ? money(deduction) : '—');
        $('#base-summary').text(details ? money(Math.max(0,details.income-deduction)) : '—');
        $('#limit-summary').text(details ? money(isCicilan ? details.installment_limit : details.ordinary_limit) : '—');
        $('#existing-summary').text(details ? money(details.ordinary_due) : '—');
        $('#available-summary').text(details ? money(details.ordinary_available) : '—');
        $('#save-kasbon').prop('disabled',!details || loading || saving);
    }
    function loadLoans(){
        const version=++requestVersion;
        summaryRequest?.abort(); activeSummary=null; loading=false;
        const employee=$('#karyawan_id').val(), year=isCicilan ? Number($('#mulai_tahun').val()) : currentYear;
        const month=isCicilan ? Number($('#mulai_bulan').val() || currentMonth) : currentMonth;
        $('#running-loans').empty();
        if(!employee || !Number.isInteger(year) || year<1901 || year>2155){$('#loan-period').text('Pilih pegawai dan periode yang valid untuk melihat pinjaman berjalan.');summary();return;}
        loading=true; $('#loan-period').text('Memuat pinjaman berjalan…');summary();
        summaryRequest=$.ajax({url:endpoint,dataType:'json',data:{karyawan_id:employee,bulan:month,tahun:year}}).done(details => {
            if(version!==requestVersion) return;
            activeSummary=details;
            $('#loan-period').text('Acuan '+details.period+'. Batas akan diperiksa ulang saat disimpan.');
            if(!details.loans.length) $('#running-loans').append($('<p class="small text-success mb-0">').text('Tidak ada pinjaman yang belum lunas.'));
            details.loans.forEach(loan => {
                const item=$('<div class="bg-light rounded-3 p-3 mb-2">');
                item.append($('<div class="fw-bold small">').text(loan.jenis+' · '+loan.tanggal));
                item.append($('<div class="small mt-1">').text('Sisa '+money(loan.sisa)+(loan.angsuran ? ' · Angsuran '+money(loan.angsuran)+'/bulan' : '')));
                if(!loan.berlaku) item.append($('<div class="small text-muted mt-1">').text('Belum masuk potongan pada periode acuan.'));
                $('#running-loans').append(item);
            });
        }).fail((xhr,status) => {
            if(status==='abort' || version!==requestVersion) return;
            $('#loan-period').text('Data belum dapat dimuat.');
            $('<button type="button" class="btn btn-sm btn-outline-success">').text('Coba lagi').on('click',loadLoans).appendTo('#running-loans');
            Swal.fire({icon:'error',title:'Pinjaman belum dapat dimuat',text:'Silakan coba lagi sebelum melanjutkan pengajuan.'});
        }).always(() => {if(version===requestVersion){loading=false;summary();}});
    }
    $('#karyawan_id,#mulai_bulan').on('change',loadLoans);
    let yearTimer; $('#mulai_tahun').on('input',function(){clearTimeout(yearTimer);yearTimer=setTimeout(loadLoans,300);});
    $('#nominal,#cicil_kali').on('input',summary);loadLoans();
    $('#kasbon-form').on('submit',async function(event){
        event.preventDefault(); if(confirming || saving || loading) return;
        const amount=Number(nominal.getRawValue());
        if(!this.checkValidity() || !(amount>0) || !activeSummary){
            this.classList.add('was-validated');
            Swal.fire({icon:'warning',title:'Lengkapi data pinjaman',text:'Pilih pegawai, isi nominal lebih dari nol, dan lengkapi jadwal angsuran jika menggunakan cicilan.'}); return;
        }
        const limit=isCicilan ? activeSummary.installment_limit : activeSummary.ordinary_available;
        const proposed=isCicilan ? amount/Number($('#cicil_kali').val()) : amount;
        if(proposed>limit){Swal.fire({icon:'warning',title:'Pengajuan melebihi batas',text:'Maksimal '+money(limit)+(isCicilan ? ' per bulan' : ' untuk pengajuan baru')+'. Periksa pinjaman berjalan pada ringkasan.'});return;}
        confirming=true;
        const person=$('#karyawan_id option:selected').text();
        const schedule=isCicilan ? ' Dibayar '+$('#cicil_kali').val()+' kali, mulai '+$('#mulai_bulan option:selected').text()+' '+$('#mulai_tahun').val()+'.' : ' Dipotong melalui penggajian.';
        const result=await Swal.fire({icon:'question',title:'Simpan kasbon?',text:person+' mengajukan '+money(amount)+'.'+schedule,showCancelButton:true,confirmButtonColor:'#198754',confirmButtonText:'Ya, simpan',cancelButtonText:'Periksa kembali'});
        confirming=false; if(!result.isConfirmed) return;
        saving=true; $('#save-kasbon').prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Menyimpan…'); this.submit();
    });
});
</script>
@endpush
