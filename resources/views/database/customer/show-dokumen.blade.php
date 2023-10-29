<div class="modal fade" id="modalDokumen{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Dokumen Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center align-middle">No</th>
                            <th class="text-center align-middle">Nama Dokumen</th>
                            <th class="text-center align-middle">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($d->document as $doc)
                        <tr class="text-center align-middle">
                            <td>{{$loop->iteration}}</td>
                            <td>{{$doc->nama_dokumen}}</td>
                            <td>
                                <a href="{{route('customer.document-download', [$doc->id])}}"
                                    class="btn btn-primary m-2" target="_blank">
                                    <i class="fa fa-download"></i>
                                </a>
                                <form action="{{route('customer.document-destroy', [$doc->id])}}"
                                    method="post" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger m-2"
                                        onclick="return confirm('Apakah anda yakin ingin menghapus data ini?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
