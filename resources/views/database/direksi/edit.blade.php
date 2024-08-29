@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Edit Direksi</u></h1>
        </div>
    </div>
    <form action="{{route('direksi.update', $data->id)}}" method="post" enctype="multipart/form-data" id="masukForm">
        @csrf
        @method('PATCH')
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control @if ($errors->has('nama'))
                    is-invalid
                @endif" name="nama" id="nama" required value="{{$data->nama}}">
                @if ($errors->has('nama'))
                <div class="invalid-feedback">
                    {{$errors->first('nama')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="nickname" class="form-label">Nickname</label>
                <input type="text" class="form-control @if ($errors->has('nickname'))
                    is-invalid
                @endif" name="nickname" id="nickname" required value="{{$data->nickname}}">
                @if ($errors->has('nickname'))
                <div class="invalid-feedback">
                    {{$errors->first('nickname')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="jabatan" class="form-label">Jabatan</label>
                <input type="text" class="form-control @if ($errors->has('jabatan'))
                    is-invalid
                @endif" name="jabatan" id="jabatan" required value="{{$data->jabatan}}">
                @if ($errors->has('jabatan'))
                <div class="invalid-feedback">
                    {{$errors->first('nicjabatankname')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <h3>Gaji & Tunjangan</h3>
        <div class="row mt-3">
            <div class="col-md-4 mb-3">
                <label for="gaji_pokok" class="form-label">Gaji Pokok</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('gaji_pokok'))
                    is-invalid
                @endif" name="gaji_pokok" id="gaji_pokok" required data-thousands="." value="{{$data->gaji_pokok}}">
                </div>
                @if ($errors->has('gaji_pokok'))
                <div class="invalid-feedback">
                    {{$errors->first('gaji_pokok')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="tunjangan_jabatan" class="form-label">Tunjangan Jabatan</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('tunjangan_jabatan'))
                    is-invalid
                @endif" name="tunjangan_jabatan" id="tunjangan_jabatan" data-thousands="." value="{{$data->tunjangan_jabatan}}">
                </div>
                @if ($errors->has('tunjangan_jabatan'))
                <div class="invalid-feedback">
                    {{$errors->first('tunjangan_jabatan')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="tunjangan_keluarga" class="form-label">Tunjangan Keluarga</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('tunjangan_keluarga'))
                    is-invalid
                @endif" name="tunjangan_keluarga" id="tunjangan_keluarga" data-thousands="." value="{{$data->tunjangan_keluarga}}">
                </div>
                @if ($errors->has('tunjangan_keluarga'))
                <div class="invalid-feedback">
                    {{$errors->first('tunjangan_keluarga')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="nik" class="form-label">NIK</label>
                <input type="text" class="form-control @if ($errors->has('nik'))
                    is-invalid
                @endif" name="nik" id="nik" required value="{{$data->nik}}">
                @if ($errors->has('nik'))
                <div class="invalid-feedback">
                    {{$errors->first('nik')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="npwp" class="form-label">NPWP</label>
                <input type="text" class="form-control @if ($errors->has('npwp'))
                    is-invalid
                @endif" name="npwp" id="npwp" required value="{{$data->npwp}}">
                @if ($errors->has('npwp'))
                <div class="invalid-feedback">
                    {{$errors->first('npwp')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="row">
                    <label for="bpjs_tk" class="form-label" >----------------------------</label>
                    <div class="btn-group" role="group" data-bs-toggle="buttons">
                        <label class="btn btn-primary active">
                            <input type="checkbox" class="me-2" name="apa_bpjs_tk" id="apa_bpjs_tk" autocomplete="off" {{$data->apa_bpjs_tk ? 'checked' : ''}}/>
                            Ikut BPJS Tenaga Kerja
                        </label>
                        <label class="btn btn-primary">
                            <input type="checkbox" class="me-2" name="apa_bpjs_kesehatan" id="apa_bpjs_kesehatan" autocomplete="off" {{$data->apa_bpjs_kesehatan ? 'checked' : ''}} />
                            Ikut BPJS Kesehatan
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <label for="bpjs_tk" class="form-label">Nomor BPJS Tenaga Kerja</label>
                <input type="text" class="form-control @if ($errors->has('bpjs_tk'))
                    is-invalid
                @endif" name="bpjs_tk" id="bpjs_tk" required value="{{$data->bpjs_tk}}">
                @if ($errors->has('bpjs_tk'))
                <div class="invalid-feedback">
                    {{$errors->first('bpjs_tk')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="bpjs_kesehatan" class="form-label">Nomor BPJS Kesehatan</label>
                <input type="text" class="form-control @if ($errors->has('bpjs_kesehatan'))
                    is-invalid
                @endif" name="bpjs_kesehatan" id="bpjs_kesehatan" required value="{{$data->bpjs_kesehatan}}">
                @if ($errors->has('bpjs_kesehatan'))
                <div class="invalid-feedback">
                    {{$errors->first('bpjs_kesehatan')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                <input type="text" class="form-control @if ($errors->has('tempat_lahir')) is-invalid @endif" name="tempat_lahir" id="tempat_lahir" required value="{{$data->tempat_lahir}}">
                @if ($errors->has('tempat_lahir'))
                <div class="invalid-feedback">
                    {{$errors->first('tempat_lahir')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control @if ($errors->has('tanggal_lahir')) is-invalid @endif" name="tanggal_lahir" id="tanggal_lahir" required value="{{$data->tanggal_lahir}}">
                @if ($errors->has('tanggal_lahir'))
                <div class="invalid-feedback">
                    {{$errors->first('tanggal_lahir')}}
                </div>
                @endif
            </div>
            <div class="col-md-12 mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea class="form-control @if ($errors->has('alamat')) is-invalid @endif" name="alamat" id="alamat" rows="3" required>{{$data->alamat}}</textarea>
                @if ($errors->has('alamat'))
                <div class="invalid-feedback">
                    {{$errors->first('alamat')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="no_hp" class="form-label">Nomor HP</label>
                <input type="text" class="form-control @if ($errors->has('no_hp')) is-invalid @endif" name="no_hp" id="no_hp" required value="{{$data->no_hp}}">
                @if ($errors->has('no_hp'))
                <div class="invalid-feedback">
                    {{$errors->first('no_hp')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="no_wa" class="form-label">Nomor WA</label>
                <input type="text" class="form-control @if ($errors->has('no_wa')) is-invalid @endif" name="no_wa" id="no_wa" required value="{{$data->no_wa}}">
                @if ($errors->has('no_wa'))
                <div class="invalid-feedback">
                    {{$errors->first('no_wa')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="bank" class="form-label">Nama Bank</label>
                <input type="text" class="form-control @if ($errors->has('bank')) is-invalid @endif" name="bank" id="bank" value="BCA" readonly required>
                @if ($errors->has('bank'))
                <div class="invalid-feedback">
                    {{$errors->first('bank')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="no_rekening" class="form-label">Nomor Rekening</label>
                <input type="text" class="form-control @if ($errors->has('no_rekening')) is-invalid @endif" name="no_rekening" id="no_rekening" required value="{{$data->no_rekening}}">
                @if ($errors->has('no_rekening'))
                <div class="invalid-feedback">
                    {{$errors->first('no_rekening')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="nama_rekening" class="form-label">Nama Rekening</label>
                <input type="text" class="form-control @if ($errors->has('nama_rekening')) is-invalid @endif" name="nama_rekening" id="nama_rekening" required value="{{$data->nama_rekening}}">
                @if ($errors->has('nama_rekening'))
                <div class="invalid-feedback">
                    {{$errors->first('nama_rekening')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="mulai_bekerja" class="form-label">Mulai Bekerja</label>
                <input type="date" class="form-control @if ($errors->has('mulai_bekerja')) is-invalid @endif" name="mulai_bekerja" id="mulai_bekerja" required value="{{$data->mulai_bekerja}}">
                @if ($errors->has('mulai_bekerja'))
                <div class="invalid-feedback">
                    {{$errors->first('mulai_bekerja')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select @if ($errors->has('status')) is-invalid @endif" name="status" id="status" required>
                    <option value="aktif" {{$data->status === 'aktif' ? 'selected' : ''}}>Aktif</option>
                    <option value="nonaktif" {{$data->status === 'nonaktif' ? 'selected' : ''}}>Non-aktif</option>
                </select>
                @if ($errors->has('status'))
                <div class="invalid-feedback">
                    {{$errors->first('status')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="foto_ktp" class="form-label ">Foto KTP <span class="text-danger">(*Kosongkan jika tidak ingin mengganti)</span></label>
                <input type="file" class="form-control @if ($errors->has('foto_ktp')) is-invalid @endif" name="foto_ktp" id="foto_ktp">
                @if ($errors->has('foto_ktp'))
                <div class="invalid-feedback">
                    {{$errors->first('foto_ktp')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="foto_diri" class="form-label">Foto Diri <span class="text-danger">(*Kosongkan jika tidak ingin mengganti)</span></label>
                <input type="file" class="form-control @if ($errors->has('foto_diri')) is-invalid @endif" name="foto_diri" id="foto_diri">
                @if ($errors->has('foto_diri'))
                <div class="invalid-feedback">
                    {{$errors->first('foto_diri')}}
                </div>
                @endif
            </div>
        </div>
        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-success" type="submit">Simpan</button>
            <a href="{{route('karyawan.index')}}" class="btn btn-secondary" type="button">Batal</a>
          </div>
    </form>
</div>
@endsection
@push('js')
<script src="{{asset('assets/js/jquery.maskMoney.js')}}"></script>
<script>
      $(document).ready(function(){
            $('#gaji_pokok').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0,
                allowZero: true
            });
            $('#tunjangan_jabatan').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0,
                allowZero: true
            });
            $('#tunjangan_keluarga').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0,
                allowZero: true
            });
        });

        $('#masukForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah anda yakin?',
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
