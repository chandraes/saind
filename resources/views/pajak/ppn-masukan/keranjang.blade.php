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
                            <th class="text-center align-middle">Vendor</th>
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
                                @if ($k->invoiceBayar)
                                <a
                                    href="{{route('invoice.bayar.detail', ['invoiceBayar' => $k->invoice_bayar_id])}}">
                                    {{$k->invoiceBayar->periode}}
                                </a>
                                @else
                                -
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                @if ($k->invoiceBayar)
                                    {{$k->invoiceBayar->vendor->nickname}}
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
                            <th class="text-end align-middle" colspan="4">Grand Total</th>
                            <th class="text-end align-middle">{{number_format($keranjangData->sum('nominal'), 0,
                                ',','.')}}</th>
                            <th></th>
                        </tr>
                        <tr>
                            <th class="text-end align-middle" colspan="4">Penyesuaian</th>
                            <th class="text-end align-middle">
                                <input type="text" class="form-control text-end" name="penyesuaianInput" id="penyesuaianInput"
                                    required value="0" onkeyup="checkPenyesuaian()">
                            </th>
                            <th></th>
                        </tr>
                        <tr>
                            <th class="text-end align-middle" colspan="4">Grand Total</th>
                            <th class="text-end align-middle" id="grandTotal">
                                {{number_format($keranjangData->sum('nominal'), 0,',','.')}}
                            </th>
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
                    <input type="hidden" name="penyesuaian" id="penyesuaianHidden" value="0">
                    <button type="submit" class="btn btn-primary">Lanjutkan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@push('js')
<script>
    var nominal = new Cleave('#penyesuaianInput', {
            numeral: true,
            negative: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });

        function checkPenyesuaian() {
            var penyesuaian = document.getElementById('penyesuaianInput').value ?? 0;
            penyesuaian = parseInt(penyesuaian.replace(/\./g, ''));
            var grandTotal = {{$keranjangData->sum('nominal')}};
            var total = penyesuaian + grandTotal;
            $('#penyesuaianHidden').val(penyesuaian);
            $('#grandTotal').text(total.toLocaleString('id-ID'));
        }
</script>
@endpush
