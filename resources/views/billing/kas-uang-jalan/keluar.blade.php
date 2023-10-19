@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Kas Uang Jalan (Keluar)</u></h1>
        </div>
    </div>
    @if (session('error'))
    <script>
        Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{session('error')}}',
            })
    </script>
    @endif
    <form action="{{route('kas-uang-jalan.keluar.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-2 mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="text" class="form-control @if ($errors->has('tanggal'))
                    is-invalid
                @endif" name="tanggal" id="tanggal" required value="{{date('d M Y')}}" disabled>
            </div>
            <div class="col-md-2 mb-3">
                <label for="kode" class="form-label">Kode</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">UJ</span>
                    <input type="text" class="form-control" name="kode" value="{{sprintf(" %02d", $nomor)}}" disabled>
                </div>
            </div>
            <div class="col-4 mb-3">
                <label for="vehicle_id" class="form-label">Nomor Lambung</label>
                <select class="form-select" name="vehicle_id" id="vehicle_id" required>
                    <option selected>-- Pilih Nomor Lambung --</option>
                    @foreach ($vehicle as $v)
                    <option value="{{$v->id}}">{{$v->nomor_lambung}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 mb-3" id="vendor">
                <label for="vendor_id" class="form-label">Vendor</label>
                <input type="text" class="form-control" name="vendor_id" id="vendor_id" disabled>
                <input type="hidden" name="p_vendor" id="p_vendor" readonly>
            </div>
        </div>
        <div class="row">
            <div class="col-4 mb-3">
                <label for="customer_id" class="form-label">Tambang</label>
                <select class="form-select" name="customer_id" id="customer_id" required>
                    <option>-- Pilih Tambang --</option>
                    @foreach ($customer as $v)
                    <option value="{{$v->id}}">{{$v->singkatan}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 mb-3">
                <label for="rute_id" class="form-label">Rute</label>
                <select class="form-select" name="rute_id" id="rute_id" required>
                </select>
            </div>
            <div class="col-4 mb-3">
                <label for="hk_uang_jalan" class="form-label">Uang Jalan</label>
                <input type="text" class="form-control" name="nominal_transaksi" id="hk_uang_jalan" required>
            </div>
        </div>
        <hr>
        <h2>Transfer Uang Jalan</h2>
        <div class="row mb-3">
            <div class="col-4 mt-2">
                <div class="mb-3">
                  <label for="transfer_ke" class="form-label">Nama Rekening</label>
                  <input type="text"
                    class="form-control" name="transfer_ke" id="transfer_ke" readonly required>
                </div>
            </div>
            <div class="col-4 mt-2">
                <div class="mb-3">
                  <label for="bank" class="form-label">Bank</label>
                  <input type="text"
                    class="form-control" name="bank" id="bank" readonly required>
                </div>
            </div>
            <div class="col-4 mt-2">
                <div class="mb-3">
                  <label for="no_rekening" class="form-label">Nomor Rekening</label>
                  <input type="text"
                    class="form-control" name="no_rekening" id="no_rekening" readonly required>
                </div>
            </div>
        </div>
        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-success" type="submit">Simpan</button>
            <a href="{{route('billing.index')}}" class="btn btn-secondary" type="button">Batal</a>
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
{{-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script> --}}
<script src="{{asset('assets/js/jquery.maskMoney.js')}}"></script>
<script>
        // masukForm on submit, sweetalert confirm
        $('#masukForm').submit(function(e){
            e.preventDefault();

            Swal.fire({
                title: 'Apakah anda yakin data sudah benar?',
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
        $(document).ready(function () {
        // Jalankan fungsi changeTipe saat halaman dimuat
            $('#vehicle_id').select2({
                theme: 'bootstrap-5'
            });

            $('#vehicle_id').on('change', function () {
                // remove hidden attribute on vendor
                $('#vendor').removeAttr('hidden');
                // Jalankan fungsi changeTipe saat terjadi perubahan
                var id = $(this).val();
                $.ajax({
                    url: "{{route('kas-uang-jalan.get-vendor')}}",
                    method: "GET",
                    data: {
                        id: id
                    },
                    success: function (data) {
                        $('#vendor_id').val(data.nama);
                        $('#p_vendor').val(data.id);
                        if (data.transfer_ke == null || data.bank == null || data.no_rekening == null) {
                            // sweetalert
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Rekening Uang Jalan Vehicle ini belum diisi, silahkan isi terlebih dahulu di Database Vehicle!!',
                            });
                            $('#transfer_ke').val('');
                            $('#bank').val('');
                            $('#no_rekening').val('');
                        } else {
                            $('#transfer_ke').val(data.transfer_ke);
                            $('#bank').val(data.bank);
                            $('#no_rekening').val(data.no_rekening);
                        }
                    }
                });

            });

            $('#customer_id').select2({
                theme: 'bootstrap-5'
            });

            $('#customer_id').on('change', function () {
                // remove hidden attribute on vendor
                // Jalankan fungsi changeTipe saat terjadi perubahan
                var id = $(this).val();
                $.ajax({
                    url: "{{route('kas-uang-jalan.get-rute')}}",
                    method: "GET",
                    data: {
                        id: id
                    },
                    success: function (data) {
                        console.log(data);
                        $('#rute_id').empty();
                        $('#rute_id').append('<option>-- Pilih Rute --</option>');
                        $.each(data, function (index, value) {
                            $('#rute_id').append('<option value="' + value.id + '">' + value.nama + '</option>');
                        });
                    }
                });

            });

            $('#rute_id').select2({
                theme: 'bootstrap-5'
            });

            $('#rute_id').on('change', function () {
                // remove hidden attribute on vendor
                // Jalankan fungsi changeTipe saat terjadi perubahan
                var rute_id = $(this).val();
                var vendor_id = $('#p_vendor').val();
                $.ajax({
                    url: "{{route('kas-uang-jalan.get-uang-jalan')}}",
                    method: "GET",
                    data: {
                        rute_id: rute_id,
                        vendor_id: vendor_id,
                    },
                    success: function (data) {
                        $('#hk_uang_jalan').val(data.hk_uang_jalan);
                    }
                });

            });
    });
</script>
@endpush
