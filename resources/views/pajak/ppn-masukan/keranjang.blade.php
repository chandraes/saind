<div class="modal fade" id="keranjangModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="keranjangTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="keranjangTitle">
                    Keranjang PPn Masukan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-hover" id="keranjangTable">
                    <thead>
                        <tr>
                            <th class="text-center align-middle">No</th>
                            <th class="text-center align-middle">Nota</th>
                            <th class="text-center align-middle">Faktur</th>
                            <th class="text-center align-middle">Nominal</th>
                            <th class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($keranjangData as $k)
                        <tr>
                            <td class="text-center align-middle">{{$loop->iteration}}</td>
                            <td class="text-center align-middle">
                                @if ($k->invoiceBelanja)
                                <a
                                    href="{{route('billing.invoice-supplier.detail', ['invoice' => $k->invoice_belanja_id])}}">
                                    {{$k->invoiceBelanja->kode}}
                                </a>
                                @else
                                -
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                {{$k->no_faktur}}
                            </td>
                            <td class="text-end align-middle">
                                {{$k->nf_nominal}}
                            </td>
                            <td class="text-center align-middle">
                                <form
                                    action="{{route('pajak.ppn-masukan.keranjang-destroy', ['ppnMasukan' => $k->id])}}"
                                    method="post">
                                    @csrf
                                    <button class="btn btn-danger"><i class="fa fa-trash"></i>Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-end align-middle" colspan="3">Grand Total</th>
                            <th class="text-end align-middle">{{number_format($keranjangData->sum('nominal'), 0,
                                ',','.')}}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
                <form action="{{route('pajak.ppn-masukan.keranjang-lanjut')}}" method="post" id="lanjutForm">
                    @csrf
                    <button type="submit" class="btn btn-primary">Lanjutkan</button>
                </form>
            </div>
        </div>
    </div>
</div>
