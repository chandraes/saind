@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Ubah Biodata Vendor</u></h1>
        </div>
    </div>
    <form action="{{route('vendor.update', $vendor->id)}}" method="post" id="masukForm">
        @csrf
        @method('PATCH')
        <div class="row mt-3 mb-3">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="mb-3">
                        <label for="tipe" class="form-label">Perusahaan / Perseorangan</label>
                        <select class="form-select" name="tipe" id="tipe-vendor" onchange="changeTipe()" required>
                            <option value=""> - Pilih -</option>
                            <option value="perusahaan" {{$vendor->tipe == 'perusahaan' ? 'selected' : ''}}>Perusahaan
                            </option>
                            <option value="perorangan" {{$vendor->tipe == 'perorangan' ? 'selected' : ''}}>Perorangan
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" class="form-control @if ($errors->has('nama'))
                        is-invalid
                        @endif" value="{{$vendor->nama}}" name="nama" id="nama" placeholder="" required>
                    @if ($errors->has('nama'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('nama') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="jabatan" class="form-label">Jabatan</label>
                    <select class="form-select" name="jabatan" id="jabatan" required>
                        @if ($vendor->jabatan)
                        <option value="{{$vendor->jabatan}}" selected>{{$vendor->jabatan}}</option>
                        @endif
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
                        name="nickname" id="nickname" placeholder="" value="{{$vendor->nickname}}" required>
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
                        name="perusahaan" id="perusahaan" placeholder="" value="{{$vendor->perusahaan}}">
                    @if ($errors->has('perusahaan'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('perusahaan') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="npwp" class="form-label">NPWP / NIK</label>
                    <input type="text" class="form-control {{$errors->has('npwp') ? 'is-invalid' : ''}}" name="npwp"
                        id="npwp" placeholder="" value="{{$vendor->npwp}}" required>
                    @if ($errors->has('npwp'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('npwp') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-12 mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control {{$errors->has('alamat') ? 'is-invalid' : ''}}" name="alamat"
                        id="alamat" rows="3" required>{{$vendor->alamat}}</textarea>
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
                <div class="col-8">
                    <div class="row">
                        <div class="btn-group mb-3" role="group" data-bs-toggle="buttons">
                            <label class="btn btn-warning active">
                                <input type="checkbox" class="me-2" name="support_operational" id="support_operational"
                                    {{$vendor->support_operational == 1 ? 'checked' : ''}} autocomplete="off"> Support
                                Operational
                            </label>
                            <label class="btn btn-warning active">
                                <input type="checkbox" class="me-2" name="ppn" id="ppn" {{$vendor->ppn == 1 ? 'checked'
                                :
                                ''}}
                                autocomplete="off"> PPN
                            </label>
                            <label class="btn btn-warning">
                                <input type="checkbox" class="me-2" name="pph" id="pph" {{$vendor->pph == 1 ? 'checked'
                                :
                                ''}} autocomplete="off" onclick="checkPphVal()"> PPh
                            </label>
                        </div>
                    </div>

                </div>
                <div class="col-4" id="pph_val_row" {{$vendor->pph == 1 ? '' : 'hidden'}}>
                    <div class="row">
                        <div class="col-6">
                            <input type="text" class="form-control {{$errors->has('pph_val') ? 'is-invalid' : ''}}"
                                name="pph_val" id="pph_value" placeholder=""
                                value="{{str_replace('.', ',',$vendor->pph_val) ?? ''}}">
                            @if ($errors->has('pph_val'))
                            <span class="text-danger">
                                <strong>{{ $errors->first('pph_val') }}</strong>
                            </span>
                            @endif
                            <label for="pph_value" class="form-label">Nilai PPh (%)</label>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row mt-3 mb-3">
                <div class="col-md-4 mb-3">
                    <label for="no_hp" class="form-label">No. HP</label>
                    <input type="text" class="form-control {{$errors->has('no_hp') ? 'is-invalid' : ''}}" name="no_hp"
                        id="no_hp" placeholder="" value="{{$vendor->no_hp}}" required>
                    @if ($errors->has('no_hp'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('no_hp') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control {{$errors->has('email') ? 'is-invalid' : ''}}" name="email"
                        id="email" placeholder="" value="{{$vendor->email}}" required>
                    @if ($errors->has('email'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label for="sponsor_select" class="form-label">Sponsor</label>
                    <select class="form-select {{$errors->has('sponsor_id') ? 'is-invalid' : ''}}" name="sponsor_id"
                        id="sponsor_select">
                        <option value="">-- Pilih Sponsor --</option>
                        @foreach ($sponsor as $s)
                        <option value="{{$s->id}}" {{$vendor->sponsor_id == $s->id ? 'selected' : ''}}>{{$s->nama}}
                        </option>
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
                <label for="pembayaran" class="form-label">Pembayaran</label>
                <select class="form-select" name="pembayaran" id="pembayaran">
                    <option value="opname" {{$vendor->pembayaran == 'opname' ? 'selected' : ''}}>Opname</option>
                    <option value="titipan" {{$vendor->pembayaran == 'titipan' ? 'selected' : ''}}>Khusus</option>
                </select>
            </div>
            <hr>
            <div class="row mt-3 mb-3">
                <div class="col-6">
                    <label for="plafon_titipan" class="form-label">Plafon Cash</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Rp</span>
                        <input type="text" class="form-control @if ($errors->has('plafon_titipan'))
                        is-invalid
                    @endif" name="plafon_titipan" id="plafon_titipan" required data-thousands="."
                            value="{{number_format($vendor->plafon_titipan, 0, ',','.')}}">
                    </div>
                </div>
                <div class="col-6">
                    <label for="plafon_lain" class="form-label">Plafon Storing</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Rp</span>
                        <input type="text" class="form-control @if ($errors->has('plafon_lain'))
                            is-invalid
                        @endif" name="plafon_lain" id="plafon_lain" required data-thousands="."
                            value="{{number_format($vendor->plafon_lain, 0, ',','.')}}">
                    </div>
                </div>
            </div>
            <hr>
            <div class="row mt-3 mb-3">
                <h3 class="mb-3">Rekening Vendor</h3>
                <div class="col-md-6 mb-3">
                    <label for="no_rekening" class="form-label">No. Rekening</label>
                    <input type="text" class="form-control {{$errors->has('no_rekening') ? 'is-invalid' : ''}}"
                        name="no_rekening" id="no_rekening" placeholder="" value="{{$vendor->no_rekening}}" required>
                    @if ($errors->has('no_rekening'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('no_rekening') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="bank" class="form-label">Nama Bank</label>
                    <input type="text" class="form-control {{$errors->has('bank') ? 'is-invalid' : ''}}" name="bank"
                        id="bank" placeholder="" value="{{$vendor->bank}}" required>
                    @if ($errors->has('bank'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('bank') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nama_rekening" class="form-label">Nama Pemilik Rekening</label>
                    <input type="text" class="form-control {{$errors->has('nama_rekening') ? 'is-invalid' : ''}}"
                        name="nama_rekening" id="nama_rekening" placeholder="" value="{{$vendor->nama_rekening}}"
                        required>
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
                        <option value="aktif" {{$vendor->status == 'aktif' ? 'selected' : ''}}>Aktif</option>
                        <option value="nonaktif" {{$vendor->status == 'nonaktif' ? 'selected' : ''}}>Tidak Aktif
                        </option>
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
    function checkPphVal() {
        console.log($('#pph').is(':checked'));
        if ($('#pph').is(':checked') == true) {
            $('#pph_val_row').show();
            $('#pph_val_row').removeAttr('hidden');
            $('#pph_value').attr('required', true);
        } else {
            $('#pph_val_row').hide();
            $('#pph_value').attr('required', false);
            $('#pph_value').val(0);
        }
    }

    function changeTipe() {
        var type = $('#tipe-vendor').val();

        if (type === 'perusahaan') {
            // remove option #jabatan
            // add option "Direktur Utama" and "Direktur" to select with id="jabatan"
            $('#jabatan').html('<option value="Direktur Utama">Direktur Utama</option><option value="Direktur">Direktur</option>');
            // show #perusahaan-row
            $('#perusahaan-row').show();
            $('#perusahaan-row').removeAttr('hidden');
            $('#perusahaan').attr('required', true);

        } else if (type === 'perorangan') {
            // hide #perusahaan-row
            // set #perusahaan value to null
            $('#perusahaan').val('');
            $('#perusahaan').attr('required', false);
            $('#perusahaan-row').hide();
            // add option "Pemilik" to select with id="jabatan"
            $('#jabatan').html('<option value="Pemilik Unit" selected>Pemilik Unit</option>');
        } else {

        }
    }

    $(document).ready(function () {
        // Jalankan fungsi changeTipe saat halaman dimuat
        changeTipe();
        $('#sponsor_select').select2({
            theme: 'bootstrap-5'
        });

        $('#plafon_titipan').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0
            });
        $('#plafon_lain').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0
            });

        var nominal = new Cleave('#pph_value', {
                numeral: true,
                numeralDecimalMark: ',',
                delimiter: '.',
                numeralIntegerScale: 3,
                numeralDecimalScale: 2,
                numeralPositiveOnly: true,
            });
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
                    $('#spinner').show();
                    this.submit();
                }
            })
        });

</script>
@endpush
