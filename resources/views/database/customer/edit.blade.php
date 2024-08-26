@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Edit Customer</u></h1>
        </div>
    </div>
    @foreach ($errors as $item)
    {{$item}}
    @endforeach
    @if (session('error'))
    <div class="row">
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>
                {{session('error')}}
            </strong>
        </div>
    </div>
    @endif
    <form action="{{route('customer.update', $data->id)}}" method="post" id="masukForm">
        @csrf
        @method('PATCH')
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" class="form-control @if ($errors->has('nama')) is-invalid @endif" name="nama"
                        id="nama" aria-describedby="helpId" required placeholder="" value="{{$data->nama}}">
                    @if ($errors->has('nama'))
                    <div class="invalid-feedback">
                        {{$errors->first('nama')}}
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="singkatan" class="form-label">Singkatan</label>
                    <input type="text" class="form-control @if ($errors->has('singkatan')) is-invalid @endif"
                        name="singkatan" id="singkatan" aria-describedby="helpId" required placeholder=""
                        value="{{$data->singkatan}}">
                    @if ($errors->has('singkatan'))
                    <div class="invalid-feedback">
                        {{$errors->first('singkatan')}}
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="npwp" class="form-label">NPWP</label>
                    <input type="text" class="form-control @if ($errors->has('npwp')) is-invalid @endif" name="npwp"
                        id="npwp" aria-describedby="helpId" placeholder="" required value="{{$data->npwp}}">
                    @if ($errors->has('npwp'))
                    <div class="invalid-feedback">
                        {{$errors->first('npwp')}}
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control @if ($errors->has('alamat')) is-invalid @endif" name="alamat"
                        id="alamat" rows="3">{{$data->alamat}}</textarea>
                    @if ($errors->has('alamat'))
                    <div class="invalid-feedback">
                        {{$errors->first('alamat')}}
                    </div>
                    @endif
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
                        name="contact_person" id="contact_person" required aria-describedby="helpId" placeholder=""
                        value="{{$data->contact_person}}">
                    @if ($errors->has('contact_person'))
                    <div class="invalid-feedback">
                        {{$errors->first('contact_person')}}
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="jabatan" class="form-label">Jabatan</label>
                    <input type="text" class="form-control @if ($errors->has('jabatan')) is-invalid @endif"
                        name="jabatan" id="jabatan" required aria-describedby="helpId" placeholder=""
                        value="{{$data->jabatan}}">
                    @if ($errors->has('jabatan'))
                    <div class="invalid-feedback">
                        {{$errors->first('jabatan')}}
                    </div>
                    @endif
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
                        id="no_hp" required aria-describedby="helpId" placeholder="" value="{{$data->no_hp}}">
                    @if ($errors->has('no_hp'))
                    <div class="invalid-feedback">
                        {{$errors->first('no_hp')}}
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="no_wa" class="form-label">Nomor WA</label>
                    <input type="text" class="form-control @if ($errors->has('no_wa')) is-invalid @endif" name="no_wa"
                        id="no_wa" required aria-describedby="helpId" placeholder="" value="{{$data->no_wa}}">
                    @if ($errors->has('no_wa'))
                    <div class="invalid-feedback">
                        {{$errors->first('no_wa')}}
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control @if ($errors->has('email')) is-invalid @endif" name="email"
                        id="email" required aria-describedby="helpId" placeholder="" value="{{$data->email}}">
                    @if ($errors->has('email'))
                    <div class="invalid-feedback">
                        {{$errors->first('email')}}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <br>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="rute" class="form-label">Rute</label>
                    <select class="form-select select2 @if ($errors->has('rute')) is-invalid @endif" name="rute[]"
                        id="rute" required multiple>
                        <option value="">Pilih Rute</option>
                        @foreach ($rute as $item)
                        <option value="{{$item->id}}" {{$data->rute->where('id', $item->id)->first() ? 'selected' :
                            ''}}>{{$item->nama}}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('rute'))
                    <div class="invalid-feedback">
                        {{$errors->first('rute')}}
                    </div>
                    @endif
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
                                autocomplete="off"> PPN & PPh
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
                                autocomplete="off"> Tonase Muat
                            </label>
                            <label class="btn btn-primary">
                                <input type="checkbox" class="me-2" name="gt_bongkar" id="gt_bongkar" {{$data->gt_bongkar == 1 ? 'checked' : ''}}
                                autocomplete="off"> Tonase Bongkar
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row mt-3 mb-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tagihan_dari" class="form-label"> Tagihan Di ambil dari</label>
                        <select class="form-select" name="tagihan_dari" id="tagihan_dari" required>
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
                <div class="col-md-6 mb-3">
                    <label for="nominal_penalty" class="form-label">Nominal Pinalty</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Rp</span>
                        <input type="text" class="form-control @if ($errors->has('nominal_penalty'))
                        is-invalid
                    @endif" name="nominal_penalty" id="nominal_penalty" value="{{$data->nominal_penalty}}">
                      </div>
                    @if ($errors->has('nominal_penalty'))
                    <div class="invalid-feedback">
                        {{$errors->first('nominal_penalty')}}
                    </div>
                    @endif
                </div>

            </div>
            <br>
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
                            value="{{number_format($data->harga_csr_atas, 0, ',','.')}}">
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
            <h3>Pengaturan Tabel Tagihan</h3>
            <div class="row">
                <div class="btn-group mb-3" role="group" data-bs-toggle="buttons">
                    <label class="btn btn-warning active">
                        <input type="checkbox" class="me-2" name="tanggal_muat" id="tanggal_muat" {{$data->tanggal_muat
                        == 1 ? 'checked' : ''}} autocomplete="off"> Tanggal Muat
                    </label>
                    <label class="btn btn-warning">
                        <input type="checkbox" class="me-2" name="nota_muat" id="nota_muat" {{$data->nota_muat == 1 ?
                        'checked' :
                        ''}} autocomplete="off"> Nota Muat
                    </label>
                    <label class="btn btn-warning">
                        <input type="checkbox" class="me-2" name="tonase" id="tonase" {{$data->tonase == 1 ? 'checked' :
                        ''}} autocomplete="off"> Tonase Muat
                    </label>
                    <label class="btn btn-warning">
                        <input type="checkbox" class="me-2" name="tanggal_bongkar" id="tanggal_bongkar"
                            {{$data->tanggal_bongkar == 1 ? 'checked' :
                        ''}} autocomplete="off"> Tanggal Bongkar
                    </label>
                    <label class="btn btn-warning">
                        <input type="checkbox" class="me-2" name="selisih" id="selisih" {{$data->selisih == 1 ?
                        'checked' :
                        ''}} autocomplete="off"> Selisih
                    </label>
                </div>
            </div>
            <div class="d-grid gap-3 mt-3">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a type="button" class="btn btn-secondary" href="{{route('customer.index')}}">Batal</a>
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

{{-- <script src="{{asset('assets/js/jquery.min.js')}}"></script> --}}
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script>

    // mask
    $('#harga_csr_atas').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0,
                allowZero: true,
            });

    $('#harga_csr_bawah').maskMoney({
        thousands: '.',
        decimal: ',',
        precision: 0,
        allowZero: true,
    });

    var nominal_penalty = new Cleave('#nominal_penalty', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

    $('#csr').on('change', function() {
        if ($(this).is(':checked')) {
            $('#rek_csr').removeAttr('hidden');
            $('#csr_transfer_ke').attr('required', true);
            $('#csr_bank').attr('required', true);
            $('#csr_no_rekening').attr('required', true);
            $('#harga_csr_atas').attr('required', true);
            $('#harga_csr_bawah').attr('required', true);
        } else {
            $('#rek_csr').attr('hidden', true);
            $('#csr_transfer_ke').attr('required', false);
            $('#csr_bank').attr('required', false);
            $('#csr_no_rekening').attr('required', false);
            $('#harga_csr_atas').attr('required', false);
            $('#harga_csr_bawah').attr('required', false);
        }
    });

    $(document).ready(function() {
            $('#rute').select2();

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
