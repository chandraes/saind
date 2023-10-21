@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12 text-center">
                <h1><u>Void Nota</u></h1>
            </div>
        </div>
        @include('swal')
    </div>
    <div class="container mt-5">
        <form action="{{ route('transaksi.void.store', $data) }}" method="post" id="masukForm">
            @csrf

            <div class="row">
                <div class="col-2">
                    <label for="kode" class="form-label">Kode</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">UJ</span>
                        <input type="text" class="form-control" name="kode"
                            value="{{ sprintf(' %02d', $data->kas_uang_jalan->nomor_uang_jalan) }}" disabled>
                    </div>
                </div>
                <div class="col-2">
                    <div class="mb-3">
                        <label for="nomor_lambung" class="form-label">Nomor Lambung</label>
                        <input type="text" class="form-control" name="nomor_lambung" id="nomor_lambung"
                            value="{{ $data->kas_uang_jalan->vehicle->nomor_lambung }}" disabled>
                    </div>
                </div>
                <div class="col-3">
                    <div class="mb-3">
                        <label for="nomor_lambung" class="form-label">Vendor</label>
                        <input type="text" class="form-control" name="nomor_lambung" id="nomor_lambung"
                            value="{{ $data->kas_uang_jalan->vendor->nama }}" disabled>
                    </div>
                </div>
                <div class="col-2">
                    <div class="mb-3">
                        <label for="nomor_lambung" class="form-label">Tambang</label>
                        <input type="text" class="form-control" name="nomor_lambung" id="nomor_lambung"
                            value="{{ $data->kas_uang_jalan->customer->singkatan }}" disabled>
                    </div>
                </div>
                <div class="col-2">
                    <div class="mb-3">
                        <label for="nomor_lambung" class="form-label">Rute</label>
                        <input type="text" class="form-control" name="nomor_lambung" id="nomor_lambung"
                            value="{{ $data->kas_uang_jalan->rute->nama }}" disabled>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-4">
                    <label for="nominal_transaksi" class="form-label">Uang Jalan</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Rp</span>
                        <input type="text" class="form-control @if ($errors->has('nominal_transaksi')) is-invalid @endif"
                            name="nominal_transaksi" id="nominal_transaksi" readonly
                            value="{{ number_format($data->kas_uang_jalan->nominal_transaksi, 0, ',', '.') }}">
                    </div>
                </div>
                <div class="col-5">
                    <div class="mb-3">
                        <label for="alasan" class="form-label">Alasan Void</label>
                        <input type="text" class="form-control" name="alasan" id="alasan" aria-describedby="helpId"
                            placeholder="" required maxlength="30" required>
                    </div>
                </div>
            </div>
            <div class="row">
                {{-- button submit --}}
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary btn-inline">Simpan</button>
                    <a href="{{ route('transaksi.nota-muat') }}" class="btn btn-danger btn-inline">Kembali</a>
                </div>
            </div>
        </form>
    </div>
@endsection
@push('css')
    <link href="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.css" rel="stylesheet">
@endpush
@push('js')
    <script src="{{ asset('assets/plugins/date-picker/date-picker.js') }}"></script>
    <script src="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.js"></script>
    <script>
        // hide alert after 5 seconds


        $(document).ready(function() {
            $('#data-table').DataTable();

        });

        $('#masukForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah anda yakin untuk Permintaan Dana ini?',
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
