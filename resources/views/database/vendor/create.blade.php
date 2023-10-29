@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Tambah Vendor</u></h1>
        </div>
    </div>
    <form action="{{route('vendor.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row mt-3 mb-3">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="mb-3">
                        <label for="tipe" class="form-label">Perusahaan / Perseorangan</label>
                        <select class="form-select" name="tipe" id="tipe-vendor" onchange="changeTipe()" required>
                            <option value=""> - Pilih -</option>
                            <option value="perusahaan">Perusahaan</option>
                            <option value="perorangan">Perorangan</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" class="form-control @if ($errors->has('nama'))
                        is-invalid
                        @endif" value="{{old('nama') ? old('nama') : ''}}" name="nama" id="nama" placeholder=""
                        required>
                    @if ($errors->has('nama'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('nama') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="jabatan" class="form-label">Jabatan</label>
                    <select class="form-select" name="jabatan" id="jabatan" required>
                    </select>
                    @if ($errors->has('jabatan'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('jabatan') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nickname" class="form-label">Nickname</label>
                    <input type="text" class="form-control {{$errors->has('nickname') ? 'is-invalid' : ''}}"
                        name="nickname" id="nickname" placeholder="" value="{{old('nickname') ? old('nickname') : ''}}"
                        required>
                    @if ($errors->has('nickname'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('nickname') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3" id="perusahaan-row" hidden>
                    <label for="perusahaan" class="form-label">Nama Perusahaan</label>
                    <input type="text" class="form-control {{$errors->has('perusahaan') ? 'is-invalid' : ''}}"
                        name="perusahaan" id="perusahaan" placeholder=""
                        value="{{old('perusahaan') ? old('perusahaan') : ''}}">
                    @if ($errors->has('perusahaan'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('perusahaan') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="npwp" class="form-label">NPWP / NIK</label>
                    <input type="text" class="form-control {{$errors->has('npwp') ? 'is-invalid' : ''}}" name="npwp"
                        id="npwp" placeholder="" value="{{old('npwp') ? old('npwp') : ''}}" required>
                    @if ($errors->has('npwp'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('npwp') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-12 mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control {{$errors->has('alamat') ? 'is-invalid' : ''}}" name="alamat"
                        id="alamat" rows="3" required>{{old('alamat') ? old('alamat') : ''}}</textarea>
                    @if ($errors->has('alamat'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('alamat') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            <hr>
            <h3>
                Informasi SO, PPN & PPh
            </h3>
            <div class="row mt-3 mb-3">
                <div class="btn-group mb-3" role="group" data-bs-toggle="buttons">
                    <label class="btn btn-warning active">
                        <input type="checkbox" class="me-2" name="support_operational" id="support_operational" autocomplete="off"> Support Operational
                    </label>
                    <label class="btn btn-warning active">
                        <input type="checkbox" class="me-2" name="ppn" id="ppn" autocomplete="off" > PPN & PPh
                    </label>
                </div>
            </div>
            <hr>
            <div class="row mt-3 mb-3">
                <div class="col-md-4 mb-3">
                    <label for="no_hp" class="form-label">No. HP</label>
                    <input type="text" class="form-control {{$errors->has('no_hp') ? 'is-invalid' : ''}}" name="no_hp"
                        id="no_hp" placeholder="" value="{{old('no_hp') ? old('no_hp') : ''}}" required>
                    @if ($errors->has('no_hp'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('no_hp') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control {{$errors->has('email') ? 'is-invalid' : ''}}" name="email"
                        id="email" placeholder="" value="{{old('email') ? old('email') : ''}}" required>
                    @if ($errors->has('email'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label for="sponsor_select" class="form-label">Sponsor</label>
                    <select class="form-select {{$errors->has('sponsor_id') ? 'is-invalid' : ''}}" name="sponsor_id"
                        id="sponsor_select" required>
                        <option value="">-- Pilih Sponsor --</option>
                        @foreach ($sponsor as $s)
                        <option value="{{$s->id}}">{{$s->nama}}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('sponsor_id'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('sponsor_id') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            <hr>
            <div class="row mt-3 mb-3">
                <div class="col-12">
                    <label for="pembayaran" class="form-label">Pembayaran</label>
                    <select class="form-select" name="pembayaran" id="pembayaran">
                        <option value="opname">Opname</option>
                        <option value="titipan">Titipan</option>
                    </select>
                </div>
            </div>
            <hr>
            <div class="row mt-3 mb-3">
                <div class="col-6">
                    <label for="plafon_titipan" class="form-label">Plafon Cash</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Rp</span>
                        <input type="text" class="form-control @if ($errors->has('plafon_titipan'))
                        is-invalid
                    @endif" name="plafon_titipan" id="plafon_titipan" required data-thousands=".">
                    </div>
                </div>
                <div class="col-6">
                    <label for="Plafon lain" class="form-label">Plafon Storing</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Rp</span>
                        <input type="text" class="form-control @if ($errors->has('Plafon lain'))
                            is-invalid
                        @endif" name="Plafon lain" id="Plafon lain" required data-thousands=".">
                    </div>
                </div>
            </div>
            <hr>
            <div class="row mt-3 mb-3">
                <h3 class="mb-3">Rekening Vendor</h3>
                <div class="col-md-6 mb-3">
                    <label for="no_rekening" class="form-label">No. Rekening</label>
                    <input type="text" class="form-control {{$errors->has('no_rekening') ? 'is-invalid' : ''}}"
                        name="no_rekening" id="no_rekening" placeholder=""
                        value="{{old('no_rekening') ? old('no_rekening') : ''}}" required>
                    @if ($errors->has('no_rekening'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('no_rekening') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="bank" class="form-label">Nama Bank</label>
                    <input type="text" class="form-control {{$errors->has('bank') ? 'is-invalid' : ''}}" name="bank"
                        id="bank" placeholder="" value="BCA" readonly required>
                    @if ($errors->has('bank'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('bank') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nama_rekening" class="form-label">Nama Pemilik Rekening</label>
                    <input type="text" class="form-control {{$errors->has('nama_rekening') ? 'is-invalid' : ''}}"
                        name="nama_rekening" id="nama_rekening" placeholder=""
                        value="{{old('nama_rekening') ? old('nama_rekening') : ''}}" required>
                    @if ($errors->has('nama_rekening'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('nama_rekening') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status"
                        class="form-control {{$errors->has('status') ? 'is-invalid' : ''}}">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Tidak Aktif</option>
                    </select>
                    @if ($errors->has('status'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('status') }}</strong>
                    </span>
                    @endif
                </div>

            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-block btn-primary">Simpan</button>
                    <a href="{{ route('vendor.index') }}" class="btn btn-block btn-danger">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
    function changeTipe() {
        var type = $('#tipe-vendor').val();

        if (type === 'perusahaan') {
            // remove option #jabatan
            // add option "Direktur Utama" and "Direktur" to select with id="jabatan"
            $('#jabatan').html('<option selected value="Direktur Utama">Direktur Utama</option><option value="Direktur">Direktur</option>');
            // show #perusahaan-row
            $('#perusahaan-row').show();
            $('#perusahaan-row').removeAttr('hidden');
            $('#perusahaan').attr('required', true);

        } else if (type === 'perorangan') {
            // hide #perusahaan-row
            // set #perusahaan value to null
            $('#perusahaan').val('');
            $('#perusahaan-row').hide();
            $('#perusahaan').attr('required', false);
            // add option "Pemilik" to select with id="jabatan"
            $('#jabatan').html('<option selected value="Pemilik Unit" selected>Pemilik Unit</option>');
        } else {

        }
    }

    $(document).ready(function () {
        // Jalankan fungsi changeTipe saat halaman dimuat
        changeTipe();
        $('#sponsor_select').select2({
            theme: 'bootstrap-5'
        });

        $('#plafon_titipan').maskMoney();
        $('#plafon_lain').maskMoney();

    });

    $('#masukForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Data yang anda masukan sudah benar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            })
        });

        function checkPpnPph() {
            if ($('#ppn').is(':checked')) {
                // make pph checked
                $('#pph').attr('checked', true);
            } else {
                $('#pph').attr('checked', false);
            }

            if ($('#pph').is(':checked')) {
                // make ppn checked
                $('#ppn').attr('checked', true);
            } else {
                $('#ppn').attr('checked', false);
            }
        }
</script>
@endpush
