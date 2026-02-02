@extends('layouts.app')

@section('content')
<div class="container py-3 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-primary m-0"><i class="fa fa-user-tie me-2"></i>Tambah Direksi</h4>
                <a href="{{route('direksi.index')}}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-3 p-md-5">
                    <form action="{{route('direksi.store')}}" method="post" enctype="multipart/form-data" id="masukForm">
                        @csrf

                        {{-- SECTION 1: DATA PRIBADI --}}
                        <div class="d-flex align-items-center mb-3">
                            <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;"><i class="fa fa-user fa-sm"></i></span>
                            <h5 class="mb-0 fw-bold">Data Pribadi</h5>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" id="nama" required value="{{ old('nama') }}">
                                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="nickname" class="form-label">Nickname <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nickname') is-invalid @enderror" name="nickname" id="nickname" required value="{{ old('nickname') }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="nik" class="form-label">NIK <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nik') is-invalid @enderror" name="nik" id="nik" required value="{{ old('nik') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="npwp" class="form-label">NPWP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('npwp') is-invalid @enderror" name="npwp" id="npwp" required value="{{ old('npwp') }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="tempat_lahir" class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" name="tempat_lahir" id="tempat_lahir" required value="{{ old('tempat_lahir') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" name="tanggal_lahir" id="tanggal_lahir" required value="{{ old('tanggal_lahir') }}">
                            </div>

                             {{-- STATUS MENIKAH & ANAK (BARU) --}}
                             <div class="col-12 col-md-6">
                                <label for="status_menikah" class="form-label">Status Pernikahan <span class="text-danger">*</span></label>
                                <select class="form-select @error('status_menikah') is-invalid @enderror" name="status_menikah" id="status_menikah" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="0" {{ old('status_menikah') == '0' ? 'selected' : '' }}>Belum Menikah</option>
                                    <option value="1" {{ old('status_menikah') == '1' ? 'selected' : '' }}>Menikah</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="jumlah_anak" class="form-label">Jumlah Anak</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa fa-child"></i></span>
                                    <input type="number" min="0" class="form-control @error('jumlah_anak') is-invalid @enderror" name="jumlah_anak" id="jumlah_anak" value="{{ old('jumlah_anak', 0) }}">
                                </div>
                                <div class="form-text text-muted small">Isi 0 jika tidak memiliki anak.</div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted">

                        {{-- SECTION 2: KONTAK --}}
                        <div class="d-flex align-items-center mb-3">
                            <span class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;"><i class="fa fa-map-marker fa-sm"></i></span>
                            <h5 class="mb-0 fw-bold">Kontak & Alamat</h5>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label for="no_hp" class="form-label">Nomor HP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('no_hp') is-invalid @enderror" name="no_hp" id="no_hp" required value="{{ old('no_hp') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="no_wa" class="form-label">Nomor WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('no_wa') is-invalid @enderror" name="no_wa" id="no_wa" required value="{{ old('no_wa') }}">
                            </div>
                            <div class="col-12">
                                <label for="alamat" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('alamat') is-invalid @enderror" name="alamat" id="alamat" rows="2" required>{{ old('alamat') }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4 text-muted">

                        {{-- SECTION 3: JABATAN & KEUANGAN --}}
                        <div class="d-flex align-items-center mb-3">
                            <span class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;"><i class="fa fa-briefcase fa-sm"></i></span>
                            <h5 class="mb-0 fw-bold">Pekerjaan & Finansial</h5>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <label for="jabatan" class="form-label">Jabatan <span class="text-danger">*</span></label>
                                {{-- Tetap menggunakan Input Text sesuai file asli --}}
                                <input type="text" class="form-control @error('jabatan') is-invalid @enderror" name="jabatan" id="jabatan" required value="{{ old('jabatan') }}">
                                @error('jabatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="mulai_bekerja" class="form-label">Mulai Bekerja <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('mulai_bekerja') is-invalid @enderror" name="mulai_bekerja" id="mulai_bekerja" required value="{{ old('mulai_bekerja') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="gaji_pokok" class="form-label">Gaji Pokok</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control text-end" name="gaji_pokok" id="gaji_pokok" required data-thousands="." value="{{ old('gaji_pokok') }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="tunjangan_jabatan" class="form-label">Tunjangan Jabatan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control text-end" name="tunjangan_jabatan" id="tunjangan_jabatan" data-thousands="." value="{{ old('tunjangan_jabatan') }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="tunjangan_keluarga" class="form-label">Tunjangan Keluarga</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control text-end" name="tunjangan_keluarga" id="tunjangan_keluarga" data-thousands="." value="{{ old('tunjangan_keluarga') }}">
                                </div>
                            </div>

                            {{-- BANK --}}
                            <div class="col-12 col-md-4">
                                <label for="bank" class="form-label">Nama Bank</label>
                                <input type="text" class="form-control bg-light" name="bank" id="bank" value="BCA" readonly required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="no_rekening" class="form-label">Nomor Rekening <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="no_rekening" id="no_rekening" required value="{{ old('no_rekening') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="nama_rekening" class="form-label">Atas Nama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_rekening" id="nama_rekening" required value="{{ old('nama_rekening') }}">
                            </div>
                        </div>

                        <hr class="my-4 text-muted">

                        {{-- SECTION 4: BPJS --}}
                        <div class="d-flex align-items-center mb-3">
                            <span class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;"><i class="fa fa-heartbeat fa-sm"></i></span>
                            <h5 class="mb-0 fw-bold">BPJS & Asuransi</h5>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="form-check form-check-inline me-4">
                                            <input class="form-check-input" type="checkbox" name="apa_bpjs_tk" id="apa_bpjs_tk" {{ old('apa_bpjs_tk') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="apa_bpjs_tk">Ikut BPJS Tenaga Kerja</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="apa_bpjs_kesehatan" id="apa_bpjs_kesehatan" {{ old('apa_bpjs_kesehatan') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="apa_bpjs_kesehatan">Ikut BPJS Kesehatan</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="bpjs_tk" class="form-label">No. BPJS Tenaga Kerja <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="bpjs_tk" id="bpjs_tk" required value="{{ old('bpjs_tk') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="bpjs_kesehatan" class="form-label">No. BPJS Kesehatan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="bpjs_kesehatan" id="bpjs_kesehatan" required value="{{ old('bpjs_kesehatan') }}">
                            </div>
                        </div>

                         {{-- SECTION 5: FOTO --}}
                         <hr class="my-4 text-muted">
                         <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="foto_ktp" class="form-label fw-bold">Foto KTP <span class="text-danger">*</span></label>
                                <input type="file" class="form-control @error('foto_ktp') is-invalid @enderror" name="foto_ktp" id="foto_ktp" required>
                                @error('foto_ktp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="foto_diri" class="form-label fw-bold">Foto Diri <span class="text-danger">*</span></label>
                                <input type="file" class="form-control @error('foto_diri') is-invalid @enderror" name="foto_diri" id="foto_diri" required>
                                @error('foto_diri') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5">
                            <a href="{{route('direksi.index')}}" class="btn btn-secondary px-4 me-md-2">Batal</a>
                            <button class="btn btn-success px-5" type="submit">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{asset('assets/js/jquery.maskMoney.js')}}"></script>
<script>
      $(document).ready(function(){
             var cleaveConfig = {
               numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            };

            var gaji_pokok = new Cleave('#gaji_pokok', cleaveConfig);
            var tunjangan_jabatan = new Cleave('#tunjangan_jabatan', cleaveConfig);
            var tunjangan_keluarga = new Cleave('#tunjangan_keluarga', cleaveConfig);
        });

        $('#masukForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Simpan Data?',
                text: "Pastikan data sudah benar.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
                }).then((result) => {
                if (result.isConfirmed) {
                    // $('#spinner').show();
                    this.submit();
                }
            })
        });
</script>
@endpush
