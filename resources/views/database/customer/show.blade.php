@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Biodata Customer</u></h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control @if ($errors->has('nama')) is-invalid @endif" name="nama"
                    id="nama" aria-describedby="helpId" placeholder="" value="{{$data->nama}}" disabled>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="singkatan" class="form-label">Singkatan</label>
                <input type="text" class="form-control @if ($errors->has('singkatan')) is-invalid @endif"
                    name="singkatan" id="singkatan" aria-describedby="helpId" placeholder=""
                    value="{{$data->singkatan}}" disabled>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="npwp" class="form-label">NPWP</label>
                <input type="text" class="form-control @if ($errors->has('npwp')) is-invalid @endif" name="npwp"
                    id="npwp" aria-describedby="helpId" placeholder="" value="{{$data->npwp}}" disabled>
            </div>
        </div>
        <div class="col-md-12">
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea class="form-control @if ($errors->has('alamat')) is-invalid @endif" name="alamat" id="alamat"
                    rows="3" disabled>{{$data->alamat}}</textarea>
            </div>
        </div>
    </div>
    <br>
    <hr>
    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label for="contact_person" class="form-label">Contact Person</label>
                <input type="text" class="form-control @if ($errors->has('contact_person')) is-invalid @endif"
                    name="contact_person" id="contact_person" aria-describedby="helpId" placeholder=""
                    value="{{$data->contact_person}}" disabled>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="jabatan" class="form-label">Jabatan</label>
                <input type="text" class="form-control @if ($errors->has('jabatan')) is-invalid @endif" name="jabatan"
                    id="jabatan" aria-describedby="helpId" placeholder="" value="{{$data->jabatan}}" disabled>
            </div>
        </div>
    </div>
    <br>
    <hr>
    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label for="no_hp" class="form-label">Nomor HP</label>
                <input type="text" class="form-control @if ($errors->has('no_hp')) is-invalid @endif" name="no_hp"
                    id="no_hp" aria-describedby="helpId" placeholder="" value="{{$data->no_hp}}" disabled>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="no_wa" class="form-label">Nomor WA</label>
                <input type="text" class="form-control @if ($errors->has('no_wa')) is-invalid @endif" name="no_wa"
                    id="no_wa" aria-describedby="helpId" placeholder="" value="{{$data->no_wa}}" disabled>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control @if ($errors->has('email')) is-invalid @endif" name="email"
                    id="email" aria-describedby="helpId" placeholder="" value="{{$data->email}}" disabled>
            </div>
        </div>
    </div>
    <br>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <div class="mb-3">
                <label for="rute" class="form-label">Rute</label>
                <select class="form-select select2 @if ($errors->has('rute')) is-invalid @endif" name="rute[]" id="rute"
                    multiple disabled>
                    <option value="">Pilih Rute</option>
                    @foreach ($rute as $item)
                    <option value="{{$item->id}}" {{$data->rute->where('id', $item->id)->first() ? 'selected' :
                        ''}}>{{$item->nama}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <hr>
    <div class="row mt-3 mb-3">
        <div class="col-md-6">
            <div class="row">
                <h3 class="mb-3">Informasi PPN & PPh</h3>
                <div class="btn-group mb-3" role="group" data-bs-toggle="buttons">
                    <label class="btn btn-warning active">
                        <input type="checkbox" class="me-2" name="ppn" id="ppn" {{$data->ppn == 1 ? 'checked' : ''}}
                        autocomplete="off" disabled> PPN & PPh
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <h3 class="mb-3">Gross, Tarra, Netto</h3>
                <div class="btn-group mb-3" role="group" data-bs-toggle="buttons">
                    <label class="btn btn-primary active">
                        <input type="checkbox" class="me-2" name="gt_muat" id="gt_muat" {{$data->gt_muat == 1 ? 'checked' : ''}}
                        autocomplete="off" disabled> Tonase Muat
                    </label>
                    <label class="btn btn-primary">
                        <input type="checkbox" class="me-2" name="gt_bongkar" id="gt_bongkar" {{$data->gt_bongkar == 1 ? 'checked' : ''}}
                        autocomplete="off" disabled> Tonase Bongkar
                    </label>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <h3>CSR</h3>
    <div class="row mt-3 mb-3">
        <div class="btn-group mb-3" role="group" data-bs-toggle="buttons">
            <label class="btn btn-success active">
                <input type="checkbox" class="me-2" name="csr" id="csr" {{$data->csr == 1 ? 'checked' : ''}}
                autocomplete="off" > CSR
            </label>
        </div>
    </div>
    <div class="row mt-3 mb-3" id="rek_csr" @if($data->csr == 0) hidden @endif>
        <div class="col-6">
            <label for="harga_csr_ata">Harga CSR > 50 km</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">Rp</span>
                <input type="text" class="form-control @if ($errors->has('harga_csr_atas'))
                is-invalid
            @endif" name="harga_csr_atas" id="harga_csr_atas" data-thousands="."
                    value="{{number_format($data->harga_csr_atas, 0, ',','.')}}" disabled>
            </div>
        </div>

        <div class="col-6">
            <label for="harga_csr_ata">Harga CSR <= 50 km</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Rp</span>
                        <input type="text" class="form-control @if ($errors->has('harga_csr_bawah'))
                is-invalid
            @endif" name="harga_csr_bawah" id="harga_csr_bawah" data-thousands="."
                            value="{{number_format($data->harga_csr_bawah, 0, ',','.')}}">
                    </div>
        </div>
        <div class="col-4">
            <div class="mb-3">
                <label for="csr_bank" class="form-label">BANK CSR</label>
                <input type="text" class="form-control" name="csr_bank" id="csr_bank" aria-describedby="helpId"
                    placeholder="" value="{{$data->csr_bank}}">
            </div>
        </div>
        <div class="col-4">
            <div class="mb-3">
                <label for="csr_transfer_ke" class="form-label">NAMA REKENING CSR</label>
                <input type="text" class="form-control" name="csr_transfer_ke" id="csr_transfer_ke"
                    aria-describedby="helpId" placeholder="" value="{{$data->csr_transfer_ke}}">
            </div>
        </div>
        <div class="col-4">
            <div class="mb-3">
                <label for="csr_no_rekening" class="form-label">NOMOR REKENING CSR</label>
                <input type="text" class="form-control" name="csr_no_rekening" id="csr_no_rekening"
                    aria-describedby="helpId" placeholder="" value="{{$data->csr_no_rekening}}">
            </div>
        </div>
    </div>
    <br>
    <hr>
    <div class="row mt-3 mb-3">
        <h3 class="mb-3">Tagihan</h3>
        <div class="mb-3">
            <label for="tagihan_dari" class="form-label">Di ambil dari</label>
            <select class="form-select" name="tagihan_dari" id="tagihan_dari" disabled>
                <option value="1" {{$data->tagihan_dari == 1 ? 'selected' : ''}}>Tonase Muat</option>
                <option value="2" {{$data->tagihan_dari == 2 ? 'selected' : ''}}>Tonase Bongkar</option>
            </select>
            @if ($errors->has('tagihan_dari'))
            <span class="text-danger">
                <strong>{{ $errors->first('tagihan_dari') }}</strong>
            </span>
            @endif
        </div>
    </div>
    <hr>
    <br>
    <div class="d-grid gap-3 mt-3">
        <a href="{{route('customer.edit', $data->id)}}" class="btn btn-primary">Edit</a>
        <a type="button" class="btn btn-secondary" href="{{route('customer.index')}}">Keluar</a>
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

<script src="{{asset('assets/js/jquery.min.js')}}"></script>
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script>
    $(document).ready(function() {
            $('#rute').select2();
        });
</script>
@endpush
