@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>MUTASI REKENING</u></h1>
            <h1>{{$tahun}}</h1>
        </div>
    </div>
    @include('swal')
    @include('dokumen.mutasi-rekening.kirim-wa')
    @include('dokumen.mutasi-rekening.create')
    <div class="row d-flex justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    {{-- <td>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#jabatan">
                            <img src="{{asset('images/jabatan.svg')}}" alt="dokumen" width="30"> Tambah Kategori
                        </a>
                    </td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalCreate"><img
                                src="{{asset('images/legalitas.svg')}}" alt="add-document" width="30"> Tambah
                            Legalitas</a>
                    </td> --}}
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <form action="{{route('dokumen.mutasi-rekening')}}" method="get">
                <div class="row">

                    <div class="col-md-6 mb-3">
                        <select class="form-select" name="tahun" id="tahun">
                            @foreach ($dataTahun as $d)
                            <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">

                        <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="container mt-5 table-responsive ">

    <table class="table table-hover table-bordered" id="karyawan-data">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle" style="width: 7%">No</th>
                <th class="text-center align-middle">Bulan</th>
                <th class="text-center align-middle" style="width: 50%">Action</th>
            </tr>
        </thead>

        <tbody>
            @php
                $no = 1;
            @endphp
            @for ($d = 1; $d <= count($bulan); $d++)
                <tr>
                    <td class="text-center align-middle">{{ $no++ }}</td>
                    <td class="text-center align-middle">{{ $data[$d]['bulan'] }}</td>
                    <td class="text-center align-middle">
                        <div class="d-flex justify-content-center flex-wrap gap-3">
                            @if ($data[$d]['file'] == null)
                                <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreate" onclick="createFun({{ json_encode($data[$d])}}, {{$d}})">Tambah Mutasi Rekening <i class="ms-2 fa fa-plus"></i></button>
                                @else
                                <a class="btn btn-primary btn-sm" href="{{ asset($data[$d]['file']) }}" target="_blank">View <i class="ms-2 fa fa-file"></i></a>
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#kirimWaModal" onclick="kirimWa({{ json_encode($data[$d]) }})">Kirim Whatsapp <i class="ms-2 fa fa-whatsapp"></i></button>
                                <form action="{{ route('dokumen.mutasi-rekening.destroy', $data[$d]['id']) }}" method="post" class="d-inline">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah anda yakin untuk menghapus data ini?')"> Hapus <i class="ms-2 fa fa-trash"></i></button>
                                </form>
                            @endif

                        </div>
                    </td>
                </tr>
            @endfor

                    {{-- <tr>
                        <td class="text-start align-middle">{{ $no++ }}</td>
                        <td class="text-start align-middle">{{ $k->nama }}</td>
                        <td class="text-center align-middle"> --}}
                            {{-- <div class="d-flex justify-content-center flex-wrap gap-3">
                                <a class="btn btn-primary btn-sm" href="{{ asset($k->file) }}" target="_blank">View <i class="ms-2 fa fa-file"></i></a>
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#kirimWaModal" onclick="kirimWa({{ $k }})">Kirim Whatsapp <i class="ms-2 fa fa-whatsapp"></i></button>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit" onclick="editFun({{ $k }})">Edit <i class="ms-2 fa fa-edit"></i></button>
                                <form action="{{ route('legalitas.destroy', $k->id) }}" method="post" class="d-inline">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah anda yakin untuk menghapus data ini?')"> Hapus <i class="ms-2 fa fa-trash"></i></button>
                                </form>
                            </div> --}}
                        {{-- </td>
                    </tr> --}}

        </tbody>
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
        document.getElementById('nama_wa').value = data.bulan+' '+data.tahun;
        document.getElementById('waForm').action = '/dokumen/mutasi-rekening/kirim-wa/'+data.id;
    }

    function createFun(data, bulan) {
        // console.log(data, bulan);
        document.getElementById('nama').value = data.bulan+' '+data.tahun;
        document.getElementById('tahun').value = data.tahun;
        document.getElementById('bulan').value = bulan;
        document.getElementById('createForm').action = '/dokumen/mutasi-rekening/store'

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
        $('#karyawan-data').DataTable({
            "paging": false,
            "ordering": false,
        });

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
