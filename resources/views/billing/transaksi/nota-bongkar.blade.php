@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Nota Bongkar</u></h1>
        </div>
    </div>
    @if (session('success'))
    <script>
        Swal.fire(
                'Berhasil!',
                '{{session('success')}}',
                'success'
            )
    </script>
    @endif
    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{session('error')}}',
        })
    </script>
    @endif
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
                        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTitleId">Nota Muat
                                        {{$d->kas_uang_jalan->vehicle->nomor_lambung}}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="{{route('transaksi.nota-muat.update', $d->id)}}" method="post"
                                    id="masukForm{{$d->id}}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-4 mb-3">
                                                <label for="tanggal_muat" class="form-label">Tanggal</label>
                                                <input type="text"
                                                  class="form-control" name="tanggal_muat" id="tanggal_muat" placeholder="" value="{{date('d M Y')}}" readonly>
                                              </div>
                                              <div class="col-4 mb-3">
                                                  <label for="tanggal_muat" class="form-label">Nomor Lambung</label>
                                                  <input type="text"
                                                    class="form-control" name="tanggal_muat" id="tanggal_muat" placeholder="" value="{{$d->kas_uang_jalan->vehicle->nomor_lambung}}" readonly>
                                                </div>
                                                <div class="col-4 mb-3">
                                                    <label for="tanggal_muat" class="form-label">Vendor</label>
                                                    <input type="text"
                                                      class="form-control" name="tanggal_muat" id="tanggal_muat" placeholder="" value="{{$d->kas_uang_jalan->vendor->nickname}}" readonly>
                                                  </div>
                                                  <div class="col-4 mb-3">
                                                    <label for="tanggal_muat" class="form-label">Tambang</label>
                                                    <input type="text"
                                                      class="form-control" name="tanggal_muat" id="tanggal_muat" placeholder="" value="{{$d->kas_uang_jalan->customer->singkatan}}" readonly>
                                                  </div>
                                                  <div class="col-4 mb-3">
                                                    <label for="tanggal_muat" class="form-label">Rute</label>
                                                    <input type="text"
                                                      class="form-control" name="tanggal_muat" id="tanggal_muat" placeholder="" value="{{$d->kas_uang_jalan->rute->nama}}" readonly>
                                                  </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label for="nota_muat" class="form-label">Nota Muat</label>
                                                <input type="text"
                                                  class="form-control" name="nota_muat" id="nota_muat" placeholder="" value="{{$d->nota_muat}}" required>
                                              </div>
                                              <div class="col-6 mb-3">
                                                <label for="tonase" class="form-label">Tonase</label>
                                                <input type="text"
                                                  class="form-control" name="tonase" id="tonase" placeholder="" value="{{$d->tonase}}" required>
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
                    <button class="btn btn-warning btn-block">Void</button>
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
