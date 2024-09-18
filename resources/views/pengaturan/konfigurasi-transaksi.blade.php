@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>BATASAN</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-4">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('pengaturan')}}"><img src="{{asset('images/pengaturan.svg')}}" alt="dokumen"
                                width="30"> Pengaturan</a></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row mt-3">
        <h2>Batasan Umum</h2>
        <table class="table table-bordered table-hover">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">PENGGUNAAN</th>
                    <th class="text-center align-middle">NILAI</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($batasanUmum as $b)
                <tr>
                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                    <td class="text-center align-middle">{{$b->untuk}}</td>
                    <td class="text-end align-middle">
                        <div class="row px-5">
                            <button class="btn btn-outline-dark" data-bs-toggle="modal"
                                data-bs-target="#editModal" onclick="edit({{$b}}, {{$b->id}})">{{$b->nf_nilai}}</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="row mt-3">
        <h2>Batasan Khusus</h2>
        <table class="table table-bordered table-hover">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">Nama</th>
                    <th class="text-center align-middle">Otomatis Tutup Dalam</th>
                    <th class="text-center align-middle">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                    <td class="text-center align-middle">{{$d->nama}}</td>
                    <td class="text-center align-middle">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#waktuModal{{$d->id}}">{{$d->waktu_aktif}} Jam</button>

                        <!-- Modal Body -->
                        <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
                        <div class="modal fade" id="waktuModal{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
                            data-bs-keyboard="false" role="dialog" aria-labelledby="title{{$d->id}} aria-hidden=" true">
                            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm"
                                role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="title{{$d->id}}">
                                            Otomatis Tutup Dalam
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form
                                        action="{{route('pengaturan.konfigurasi-transaksi.update-jam', ['konfigurasi' => $d->id])}}"
                                        method="post">
                                        @csrf
                                        @method('PATCH')

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control @if ($errors->has('waktu_aktif'))
                                                is-invalid
                                            @endif" name="waktu_aktif" id="waktu_aktif" value="{{$d->waktu_aktif}}">
                                                    <span class="input-group-text" id="basic-addon1">Jam</span>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Tutup
                                            </button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </td>
                    {{-- switch to change status true or false --}}
                    <td class="text-center align-middle">
                        <form action="{{route('pengaturan.konfigurasi-transaksi.update', $d->id)}}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="btn btn-{{$d->status ? 'success' : 'danger'}} btn-sm">{{$d->status ? 'Tutup' :
                                'Buka'}}</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('js')
{{-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script> --}}
<script src="{{asset('assets/js/jquery.maskMoney.js')}}"></script>
<script>
    // masukForm on submit, sweetalert confirm
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
                    $('#spinner').show();
                    this.submit();
                }
            })
        });

        function edit(data, id)
        {
                document.getElementById('nilai').value = data.nf_nilai;
                // Populate other fields...
                document.getElementById('editForm').action = '/pengaturan/batasan/update/' + id;

        }

        confirmAndSubmit('#editForm', "Apakah anda yakin?");
</script>
@endpush
