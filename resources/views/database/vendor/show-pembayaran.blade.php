<div class="modal fade" id="modal-pembayaran{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                  @if ($d->vendor_bayar->count() > 0)
                  @foreach ($d->vendor_bayar as $i)

                    {{$i->customer->nama}}
                    <table class="table table-hover table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center align-middle">Rute</th>
                                <th class="text-center align-middle">Kesepakatan Opname</th>
                                <th class="text-center align-middle">Kesepakatan Titipan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($i as $a)
                            <tr>
                                <td class="text-center align-middle">{{$a}}</td>
                                <td class="text-center align-middle"></td>
                                <td class="text-center align-middle"></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                  @endforeach
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
                                  @if ($d->vendor_bayar->where('customer_id', $c->id)->first() && $d->pembayaran == 'opname')
                                  Rp.
                                  {{
                                      number_format($d->vendor_bayar->where('customer_id', $c->id)->pluck('harga_kesepakatan')['0'], 0, ',', '.')
                                      }}
                                  @endif

                              </td>
                              <td class="text-center align-middle">
                                  @if ($d->vendor_bayar->where('customer_id', $c->id)->first() && $d->pembayaran == 'titipan')
                                  Rp.  {{
                                      number_format($d->vendor_bayar->where('customer_id', $c->id)->pluck('harga_kesepakatan')['0'], 0, ',', '.')
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
                <a href="{{route('vendor.pembayaran.edit', $d->id)}}" type="button" class="btn btn-primary">Edit Pembayaran</a>
            </div>
        </div>
    </div>
</div>
