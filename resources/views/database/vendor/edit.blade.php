@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('vendor.index')}}">Vendor</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Ubah Biodata</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark">
                <i class="fa fa-edit text-warning me-2"></i>Ubah Biodata Vendor
            </h1>
        </div>
    </div>

    <form action="{{route('vendor.update', $vendor->id)}}" method="post" id="masukForm">
        @csrf
        @method('PATCH')

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary fw-bold"><i class="fa fa-id-card me-2"></i>Informasi Identitas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="tipe" class="form-label">Tipe Vendor</label>
                                <select class="form-select shadow-none" name="tipe" id="tipe-vendor" onchange="changeTipe()" required>
                                    <option value=""> - Pilih -</option>
                                    <option value="perusahaan" {{$vendor->tipe == 'perusahaan' ? 'selected' : ''}}>Perusahaan</option>
                                    <option value="perorangan" {{$vendor->tipe == 'perorangan' ? 'selected' : ''}}>Perorangan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control @error('nama') is-invalid @enderror shadow-none" value="{{$vendor->nama}}" name="nama" id="nama" required>
                                @error('nama') <div class="invalid-feedback">{{$message}}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="nickname" class="form-label">Nickname</label>
                                <input type="text" class="form-control @error('nickname') is-invalid @enderror shadow-none" name="nickname" id="nickname" value="{{$vendor->nickname}}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="jabatan" class="form-label">Jabatan</label>
                                <select class="form-select shadow-none" name="jabatan" id="jabatan" required>
                                    @if ($vendor->jabatan)
                                    <option value="{{$vendor->jabatan}}" selected>{{$vendor->jabatan}}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-12" id="perusahaan-row" hidden>
                                <label for="perusahaan" class="form-label">Nama Perusahaan</label>
                                <input type="text" class="form-control @error('perusahaan') is-invalid @enderror shadow-none" name="perusahaan" id="perusahaan" value="{{$vendor->perusahaan}}">
                            </div>
                            <div class="col-md-6">
                                <label for="npwp" class="form-label">NPWP / NIK</label>
                                <input type="text" class="form-control @error('npwp') is-invalid @enderror shadow-none" name="npwp" id="npwp" value="{{$vendor->npwp}}" required>
                            </div>
                            <div class="col-md-12">
                                <label for="alamat" class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control shadow-none" name="alamat" id="alamat" rows="3" required>{{$vendor->alamat}}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary fw-bold"><i class="fa fa-university me-2"></i>Informasi Rekening & Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="bank" class="form-label">Nama Bank</label>
                                <input type="text" class="form-control shadow-none" name="bank" id="bank" value="{{$vendor->bank}}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="no_rekening" class="form-label">No. Rekening</label>
                                <input type="text" class="form-control shadow-none" name="no_rekening" id="no_rekening" value="{{$vendor->no_rekening}}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="nama_rekening" class="form-label">Nama Pemilik</label>
                                <input type="text" class="form-control shadow-none" name="nama_rekening" id="nama_rekening" value="{{$vendor->nama_rekening}}" required>
                            </div>
                            <div class="col-md-12">
                                <hr class="text-muted opacity-25">
                                <label for="pembayaran" class="form-label fw-bold">Metode Pembayaran</label>
                                <select class="form-select shadow-none" name="pembayaran" id="pembayaran">
                                    <option value="opname" {{$vendor->pembayaran == 'opname' ? 'selected' : ''}}>Opname</option>
                                    <option value="titipan" {{$vendor->pembayaran == 'titipan' ? 'selected' : ''}}>Khusus</option>
                                    <option value="titipan_khusus" {{$vendor->pembayaran == 'titipan_khusus' ? 'selected' : ''}}>Titipan Khusus</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 border-start border-primary border-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Status Kontrak</h6>
                        <select name="status" id="status" class="form-select shadow-none fw-bold text-primary">
                            <option value="aktif" {{$vendor->status == 'aktif' ? 'selected' : ''}}>AKTIF</option>
                            <option value="nonaktif" {{$vendor->status == 'nonaktif' ? 'selected' : ''}}>TIDAK AKTIF</option>
                        </select>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Kontak & Sponsor</h6>
                        <div class="mb-3">
                            <label class="form-label small">No. HP</label>
                            <input type="text" class="form-control shadow-none" name="no_hp" id="no_hp" value="{{$vendor->no_hp}}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Email</label>
                            <input type="email" class="form-control shadow-none" name="email" id="email" value="{{$vendor->email}}" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small">Sponsor</label>
                            <select class="form-select select2 shadow-none" name="sponsor_id" id="sponsor_select">
                                <option value="">-- Pilih Sponsor --</option>
                                @foreach ($sponsor as $s)
                                <option value="{{$s->id}}" {{$vendor->sponsor_id == $s->id ? 'selected' : ''}}>{{$s->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 border-start border-info border-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Konfigurasi Pajak & SO</h6>
                        <div class="bg-light p-3 rounded">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="support_operational" id="support_operational" {{$vendor->support_operational == 1 ? 'checked' : ''}}>
                                <label class="form-check-label fw-bold small" for="support_operational">Support Operational</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="ppn" id="ppn" {{$vendor->ppn == 1 ? 'checked' : ''}}>
                                <label class="form-check-label fw-bold small" for="ppn">PPN</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="pph" id="pph" {{$vendor->pph == 1 ? 'checked' : ''}} onclick="checkPphVal()">
                                <label class="form-check-label fw-bold small" for="pph">PPh</label>
                            </div>
                            <div id="pph_val_row" class="mt-3" {{$vendor->pph == 1 ? '' : 'hidden'}}>
                                <label class="form-label small fw-bold">Nilai PPh (%)</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" name="pph_val" id="pph_value" value="{{str_replace('.', ',',$vendor->pph_val) ?? ''}}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 border-start border-success border-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Batas Plafon (Limit)</h6>
                        <div class="mb-3">
                            <label class="form-label small">Plafon Cash</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">Rp</span>
                                <input type="text" class="form-control shadow-none" name="plafon_titipan" id="plafon_titipan" required value="{{number_format($vendor->plafon_titipan, 0, ',','.')}}">
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small">Plafon Storing</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">Rp</span>
                                <input type="text" class="form-control shadow-none" name="plafon_lain" id="plafon_lain" required value="{{number_format($vendor->plafon_lain, 0, ',','.')}}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12 d-flex justify-content-between">
                <a href="{{ route('vendor.index') }}" class="btn btn-link text-muted text-decoration-none">
                    <i class="fa fa-arrow-left me-1"></i> Batal & Kembali
                </a>
                <button type="submit" class="btn btn-primary px-5 shadow-sm fw-bold">
                    <i class="fa fa-save me-2"></i>Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    body { background-color: #f8fafc; }
    .card { border-radius: 15px; }
    .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; }
    .form-control, .form-select { border-radius: 8px; border: 1px solid #e2e8f0; padding: 0.6rem 0.75rem; }
    .form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .input-group-text { border: 1px solid #e2e8f0; border-radius: 8px 0 0 8px; font-weight: 600; }
</style>
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
    function checkPphVal() {
        if ($('#pph').is(':checked')) {
            $('#pph_val_row').slideDown(200).removeAttr('hidden');
            $('#pph_value').attr('required', true);
        } else {
            $('#pph_val_row').slideUp(200);
            $('#pph_value').attr('required', false).val(0);
        }
    }

    function changeTipe() {
        var type = $('#tipe-vendor').val();
        if (type === 'perusahaan') {
            $('#jabatan').html('<option value="Direktur Utama">Direktur Utama</option><option value="Direktur">Direktur</option>');
            $('#perusahaan-row').slideDown(200).removeAttr('hidden');
            $('#perusahaan').attr('required', true);
        } else if (type === 'perorangan') {
            $('#perusahaan').val('').attr('required', false);
            $('#perusahaan-row').slideUp(200);
            $('#jabatan').html('<option value="Pemilik Unit" selected>Pemilik Unit</option>');
        }
    }

    $(document).ready(function () {
        changeTipe();

        $('#sponsor_select').select2({ theme: 'bootstrap-5' });

        $('#plafon_titipan, #plafon_lain').maskMoney({
            thousands: '.', decimal: ',', precision: 0
        });

        new Cleave('#pph_value', {
            numeral: true,
            numeralDecimalMark: ',',
            delimiter: '.',
            numeralIntegerScale: 3,
            numeralDecimalScale: 2,
            numeralPositiveOnly: true,
        });

        $('#masukForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Pastikan data vendor sudah valid.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    this.submit();
                }
            })
        });
    });
</script>
@endpush
