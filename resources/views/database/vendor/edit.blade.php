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
                            <option value="perusahaan" {{$vendor->tipe == 'perusahaan' ? 'selected' : ''}}>Perusahaan</option>
                            <option value="perorangan" {{$vendor->tipe == 'perorangan' ? 'selected' : ''}}>Perorangan</option>
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
                    <input type="text" class="form-control {{$errors->has('nickname') ? 'is-invalid' : ''}}" name="nickname" id="nickname" placeholder="" value="{{$vendor->nickname}}" required>
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
                    <input type="text" class="form-control {{$errors->has('perusahaan') ? 'is-invalid' : ''}}" name="perusahaan" id="perusahaan" placeholder="" value="{{$vendor->perusahaan}}" >
                    @if ($errors->has('perusahaan'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('perusahaan') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="npwp" class="form-label">NPWP / NIK</label>
                    <input type="text" class="form-control {{$errors->has('npwp') ? 'is-invalid' : ''}}" name="npwp" id="npwp" placeholder="" value="{{$vendor->npwp}}" required>
                    @if ($errors->has('npwp'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('npwp') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-12 mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control {{$errors->has('alamat') ? 'is-invalid' : ''}}" name="alamat" id="alamat" rows="3" required>{{$vendor->alamat}}</textarea>
                    @if ($errors->has('alamat'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('alamat') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            <hr>
            <div class="row mt-3 mb-3">
                <div class="col-md-4 mb-3">
                    <label for="no_hp" class="form-label">No. HP</label>
                    <input type="text" class="form-control {{$errors->has('no_hp') ? 'is-invalid' : ''}}" name="no_hp" id="no_hp" placeholder="" value="{{$vendor->no_hp}}" required>
                    @if ($errors->has('no_hp'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('no_hp') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control {{$errors->has('email') ? 'is-invalid' : ''}}" name="email" id="email" placeholder="" value="{{$vendor->email}}" required>
                    @if ($errors->has('email'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label for="sponsor_select" class="form-label">Sponsor</label>
                    <select class="form-select {{$errors->has('sponsor_id') ? 'is-invalid' : ''}}" name="sponsor_id" id="sponsor_select">
                        <option value="">-- Pilih Sponsor --</option>
                        @foreach ($sponsor as $s)
                        <option value="{{$s->id}}" {{$vendor->sponsor_id == $s->id ? 'selected' : ''}}>{{$s->nama}}</option>
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
                <h3 class="mb-3">Rekening Vendor</h3>
                <div class="col-md-6 mb-3">
                    <label for="no_rekening" class="form-label">No. Rekening</label>
                    <input type="text" class="form-control {{$errors->has('no_rekening') ? 'is-invalid' : ''}}" name="no_rekening" id="no_rekening" placeholder="" value="{{$vendor->no_rekening}}" required>
                    @if ($errors->has('no_rekening'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('no_rekening') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="bank" class="form-label">Nama Bank</label>
                    <input type="text" class="form-control {{$errors->has('bank') ? 'is-invalid' : ''}}" name="bank" id="bank" placeholder="" value="BCA" readonly required>
                    @if ($errors->has('bank'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('bank') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nama_rekening" class="form-label">Nama Pemilik Rekening</label>
                    <input type="text" class="form-control {{$errors->has('nama_rekening') ? 'is-invalid' : ''}}" name="nama_rekening" id="nama_rekening" placeholder="" value="{{$vendor->nama_rekening}}"
                        required>
                        @if ($errors->has('nama_rekening'))
                        <span class="text-danger">
                            <strong>{{ $errors->first('nama_rekening') }}</strong>
                        </span>
                        @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control {{$errors->has('status') ? 'is-invalid' : ''}}">
                        <option value="aktif" {{$vendor->status == 'aktif' ? 'selected' : ''}}>Aktif</option>
                        <option value="nonaktif" {{$vendor->status == 'nonaktif' ? 'selected' : ''}}>Tidak Aktif</option>
                    </select>
                    @if ($errors->has('status'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('status') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
            <hr>
            <div class="row mt-3 mb-3">
                <h3 class="mb-3">Rekening Uang Jalan</h3>
                <div class="col-md-4 mb-3">
                    <label for="no_rekening_uj" class="form-label">No. Rekening Uang Jalan</label>
                    <input type="text" class="form-control {{$errors->has('no_rekening_uj') ? 'is-invalid' : ''}}" name="no_rekening_uj" id="no_rekening_uj" placeholder="" value="{{$vendor->no_rekening_uj}}" required>
                    @if ($errors->has('no_rekening_uj'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('no_rekening_uj') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label for="bank_uj" class="form-label">Nama Bank Uang Jalan</label>
                    <input type="text" class="form-control {{$errors->has('bank_uj') ? 'is-invalid' : ''}}" name="bank_uj" id="bank_uj" placeholder="" value="{{$vendor->bank_uj}}" required>
                    @if ($errors->has('bank_uj'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('bank_uj') }}</strong>
                    </span>
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label for="nama_rekening_uj" class="form-label">Nama Pemilik Rekening Uang Jalan</label>
                    <input type="text" class="form-control {{$errors->has('nama_rekening_uj') ? 'is-invalid' : ''}}" name="nama_rekening_uj" id="nama_rekening_uj" placeholder="" value="{{$vendor->nama_rekening_uj}}" required>
                    @if ($errors->has('nama_rekening_uj'))
                    <span class="text-danger">
                        <strong>{{ $errors->first('nama_rekening_uj') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
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
            $('#jabatan').html('<option value="Direktur Utama">Direktur Utama</option><option value="Direktur">Direktur</option>');
            // show #perusahaan-row
            $('#perusahaan-row').show();
            $('#perusahaan-row').removeAttr('hidden');
            $('#perusahaan').attr('required', true);

        } else if (type === 'perorangan') {
            // hide #perusahaan-row
            // set #perusahaan value to null
            $('#perusahaan').val('');
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

</script>
@endpush
