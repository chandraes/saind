<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-uang{{$d->id}}">
    Lihat Uang Jalan
</button>
<div class="modal fade" id="modal-uang{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId{{$d->id}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId{{$d->id}}">Kesepakatan Uang Jalan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if ($d->vendor_uang_jalan->count() > 0)
                <table class="table table-bordered table-hover">
                    <thead class="text-center align-middle">
                        <th>No</th>
                        <th>Rute</th>
                        <th>Kesepakatan Uang Jalan</th>
                    </thead>
                    <tbody>
                        @foreach ($d->vendor_uang_jalan as $item)
                        <tr>
                            <td class="text-center">{{$loop->iteration}}</td>
                            <td>{{$item->rute ? $item->rute->nama : '-'}}</td>
                            <td>Rp. {{number_format($item->hk_uang_jalan, 0, ',', '.')}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-center text-muted mt-2">Belum ada kesepakatan uang jalan.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="{{route('uj.vendor.uang-jalan.edit', $d->id)}}" class="btn btn-primary">Edit Uang Jalan</a>
            </div>
        </div>
    </div>
</div>
