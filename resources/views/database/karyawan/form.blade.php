<div class="container employee-form-page">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div><div class="text-success small fw-bold text-uppercase mb-1">Manajemen pegawai</div><h2 class="fw-bold mb-1">{{ $editing ? 'Ubah Pegawai' : 'Tambah Pegawai' }}</h2><p class="text-muted mb-0">{{ $editing ? 'Perbarui biodata dan informasi kepegawaian.' : 'Lengkapi biodata untuk mendaftarkan pegawai baru.' }}</p></div>
        <a href="{{ route('karyawan.index') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Daftar Pegawai</a>
    </div>
    @include('swal')
    <form id="employee-form" action="{{ $editing ? route('karyawan.update', $karyawan) : route('karyawan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 employee-form-nav"><div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Informasi Pegawai</h6>
                    <nav class="nav flex-column gap-1" aria-label="Bagian formulir">
                        @foreach (['identity' => 'Identitas & Jabatan', 'salary' => 'Gaji & Tunjangan', 'personal' => 'Biodata & Kontak', 'insurance' => 'BPJS & Perpajakan', 'account' => 'Rekening Bank', 'documents' => 'Dokumen Pegawai'] as $anchor => $label)<a class="nav-link text-secondary px-2 py-2 rounded" href="#{{ $anchor }}">{{ $loop->iteration }}. {{ $label }}</a>@endforeach
                    </nav><hr><p class="small text-muted mb-0">Kolom bertanda <span class="text-danger">*</span> wajib diisi.</p>
                </div></div>
            </div>
            <div class="col-lg-9">
                <div id="identity" class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4">
                    <h5 class="fw-bold mb-1">Identitas & Jabatan</h5><p class="text-muted small mb-4">Informasi utama dan status kepegawaian.</p>
                    <div class="row g-3">
                        @foreach (['nama' => 'Nama Lengkap', 'nickname' => 'Nama Panggilan'] as $field => $label)
                            <div class="col-md-6"><label for="{{ $field }}" class="form-label fw-bold">{{ $label }} <span class="text-danger">*</span></label><input id="{{ $field }}" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $karyawan->$field) }}" maxlength="255" required><div class="invalid-feedback" data-error="{{ $field }}">@error($field){{ $message }}@enderror</div></div>
                        @endforeach
                        <div class="col-md-6"><label for="jabatan_id" class="form-label fw-bold">Jabatan <span class="text-danger">*</span></label><select id="jabatan_id" name="jabatan_id" class="form-select @error('jabatan_id') is-invalid @enderror" required><option value="">Pilih jabatan</option>@foreach ($jabatan as $position)<option value="{{ $position->id }}" @selected((string) old('jabatan_id', $karyawan->jabatan_id) === (string) $position->id)>{{ $position->nama }}</option>@endforeach</select><div class="invalid-feedback" data-error="jabatan_id">@error('jabatan_id'){{ $message }}@enderror</div></div>
                        <div class="col-md-6"><label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label><select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>@foreach (['aktif'=>'Aktif','nonaktif'=>'Nonaktif'] as $value=>$label)<option value="{{ $value }}" @selected(old('status', $karyawan->status ?? 'aktif') === $value)>{{ $label }}</option>@endforeach</select><div class="invalid-feedback" data-error="status">@error('status'){{ $message }}@enderror</div></div>
                        <div class="col-md-6"><label for="mulai_bekerja" class="form-label fw-bold">Mulai Bekerja <span class="text-danger">*</span></label><input type="date" id="mulai_bekerja" name="mulai_bekerja" class="form-control @error('mulai_bekerja') is-invalid @enderror" value="{{ old('mulai_bekerja', $karyawan->mulai_bekerja) }}" required><div class="invalid-feedback" data-error="mulai_bekerja">@error('mulai_bekerja'){{ $message }}@enderror</div></div>
                    </div>
                </div></div>
                <div id="salary" class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4">
                    <h5 class="fw-bold mb-1">Gaji & Tunjangan</h5><p class="text-muted small mb-4">Nominal dalam rupiah. Tunjangan yang kosong dihitung nol.</p><div class="row g-3">
                    @foreach (['gaji_pokok'=>'Gaji Pokok','tunjangan_jabatan'=>'Tunjangan Jabatan','tunjangan_keluarga'=>'Tunjangan Keluarga'] as $field=>$label)
                        <div class="col-md-4"><label for="{{ $field }}" class="form-label fw-bold">{{ $label }} @if ($field === 'gaji_pokok')<span class="text-danger">*</span>@endif</label><div class="input-group"><span class="input-group-text">Rp</span><input id="{{ $field }}" name="{{ $field }}" class="form-control salary-input @error($field) is-invalid @enderror" inputmode="numeric" value="{{ old($field, number_format($karyawan->$field ?? 0, 0, ',', '.')) }}" @required($field === 'gaji_pokok')></div><div class="invalid-feedback" data-error="{{ $field }}">@error($field){{ $message }}@enderror</div></div>
                    @endforeach</div><div class="rounded-3 bg-success bg-opacity-10 text-success p-3 mt-3 d-flex justify-content-between gap-3"><span>Total Gaji & Tunjangan</span><strong id="salary-total" aria-live="polite">Rp0</strong></div>
                </div></div>
                <div id="personal" class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4">
                    <h5 class="fw-bold mb-1">Biodata & Kontak</h5><p class="text-muted small mb-4">Data identitas dan kontak yang dapat dihubungi.</p><div class="row g-3">
                    @foreach (['nik'=>'NIK','tempat_lahir'=>'Tempat Lahir','tanggal_lahir'=>'Tanggal Lahir','no_hp'=>'Nomor HP','no_wa'=>'Nomor WhatsApp'] as $field=>$label)
                        <div class="col-md-6"><label for="{{ $field }}" class="form-label fw-bold">{{ $label }} <span class="text-danger">*</span></label><input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'tanggal_lahir' ? 'date' : (in_array($field, ['no_hp','no_wa']) ? 'tel' : 'text') }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $karyawan->$field) }}" maxlength="255" @if ($field === 'tanggal_lahir') max="{{ now()->toDateString() }}" @endif required><div class="invalid-feedback" data-error="{{ $field }}">@error($field){{ $message }}@enderror</div></div>
                    @endforeach
                    <div class="col-12"><label for="alamat" class="form-label fw-bold">Alamat <span class="text-danger">*</span></label><textarea id="alamat" name="alamat" rows="3" class="form-control @error('alamat') is-invalid @enderror" required maxlength="5000">{{ old('alamat', $karyawan->alamat) }}</textarea><div class="invalid-feedback" data-error="alamat">@error('alamat'){{ $message }}@enderror</div></div>
                    </div>
                </div></div>
                <div id="insurance" class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4">
                    <h5 class="fw-bold mb-1">BPJS & Perpajakan</h5><p class="text-muted small mb-4">Nomor BPJS wajib diisi jika kepesertaan diaktifkan.</p><div class="row g-3">
                    <div class="col-12"><label for="npwp" class="form-label fw-bold">NPWP <span class="text-danger">*</span></label><input id="npwp" name="npwp" class="form-control @error('npwp') is-invalid @enderror" value="{{ old('npwp', $karyawan->npwp) }}" maxlength="255" required><div class="form-text">Isi tanda - jika belum memiliki NPWP.</div><div class="invalid-feedback" data-error="npwp">@error('npwp'){{ $message }}@enderror</div></div>
                    @foreach (['tk'=>'BPJS Ketenagakerjaan','kesehatan'=>'BPJS Kesehatan'] as $type=>$label)
                        @php($field = 'bpjs_'.$type)
                        <div class="col-md-6"><div class="form-check form-switch mb-3"><input class="form-check-input bpjs-switch" type="checkbox" role="switch" id="apa_{{ $field }}" name="apa_{{ $field }}" value="1" data-target="{{ $field }}" @checked(old('apa_'.$field, session()->hasOldInput() ? 0 : $karyawan->{'apa_'.$field}))><label class="form-check-label fw-bold" for="apa_{{ $field }}">Ikut {{ $label }}</label></div><label for="{{ $field }}" class="form-label">Nomor {{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $karyawan->$field) }}" maxlength="255" placeholder="Nomor kepesertaan"><div class="invalid-feedback" data-error="{{ $field }}">@error($field){{ $message }}@enderror</div><div class="invalid-feedback" data-error="apa_{{ $field }}">@error('apa_'.$field){{ $message }}@enderror</div></div>
                    @endforeach</div>
                </div></div>
                <div id="account" class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4">
                    <h5 class="fw-bold mb-1">Rekening Bank</h5><p class="text-muted small mb-4">Pastikan rekening sesuai dengan penerima pembayaran.</p><div class="row g-3">
                    @foreach (['bank'=>'Nama Bank','no_rekening'=>'Nomor Rekening','nama_rekening'=>'Nama Pemilik Rekening'] as $field=>$label)
                        <div class="col-md-4"><label for="{{ $field }}" class="form-label fw-bold">{{ $label }} <span class="text-danger">*</span></label><input id="{{ $field }}" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $karyawan->$field) }}" maxlength="255" required><div class="invalid-feedback" data-error="{{ $field }}">@error($field){{ $message }}@enderror</div></div>
                    @endforeach</div>
                </div></div>
                <div id="documents" class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4">
                    <h5 class="fw-bold mb-1">Dokumen Pegawai</h5><p class="text-muted small mb-4">Format JPG, JPEG, atau PNG. Maksimal 10 MB per foto. {{ $editing ? 'Kosongkan jika foto tidak diganti.' : 'Kedua foto wajib diunggah.' }}</p><div class="row g-3">
                    @foreach (['foto_diri'=>'Foto Diri','foto_ktp'=>'Foto KTP'] as $field=>$label)
                        <div class="col-md-6"><label for="{{ $field }}" class="form-label fw-bold">{{ $label }} @if (!$editing)<span class="text-danger">*</span>@endif</label><input id="{{ $field }}" name="{{ $field }}" type="file" accept="image/jpeg,image/png" class="form-control employee-photo @error($field) is-invalid @enderror" @required(!$editing)><div class="invalid-feedback" data-error="{{ $field }}">@error($field){{ $message }}@enderror</div>
                            <div class="photo-preview mt-3 rounded-3 border bg-light d-flex align-items-center justify-content-center"><img id="preview-{{ $field }}" class="img-fluid rounded-2" alt="Pratinjau {{ strtolower($label) }}" @if ($photos[$field]) src="{{ asset('storage/'.preg_replace('#^public/#', '', $karyawan->$field)) }}" @else hidden @endif><span id="empty-{{ $field }}" class="text-muted small" @if ($photos[$field]) hidden @endif>Belum ada foto</span></div>
                        </div>
                    @endforeach
                    <div class="col-12"><label for="perjanjian_kerja" class="form-label fw-bold">Perjanjian Kerja <span class="text-danger">*</span></label>
                        <input id="perjanjian_kerja" name="perjanjian_kerja" type="file" accept="application/pdf,.pdf" class="form-control @error('perjanjian_kerja') is-invalid @enderror" @required(!$karyawan->perjanjian_kerja)>
                        <div class="invalid-feedback" data-error="perjanjian_kerja">@error('perjanjian_kerja'){{ $message }}@enderror</div>
                        <div class="form-text">Wajib tersedia. Hanya PDF, maksimal 5 MB. {{ $karyawan->perjanjian_kerja ? 'Dokumen tersimpan tetap digunakan jika tidak diganti.' : 'Unggah perjanjian kerja pegawai sebelum menyimpan.' }}</div>
                        @if($karyawan->perjanjian_kerja)<a class="btn btn-sm btn-outline-success mt-2" target="_blank" rel="noopener" href="{{ route('karyawan.document', ['karyawan' => $karyawan->id, 'document' => 'perjanjian_kerja']) }}"><i class="fa fa-file-text-o me-1"></i>Lihat perjanjian tersimpan</a>@endif
                    </div></div>
                </div></div>
                <div class="employee-save-bar bg-white border rounded-4 p-3 shadow-sm d-flex flex-wrap justify-content-between align-items-center gap-3"><span class="text-muted small">Periksa kembali data sebelum menyimpan.</span><div class="d-flex gap-2"><a href="{{ route('karyawan.index') }}" class="btn btn-outline-secondary">Batal</a><button id="employee-save" type="submit" class="btn btn-success"><i class="fa fa-check me-1"></i> Simpan Pegawai</button></div></div>
            </div>
        </div>
    </form>
</div>
@push('css')
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet">
<style>
.employee-form-page .card{scroll-margin-top:24px}.employee-form-page .invalid-feedback:not(:empty){display:block}.employee-form-page .photo-preview{height:180px;overflow:hidden}.employee-form-page .photo-preview img{max-height:160px;object-fit:contain}.employee-form-nav .nav-link:hover{background:#edf7f1;color:#198754!important}.employee-save-bar{position:sticky;bottom:12px;z-index:10}.employee-form-page .select2-selection--single{min-height:38px;border-color:#ced4da!important}.employee-form-page .select2-selection__rendered{line-height:36px!important}.employee-form-page .select2-selection__arrow{height:36px!important}@media(min-width:992px){.employee-form-nav{position:sticky;top:24px}}
</style>
@endpush
@push('js')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
$(function () {
    $('#jabatan_id, #status').select2({width:'100%'});
    const form = document.getElementById('employee-form'); let saving = false;
    const currencies = [];
    $('.salary-input').each(function () {currencies.push(new Cleave(this,{numeral:true,numeralThousandsGroupStyle:'thousand',numeralDecimalScale:0,numeralPositiveOnly:true,delimiter:'.',numeralDecimalMark:','}));});
    function totalSalary(){const total=currencies.reduce((sum,input)=>sum+(Number(input.getRawValue())||0),0);$('#salary-total').text('Rp'+total.toLocaleString('id-ID'));}
    $('.salary-input').on('input',totalSalary);totalSalary();
    $('.bpjs-switch').on('change',function(){$('#'+$(this).data('target')).prop('required',this.checked);}).trigger('change');
    $('.employee-photo').each(function(){const field=this.id;$('#preview-'+field).on('error',function(){this.hidden=true;$('#empty-'+field).text('Foto belum tersedia').prop('hidden',false);});}).on('change',function(){
        const file=this.files[0]; const field=this.id; if(!file)return;
        if(!['image/jpeg','image/png'].includes(file.type)||file.size>10000*1024){this.value='';Swal.fire({icon:'error',title:'Foto tidak sesuai',text:'Pilih foto JPG/PNG dengan ukuran maksimal 10 MB.'});return;}
        const reader=new FileReader();reader.onload=event=>{$('#preview-'+field).attr('src',event.target.result).prop('hidden',false);$('#empty-'+field).prop('hidden',true);};reader.readAsDataURL(file);
    });
    $(form).on('submit',async function(event){
        event.preventDefault();if(saving||!form.reportValidity())return;
        const result=await Swal.fire({icon:'question',title:'Simpan data pegawai?',text:'Pastikan biodata dan rekening sudah benar.',showCancelButton:true,confirmButtonText:'Ya, simpan',cancelButtonText:'Batal',confirmButtonColor:'#198754'});
        if(!result.isConfirmed||saving)return;
        saving=true;$(form).find('.is-invalid').removeClass('is-invalid');$(form).find('[data-error]').text('');
        $('#employee-save').prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan…');
        $.ajax({url:form.action,method:'POST',data:new FormData(form),processData:false,contentType:false,headers:{Accept:'application/json'}})
            .done(response=>Swal.fire({icon:'success',title:'Berhasil',text:response.message}).then(()=>window.location.assign(response.redirect)))
            .fail(xhr=>{
                if(xhr.status===422&&xhr.responseJSON&&xhr.responseJSON.errors){const errors=xhr.responseJSON.errors;Object.entries(errors).forEach(([field,messages])=>{$(form).find('[name="'+field+'"]').addClass('is-invalid');$(form).find('[data-error="'+field+'"]').text(messages[0]);});Swal.fire({icon:'error',title:'Periksa kembali isian',text:Object.values(errors).flat().join('\n')}).then(()=>{const first=$(form).find('.is-invalid').get(0);if(first){first.scrollIntoView({behavior:'smooth',block:'center'});first.focus();}});}
                else Swal.fire({icon:'error',title:'Data belum tersimpan',text:xhr.status===419?'Sesi berakhir. Muat ulang halaman dan coba kembali.':xhr.status>=500?'Terjadi gangguan server. Silakan coba kembali.':xhr.responseJSON&&xhr.responseJSON.message||'Periksa koneksi Anda dan coba kembali.'});
                saving=false;$('#employee-save').prop('disabled',false).html('<i class="fa fa-check me-1"></i> Simpan Pegawai');
            });
    });
});
</script>
@endpush
