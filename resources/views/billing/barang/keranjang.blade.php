<div class="modal fade" id="keranjangBelanja" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="keranjangTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="keranjangTitle">Keranjang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center align-middle">Kategori Barang</th>
                            <th class="text-center align-middle">Nama Barang</th>
                            <th class="text-center align-middle">Jumlah</th>
                            <th class="text-center align-middle">Harga Satuan</th>
                            <th class="text-center align-middle">Total</th>
                            <th class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($keranjang as $b)
                        <tr>
                            <td class="text-center align-middle">{{$b->barang->kategori_barang->nama}}</td>
                            <td class="text-center align-middle">{{$b->barang->nama}}</td>
                            <td class="text-center align-middle">{{$b->jumlah}}</td>
                            <td class="text-center align-middle">{{number_format($b->harga_satuan, 0, ',','.')}}</td>
                            <td class="text-center align-middle">{{number_format($b->total, 0, ',','.')}}</td>
                            <td class="text-center align-middle">
                                <form action="{{route('billing.form-barang.keranjang-destroy', $b->id)}}" method="post">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-center align-middle"></td>
                            <td class="text-center align-middle"></td>
                            <td class="text-center align-middle"></td>
                            <td class="text-center align-middle"></td>
                            <td class="text-center align-middle">{{count($keranjang) > 0 ? number_format($keranjang->sum('total'), 0, ',','.') : ''}}</td>
                            <td class="text-center align-middle"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <form action="{{route('billing.form-barang.barang-store')}}" method="get" id="beliBarang">
                    <button type="submit" class="btn btn-primary">Beli Barang</button>
                </form>
            </div>
        </div>
    </div>
</div>
