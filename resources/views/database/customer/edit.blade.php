@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('customer.index')}}">Customer</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Customer</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark">
                <i class="fa fa-user-edit text-primary me-2"></i>Edit Data Customer
            </h1>
        </div>
    </div>

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>
        <strong>Error!</strong> {{session('error')}}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{route('customer.update', $data->id)}}" method="post" id="masukForm">
        @csrf
        @method('PATCH')

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary fw-bold"><i class="fa fa-building me-2"></i>Informasi Perusahaan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="nama" class="form-label">Nama Perusahaan</label>
                                <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" id="nama" value="{{old('nama', $data->nama)}}" required placeholder="PT. Nama Customer">
                                @error('nama') <div class="invalid-feedback">{{$message}}</div> @enderror
                            </div>
                            <div class="col-md-5">
                                <label for="singkatan" class="form-label">Singkatan/Alias</label>
                                <input type="text" class="form-control @error('singkatan') is-invalid @enderror" name="singkatan" id="singkatan" value="{{old('singkatan', $data->singkatan)}}" required placeholder="Contoh: ABC">
                                @error('singkatan') <div class="invalid-feedback">{{$message}}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="npwp" class="form-label">Nomor NPWP</label>
                                <input type="text" class="form-control @error('npwp') is-invalid @enderror" name="npwp" id="npwp" value="{{old('npwp', $data->npwp)}}" required placeholder="00.000.000.0-000.000">
                                @error('npwp') <div class="invalid-feedback">{{$message}}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label for="alamat" class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control @error('alamat') is-invalid @enderror" name="alamat" id="alamat" rows="2" required>{{old('alamat', $data->alamat)}}</textarea>
                                @error('alamat') <div class="invalid-feedback">{{$message}}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary fw-bold"><i class="fa fa-address-book me-2"></i>Kontak & Operasional</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="contact_person" class="form-label">Nama Kontak (CP)</label>
                                <input type="text" class="form-control @error('contact_person') is-invalid @enderror" name="contact_person" id="contact_person" required value="{{old('contact_person', $data->contact_person)}}">
                            </div>
                            <div class="col-md-6">
                                <label for="jabatan" class="form-label">Jabatan</label>
                                <input type="text" class="form-control @error('jabatan') is-invalid @enderror" name="jabatan" id="jabatan" required value="{{old('jabatan', $data->jabatan)}}">
                            </div>
                            <div class="col-md-4">
                                <label for="no_hp" class="form-label">No. HP</label>
                                <input type="text" class="form-control @error('no_hp') is-invalid @enderror" name="no_hp" id="no_hp" required value="{{old('no_hp', $data->no_hp)}}">
                            </div>
                            <div class="col-md-4">
                                <label for="no_wa" class="form-label">No. WhatsApp</label>
                                <input type="text" class="form-control @error('no_wa') is-invalid @enderror" name="no_wa" id="no_wa" required value="{{old('no_wa', $data->no_wa)}}">
                            </div>
                            <div class="col-md-4">
                                <label for="email" class="form-label">Alamat Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" required value="{{old('email', $data->email)}}">
                            </div>
                            <div class="col-md-12 mt-3">
                                <label for="rute" class="form-label fw-bold">Akses Rute Perjalanan</label>
                                <select class="form-select select2 @error('rute') is-invalid @enderror" name="rute[]" id="rute" required multiple>
                                    @foreach ($rute as $item)
                                    <option value="{{$item->id}}" {{$data->rute->where('id', $item->id)->first() ? 'selected' : ''}}>{{$item->nama}}</option>
                                    @endforeach
                                </select>
                                @error('rute') <div class="invalid-feedback">{{$message}}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 border-start border-primary border-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Fitur Khusus Bisnis</h6>
                        <div class="bg-light p-3 rounded shadow-sm">
                            <div class="form-check custom-checkbox mb-3">
                                <input class="form-check-input border-primary" type="checkbox" name="is_kompensasi_jr" id="is_kompensasi_jr" {{ old('is_kompensasi_jr', $data->is_kompensasi_jr) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_kompensasi_jr">
                                    <i class="fa fa-road me-2 text-primary"></i>Kompensasi JR
                                </label>
                            </div>
                            <div class="form-check custom-checkbox mb-3">
                                <input class="form-check-input border-primary" type="checkbox" name="is_penyesuaian_bbm" id="is_penyesuaian_bbm" {{ old('is_penyesuaian_bbm', $data->is_penyesuaian_bbm) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_penyesuaian_bbm">
                                    <i class="fa fa-automobile me-2 text-primary"></i>Penyesuaian BBM
                                </label>
                            </div>
                            <div class="form-check custom-checkbox">
                                <input class="form-check-input border-primary" type="checkbox" name="is_achievement" id="is_achievement" {{ old('is_achievement', $data->is_achievement) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_achievement">
                                    <i class="fa fa-trophy me-2 text-primary"></i>Achievement
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 border-start border-info border-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Gross, Tarra, Netto (Tonase)</h6>
                        <p class="small text-muted mb-3 fst-italic">* Pilih tipe tonase yang digunakan pada nota.</p>
                        <div class="bg-light p-3 rounded shadow-sm">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="gt_muat" id="gt_muat" {{ old('gt_muat', $data->gt_muat) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="gt_muat">Tonase Muat</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="gt_bongkar" id="gt_bongkar" {{ old('gt_bongkar', $data->gt_bongkar) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="gt_bongkar">Tonase Bongkar</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 border-start border-warning border-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Aspek Perpajakan</h6>
                        <div class="form-check form-switch p-0 ps-5 ms-2">
                            <input class="form-check-input" type="checkbox" name="ppn" id="ppn" style="transform: scale(1.3);" {{ old('ppn', $data->ppn) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold ms-2" for="ppn">Aktifkan PPN & PPh</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 border-start border-success border-4">
            <div class="card-body py-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-1 text-success fw-bold"><i class="fa fa-leaf me-2"></i>Program CSR</h5>
                        <p class="text-muted small mb-0">Aktifkan untuk pengelolaan dana CSR khusus pelanggan ini.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input" type="checkbox" name="csr" id="csr" style="transform: scale(1.3);" {{ old('csr', $data->csr) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold ms-2 text-success" for="csr">
                                Aktifkan CSR
                            </label>
                        </div>
                    </div>
                </div>

                <div id="rek_csr" class="mt-4 pt-4 border-top" style="{{ old('csr', $data->csr) == 1 ? '' : 'display:none;' }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Harga CSR (> 50 km)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="text" class="form-control" name="harga_csr_atas" id="harga_csr_atas" value="{{number_format(old('harga_csr_atas', $data->harga_csr_atas), 0, ',', '.')}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Harga CSR (≤ 50 km)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="text" class="form-control" name="harga_csr_bawah" id="harga_csr_bawah" value="{{number_format(old('harga_csr_bawah', $data->harga_csr_bawah), 0, ',', '.')}}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama Bank</label>
                            <input type="text" class="form-control" name="csr_bank" id="csr_bank" value="{{old('csr_bank', $data->csr_bank)}}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Atas Nama Rekening</label>
                            <input type="text" class="form-control" name="csr_transfer_ke" id="csr_transfer_ke" value="{{old('csr_transfer_ke', $data->csr_transfer_ke)}}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nomor Rekening</label>
                            <input type="text" class="form-control" name="csr_no_rekening" id="csr_no_rekening" value="{{old('csr_no_rekening', $data->csr_no_rekening)}}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary fw-bold"><i class="fa fa-file-invoice-dollar me-2"></i>Pengaturan Tagihan & Penalty</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="tagihan_dari" class="form-label fw-bold">Dasar Pengambilan Tagihan</label>
                        <select class="form-select @error('tagihan_dari') is-invalid @enderror" name="tagihan_dari" id="tagihan_dari" required>
                            <option value="1" {{old('tagihan_dari', $data->tagihan_dari) == 1 ? 'selected' : ''}}>Tonase Muat</option>
                            <option value="2" {{old('tagihan_dari', $data->tagihan_dari) == 2 ? 'selected' : ''}}>Tonase Bongkar</option>
                        </select>
                        @error('tagihan_dari') <div class="invalid-feedback">{{$message}}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="nominal_penalty" class="form-label fw-bold">Nominal Penalty</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="text" class="form-control @error('nominal_penalty') is-invalid @enderror" name="nominal_penalty" id="nominal_penalty" value="{{old('nominal_penalty', $data->nominal_penalty)}}">
                            @error('nominal_penalty') <div class="invalid-feedback">{{$message}}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded border border-dashed bg-light">
                    <h6 class="fw-bold mb-3 text-secondary text-uppercase small"><i class="fa fa-table me-2"></i>Kolom Tabel Tagihan yang Ditampilkan</h6>
                    <div class="row g-3">
                        @php
                            $columns = [
                                'tanggal_muat' => 'Tgl Muat',
                                'nota_muat' => 'Nota Muat',
                                'tonase' => 'Tonase',
                                'tanggal_bongkar' => 'Tgl Bongkar',
                                'selisih' => 'Selisih'
                            ];
                        @endphp
                        @foreach($columns as $key => $label)
                        <div class="col-md-auto col-6">
                            <div class="form-check custom-checkbox">
                                <input class="form-check-input" type="checkbox" name="{{$key}}" id="t_{{$key}}" {{ old($key, $data->$key) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="t_{{$key}}">{{$label}}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 mb-5">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <a href="{{route('customer.index')}}" class="btn btn-link text-muted text-decoration-none">
                    <i class="fa fa-arrow-left me-1"></i> Batal & Kembali
                </a>
                <div>
                    <button type="submit" class="btn btn-primary px-5 py-2 shadow border-0">
                        <i class="fa fa-save me-2"></i>Perbarui Data
                    </button>
                </div>
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
    .form-label { font-size: 0.875rem; font-weight: 600; color: #334155; }
    .form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
    .input-group-text { border-radius: 8px 0 0 8px; font-weight: 600; color: #64748b; }
    .form-switch .form-check-input { cursor: pointer; }
    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
    /* Checkbox Styling */
    .custom-checkbox .form-check-input { width: 1.25em; height: 1.25em; margin-top: 0.15em; cursor: pointer; }
    .custom-checkbox .form-check-label { cursor: pointer; }
</style>
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script>
    $(document).ready(function() {
        // Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: "Pilih Rute Perjalanan",
            allowClear: true
        });

        // Cleave for Penalty
        new Cleave('#nominal_penalty', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });

        // MaskMoney for CSR
        const moneyOptions = { thousands: '.', decimal: ',', precision: 0, allowZero: true };
        $('#harga_csr_atas').maskMoney(moneyOptions);
        $('#harga_csr_bawah').maskMoney(moneyOptions);

        // CSR Toggle Logic
        $('#csr').on('change', function() {
            const checked = $(this).is(':checked');
            if (checked) {
                $('#rek_csr').slideDown(400);
                $('#rek_csr input').attr('required', true);
            } else {
                $('#rek_csr').slideUp(300);
                $('#rek_csr input').attr('required', false);
            }
        });

        // Ensure fields are required if CSR is active on page load
        if ($('#csr').is(':checked')) {
            $('#rek_csr input').attr('required', true);
        } else {
            $('#rek_csr input').attr('required', false);
        }

        // Submit Confirmation
        $('#masukForm').on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Perbarui Data Customer?',
                text: "Pastikan seluruh data perubahan sudah sesuai.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Perbarui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    this.submit();
                }
            });
        });
    });
</script>
@endpush
