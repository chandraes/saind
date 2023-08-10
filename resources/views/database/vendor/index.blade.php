@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Vendor</u></h1>
        </div>
    </div>
    @if (session('success'))
    <div class="row">
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>
                {{session('success')}}
            </strong>
        </div>
    </div>
    @endif
    <div class="row float-end">
        <div class="col-md-12">
            <strong>
                <span id="clock"></span>
            </strong>
        </div>
    </div>
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-4">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td><a href="{{route('vendor.create')}}"><img
                                src="{{asset('images/vendor.svg')}}" alt="add-document" width="30"> Tambah Vendor</a>
                    </td>
                    {{-- <td><a href="{{route('dokumen.sph_doc')}}" target="_blank"><img
                                src="{{asset('images/document-add.svg')}}" alt="add-document" width="30"> Tambah SPH</a>
                    </td> --}}
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container-fluid table-responsive">
    <table class="table table-bordered table-hover" id="data">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Nama Perusahaan</th>
                <th class="text-center align-middle">Nama CP</th>
                <th class="text-center align-middle">Nickname</th>
                <th class="text-center align-middle">Pembayaran</th>
                <th class="text-center align-middle">Uang Jalan</th>
                <th class="text-center align-middle">Status</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vendors as $d)
            <tr>
                <td class="align-middle">{{$loop->iteration}}</td>
                <td class="align-middle">{{$d->perusahaan}}</td>
                <td class="align-middle">{{$d->nama}}</td>
                <td class="align-middle">{{$d->nickname}}</td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-pembayaran{{$d->id}}">
                        Lihat Pembayaran
                      </button>
                      <div class="modal fade" id="modal-pembayaran{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                          <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-l" role="document">
                              <div class="modal-content">
                                  <div class="modal-header">
                                      <h5 class="modal-title" id="modalTitleId">Pembayaran</h5>
                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                        @if ($d->vendor_bayar->count() > 0)
                                        <table class="table table-bordered table-hover">
                                            <thead class="text-center align-middle">
                                                <th class="text-center align-middle">No</th>
                                                <th class="text-center align-middle">Customer</th>
                                                <th class="text-center align-middle">Kesepakan OPNAME</th>
                                                <th class="text-center align-middle">Kesepakan Titipan</th>
                                            </thead>
                                            <tbody>
                                                @foreach ($customers as $c)
                                                <tr>
                                                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                                                    <td class="text-center align-middle">{{$c->nama}}</td>
                                                    <td class="text-center align-middle">
                                                        @if ($d->vendor_bayar->where('customer_id', $c->id)->where('pembayaran','=' ,'opname')->first())
                                                        Rp.
                                                        {{
                                                            number_format($d->vendor_bayar->where('customer_id', $c->id)->where('pembayaran','=' ,'opname')->pluck('harga_kesepakatan')['0'], 0, ',', '.')
                                                            }}
                                                        @endif

                                                    </td>
                                                    <td class="text-center align-middle">
                                                        @if ($d->vendor_bayar->where('customer_id', $c->id)->where('pembayaran','=' ,'titipan')->first())
                                                        Rp.  {{
                                                            number_format($d->vendor_bayar->where('customer_id', $c->id)->where('pembayaran','=' ,'titipan')->pluck('harga_kesepakatan')['0'], 0, ',', '.')
                                                            }}
                                                        @endif

                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                    </table>
                                        @endif
                                  </div>
                                  <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                      <button type="button" class="btn btn-primary">Edit Uang Jalan</button>
                                  </div>
                              </div>
                          </div>
                      </div>
                </td>
                <td class="text-center align-middle">
                     <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-uang{{$d->id}}">
                        Lihat Uang Jalan
                      </button>
                      <div class="modal fade" id="modal-uang{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                          <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-l" role="document">
                              <div class="modal-content">
                                  <div class="modal-header">
                                      <h5 class="modal-title" id="modalTitleId">Modal title</h5>
                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                        @if ($d->vendor_uang_jalan->count() > 0)
                                        <table class="table table-bordered table-hover">
                                            <thead class="text-center align-middle">
                                                <th class="text-center align-middle">No</th>
                                                <th class="text-center align-middle">Rute</th>
                                                <th class="text-center align-middle">Kesepakan Uang Jalan</th>
                                            </thead>

                                        @foreach ($d->vendor_uang_jalan as $item)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{$item->rute->nama}}</td>
                                            <td>Rp. {{number_format($item->hk_uang_jalan, 0, ',', '.')}}</td>
                                        </tr>
                                        @endforeach
                                    </table>
                                        @endif
                                  </div>
                                  <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                      <button type="button" class="btn btn-primary">Edit Uang Jalan</button>
                                  </div>
                              </div>
                          </div>
                      </div>
                </td>
                <td class="align-middle text-center">
                    @if ($d->status == "aktif")
                    <span class="badge badge-xl rounded-pill text-bg-success">Aktif</span>
                    @elseif ($d->status === "nonaktif")
                    <span class="badge rounded-pill text-bg-danger">Non Aktif</span>
                    @endif
                </td>
                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center">
                        <a href="{{route('vendor.biodata-vendor', $d->id)}}" target="_blank" class="btn btn-success me-2">PDF</a>
                        <a href="{{route('vendor.edit', $d->id)}}" class="btn btn-warning me-2">Ubah</a>
                        <form action="{{route('vendor.destroy', $d->id)}}" method="post">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('css')
<link href="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.css" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.js"></script>
<script>
    // success-alert close after 5 second
    $("#success-alert").fadeTo(5000, 500).slideUp(500, function(){
        $("#success-alert").slideUp(500);
    });

    $(document).ready(function() {
        $('#data').DataTable();
    } );
</script>
@endpush
