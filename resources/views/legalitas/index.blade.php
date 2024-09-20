@extends('layouts.app')

@section('content')
@php
    use Carbon\Carbon;
@endphp
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>LEGALITAS</u></h1>
        </div>
    </div>
    @include('swal')
    @include('legalitas.kategori')
    @include('legalitas.create')
    @include('legalitas.edit')
    @include('legalitas.kirim-wa')
    <div class="row d-flex justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#jabatan">
                            <img src="{{asset('images/jabatan.svg')}}" alt="dokumen" width="30"> Tambah Kategori
                        </a>
                    </td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalCreate"><img
                                src="{{asset('images/legalitas.svg')}}" alt="add-document" width="30"> Tambah
                            Legalitas</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>


<div class="container mt-5 table-responsive ">
    <table class="table table-hover table-bordered" id="karyawan-data">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle" style="width: 7%">No</th>
                <th class="text-center align-middle">Kategori</th>
                <th class="text-center align-middle">Nama Dokumen</th>
                <th class="text-center align-middle">Tanggal<br>Kadaluarsa</th>
                <th class="text-center align-middle" style="width: 40%">Action</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($data as $kategoriId => $documents)
            @php
                $rowspan = $documents->count();
                $kategori = $documents->first()->kategori ? $documents->first()->kategori->nama : '-';
            @endphp
            @foreach ($documents as $index => $k)
            @php
                $isDanger = false;
                if ($k->tanggal_expired) {
                    $tanggalExpired = Carbon::parse($k->tanggal_expired);
                    $now = Carbon::now();
                    $diffInDays = $tanggalExpired->diffInDays($now, true); // false to allow negative values if the date is in the past

                    // Kondisi untuk menentukan apakah "bahaya" atau tidak
                    $isDanger = ($diffInDays <= 45 && $diffInDays >= 0) || $tanggalExpired->isPast();
                }
            @endphp
                <tr>
                    @if ($index == 0)
                        <td class="text-center align-middle" rowspan="{{ $rowspan }}">{{ $loop->parent->iteration }}</td>
                        <td class="text-center align-middle" rowspan="{{ $rowspan }}">{{ $kategori }}</td>
                    @endif
                    <td class="text-start align-middle {{ $isDanger ? 'bg-danger' : '' }}">{{ $k->nama }}</td>
                    <td class="text-center align-middle {{ $isDanger ? 'bg-danger' : '' }}">{{ $k->tanggal_expired ? Carbon::parse($k->tanggal_expired)->format('d-m-Y') : '-' }}</td>
                    <td class="text-center align-middle">
                        <div class="d-flex justify-content-center flex-wrap gap-3">
                            <a class="btn btn-primary btn-sm" href="{{ asset($k->file) }}" target="_blank">View <i class="ms-2 fa fa-file"></i></a>
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#kirimWaModal" onclick="kirimWa({{ $k }})">Kirim Whatsapp <i class="ms-2 fa fa-whatsapp"></i></button>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit" onclick="editFun({{ $k }})">Edit <i class="ms-2 fa fa-edit"></i></button>
                            <form action="{{ route('legalitas.destroy', $k->id) }}" method="post" class="d-inline">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah anda yakin untuk menghapus data ini?')"> Hapus <i class="ms-2 fa fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endforeach
        </tbody>       {{-- <tbody>
            @foreach ($data as $k)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$k->kategori ? $k->kategori->nama : '-'}}</td>
                <td class="text-start align-middle">{{$k->nama}}</td>
                <td class="text-center align-middle">
                    <div class="row px-4">
                        <div class="col-md-12">
                            <div class="row mb-2">
                                <a class="btn btn-primary btn-sm" href="{{asset($k->file)}}" target="_blank">Lihat
                                    Dokumen <i class="fa fa-file"></i></a>
                            </div>
                        </div>
                        <div class="col-md-12 mb-2">
                            <div class="row ">
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#kirimWaModal" onclick="kirimWa({{$k}})">Kirim Whatsapp <i
                                        class="fa fa-whatsapp"></i></button>
                            </div>
                        </div>
                        <div class="col-md-12 mb-2">
                            <div class="row ">
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#kirimWaModal" onclick="kirimWa({{$k}})">Edit <i
                                        class="fa fa-edit"></i></button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <form action="{{route('legalitas.destroy', $k->id)}}" method="post">
                                @csrf
                                @method('delete')
                                <div class="row">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Apakah anda yakin untuk menghapus data ini?')"><i
                                            class="fa fa-trash"></i> Hapus </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody> --}}
    </table>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script>
    function kirimWa(data) {
        document.getElementById('nama_dokumen_wa').value = data.nama;
       document.getElementById('waForm').action = '/legalitas/kirim-wa/'+data.id;
    }

    function editFun(data) {
        document.getElementById('editForm').action = '/legalitas/update/'+data.id;
        document.getElementById('edit_legalitas_kategori_id').value = data.legalitas_kategori_id;

        if (data.tanggal_expired) {
            document.getElementById('edit_apa_expired').checked = true;
            document.getElementById('edit_tgl_ex').style.display = 'block';
            const dateParts = data.tanggal_expired.split('-');
            const year = dateParts[0];
            const month = dateParts[1];
            const day = dateParts[2];

            // Format the date to d-m-Y
            const formattedDate = `${day}-${month}-${year}`;

            // Set the value of the input field
            document.getElementById('edit_tanggal_expired').value = formattedDate;
            document.getElementById('edit_tanggal_expired').flatpickr({
                dateFormat: 'd-m-Y',
                allowInput: true,
            });

        } else {
            document.getElementById('edit_apa_expired').checked = false;
            document.getElementById('edit_tanggal_expired').value = '';
            document.getElementById('edit_tgl_ex').style.display = 'none';
        }

        document.getElementById('edit_nama').value = data.nama;
    }

    var tujuan = new Cleave('#tujuan', {
        delimiter: '-',
        blocks: [4, 4, 8]
    });

    $('#waForm').submit(function(e){
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

        $('#createForm').submit(function(e){
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

    $(document).ready(function() {
        $('#karyawan-data').DataTable();

    } );

    function toggleNamaJabatan(id) {

        // check if input is readonly
        if ($('#nama_jabatan-'+id).attr('readonly')) {
            // remove readonly
            $('#nama_jabatan-'+id).removeAttr('readonly');
            // show button
            $('#buttonJabatan-'+id).removeAttr('hidden');
        } else {
            // add readonly
            $('#nama_jabatan-'+id).attr('readonly', true);
            // hide button
            $('#buttonJabatan-'+id).attr('hidden', true);
        }
    }
</script>
@endpush
