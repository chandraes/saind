@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row ">
        <div class="col-md-12 text-center">
            <h1><u>UPAH GENDONG</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td>
                        @include('database.upah-gendong.create')
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@if ($errors->any())
<div class="container">
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{$error}}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif
@include('database.upah-gendong.edit')
<div class="container mt-5 table-responsive ">
    <table class="table table-hover table-bordered" id="karyawan-data">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Nominal Upah Gendong</th>
                <th class="text-center align-middle">Minimal Tonase</th>
                <th class="text-center align-middle">NOLAM</th>
                <th class="text-center align-middle">Nama Driver</th>
                <th class="text-center align-middle">Pengurus</th>
                <th class="text-center align-middle">Bank</th>
                <th class="text-center align-middle">Nama Rekening</th>
                <th class="text-center align-middle">Nomor Rekening</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-end align-middle">Rp. {{number_format($d->nominal,0,',','.')}}</td>
                <td class="text-center align-middle">{{$d->tonase_min}}</td>
                <td class="text-center align-middle">
                    {{$d->vehicle->nomor_lambung}}
                </td>
                <td class="text-center align-middle">{{$d->nama_driver}}</td>
                <td class="text-center align-middle">{{$d->nama_pengurus}}</td>
                <td class="text-center align-middle">{{$d->bank}}</td>
                <td class="text-center align-middle">{{$d->nama_rek}}</td>
                <td class="text-center align-middle">{{$d->no_rek}}</td>
                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary m-2" data-bs-toggle="modal"
                            data-bs-target="#editUg" onclick="editUg({{$d}}, {{$d->id}})"><i
                                class="fa fa-edit"></i> Edit</button>
                        <form action="{{route('database.upah-gendong.destroy', ['ug' => $d])}}" method="post"
                            id="deleteForm-{{$d->id}}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger m-2"><i class="fa fa-trash"></i> Delete</button>
                        </form>
                    </div>
                    {{-- button delete with sweetalert confirmation --}}

                </td>
            </tr>
            <script>
                $('#deleteForm-{{$d->id}}').submit(function(e){
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
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script>
      function editUg(data, id) {
            $('#edit_vehicle_id').val(data.vehicle_id).trigger('change');
            document.getElementById('edit_nama_driver').value = data.nama_driver;
            document.getElementById('edit_nominal').value = data.nf_nominal;
            document.getElementById('edit_tonase_min').value = data.tonase_min;
            document.getElementById('edit_nama_pengurus').value = data.nama_pengurus;
            document.getElementById('edit_nama_rek').value = data.nama_rek;
            document.getElementById('edit_no_rek').value = data.no_rek;
            document.getElementById('edit_bank').value = data.bank;
            // Populate other fields...
            document.getElementById('editForm').action = '/database/upah-gendong/update/' + id;
        }

    $(document).ready(function() {
        $('#karyawan-data').DataTable();

        var nominal = new Cleave('#nominal', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });

        var nominal = new Cleave('#edit_nominal', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });

        $('#vehicle_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih NOLAM',
            dropdownParent: $('#tambahSponsorId')
        });


        $('#edit_vehicle_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih NOLAM',
            dropdownParent: $('#editUg')
        });

        $('#editForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
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

        $('#masukForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
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
    } );

</script>
@endpush
