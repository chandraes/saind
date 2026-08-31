<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-uang{{$d->id}}">
    Lihat Uang Jalan
</button>

<div class="modal fade" id="modal-uang{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId{{$d->id}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId{{$d->id}}">Kesepakatan Uang Jalan - {{ $d->nama }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if ($d->vendor_uang_jalan->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-dark text-center">
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th>Rute</th>
                                <th style="width: 30%;">Harga Uang Jalan</th>
                                <th style="width: 30%;">UJ Ditahan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($d->vendor_uang_jalan as $item)
                            <tr>
                                <td class="text-center fw-bold">{{$loop->iteration}}</td>
                                <td>{{$item->rute ? $item->rute->nama : '-'}}</td>
                                <td class="text-end">Rp. {{number_format($item->hk_uang_jalan, 0, ',', '.')}}</td>
                                <td class="text-end">Rp. {{number_format($item->uj_ditahan ?? 0, 0, ',', '.')}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center text-muted my-3">Belum ada kesepakatan uang jalan.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="{{route('uj.vendor.uang-jalan.edit', $d->id)}}" class="btn btn-primary">Edit Uang Jalan</a>
            </div>
        </div>
    </div>
</div>
