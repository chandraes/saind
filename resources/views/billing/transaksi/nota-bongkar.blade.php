@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Nota Bongkar</u></h1>
        </div>
    </div>
   @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('billing.transaksi.index')}}"><img src="{{asset('images/transaction.svg')}}"
                                alt="dokumen" width="30"> Form Transaksi</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container-fluid mt-5 table-responsive ">
    <table class="table table-bordered table-hover" id="data-table">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Tanggal</th>
                <th class="text-center align-middle">Kode</th>
                <th class="text-center align-middle">Nomor Lambung</th>
                <th class="text-center align-middle">Vendor</th>
                <th class="text-center align-middle">Tambang</th>
                <th class="text-center align-middle">Rute</th>
                <th class="text-center align-middle">Tanggal Muat</th>
                <th class="text-center align-middle">Nota Muat</th>
                <th class="text-center align-middle">Timbangan Muat</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->tanggal}}</td>
                <td class="align-middle">

                    <div class="modal fade" id="uj{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
                        data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg"
                            role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTitleId">Nota Bongkar
                                        {{$d->kas_uang_jalan->vehicle->nomor_lambung}}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="{{route('transaksi.nota-bongkar.update', $d->id)}}" method="post"
                                    id="masukForm{{$d->id}}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-4 mb-3">
                                                <label for="tanggal_muat" class="form-label">Kode</label>
                                                <input type="text" class="form-control" name="tanggal_muat"
                                                    id="tanggal_muat" placeholder="" value="UJ{{sprintf("%02d",
                                                    $d->kas_uang_jalan->nomor_uang_jalan)}}" readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="tanggal_muat" class="form-label">Tanggal</label>
                                                <input type="text" class="form-control" name="tanggal_uang_jalan"
                                                    id="tanggal_muat" placeholder="" value="{{$d->kas_uang_jalan->tanggal}}" readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="no_lambung" class="form-label">Nomor Lambung</label>
                                                <input type="text" class="form-control" name="no_lambung"
                                                    id="no_lambung" placeholder=""
                                                    value="{{$d->kas_uang_jalan->vehicle->nomor_lambung}}" readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="vendor" class="form-label">Vendor</label>
                                                <input type="text" class="form-control" name="vendor" id="vendor"
                                                    placeholder="" value="{{$d->kas_uang_jalan->vendor->nickname}}"
                                                    readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="tambang" class="form-label">Tambang</label>
                                                <input type="text" class="form-control" name="tambang" id="tambang"
                                                    placeholder="" value="{{$d->kas_uang_jalan->customer->singkatan}}"
                                                    readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="rute" class="form-label">Rute</label>
                                                <input type="text" class="form-control" name="rute" id="rute"
                                                    placeholder="" value="{{$d->kas_uang_jalan->rute->nama}}" readonly>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-4 mb-3">
                                                <label for="nota_muat" class="form-label">Nota Muat</label>
                                                <input type="text" class="form-control" name="nota_muat" id="nota_muat"
                                                    placeholder="" value="{{$d->nota_muat}}" readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="tonase" class="form-label">Timbangan Muat</label>
                                                <input type="text" class="form-control" name="tonase" id="tonase"
                                                    placeholder="" value="{{$d->tonase}}" readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="tonase" class="form-label">Tanggal Muat</label>
                                                <input type="text" class="form-control" name="tonase" id="tonase"
                                                    placeholder="" value="{{$d->tanggal_muat}}" readonly>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-4 mb-3">
                                                <label for="nota_bongkar" class="form-label">Nota Bongkar</label>
                                                <input type="text" class="form-control" name="nota_bongkar" id="nota_bongkar"
                                                    placeholder="" value="" required>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="timbangan_bongkar" class="form-label">Timbangan Bongkar</label>
                                                <input type="text" class="form-control" name="timbangan_bongkar" id="timbangan_bongkar"
                                                    placeholder="" value="" required>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="tonase" class="form-label">Tanggal Bongkar</label>
                                                <input type="text" class="form-control" name="tonase" id="tonase"
                                                    placeholder="" value="{{date('d M Y')}}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#uj{{$d->id}}"> <strong>UJ{{sprintf("%02d",
                                $d->kas_uang_jalan->nomor_uang_jalan)}}</strong></a>
                    </div>
                </td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->vehicle->nomor_lambung}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->vendor->nickname}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->customer->singkatan}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->nama}}</td>
                <td class="text-center align-middle">{{$d->tanggal_muat}}</td>
                <td class="text-center align-middle">{{$d->nota_muat}}</td>
                <td class="text-center align-middle">{{$d->tonase}}</td>
                <td class="text-center align-middle">
                    <!-- Modal trigger button -->
                    <button type="button" class="btn btn-primary m-2" data-bs-toggle="modal" data-bs-target="#backModal-{{$d->id}}">
                      Back
                    </button>

                    <!-- Modal Body -->
                    <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
                    <div class="modal fade" id="backModal-{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="Title-{{$d->id}}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="Title-{{$d->id}}">Masukkan Password</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{route('transaksi.back', $d)}}" method="post">
                                    @csrf
                                <div class="modal-body">
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Password" aria-label="Password" aria-describedby="password"
                                        required>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary">Lanjutkan</button>
                                </div>
                            </form>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-warning btn-block m-2" type="button" data-bs-toggle="modal" data-bs-target="#modalVoid-{{$d->id}}">Void</button>

                    <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
                    <div class="modal fade" id="modalVoid-{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTitleId">Masukan Password</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{route('transaksi.void-masuk', $d)}}" method="post">
                                    @csrf
                                <div class="modal-body">
                                    <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Password" aria-label="Password" aria-describedby="password"
                                            required>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </form>
                            </div>
                        </div>
                    </div>

                </td>
            </tr>
            <script>
                $('#masukForm{{$d->id}}').submit(function(e){
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
            </script>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('css')
<link href="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.css" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.js"></script>
<script>
    // hide alert after 5 seconds


    $(document).ready(function() {
        $('#data-table').DataTable();

    } );

    function toggleInputTambah() {
        var value = document.getElementById('vendor_id').value;
        if (value == '') {
            document.getElementById('row-input').hidden = true;
        } else {
            document.getElementById('row-input').hidden = false;
        }
    }
</script>
@endpush
