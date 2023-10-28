<div class="modal fade" id="persenAwal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Persentase Awal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-responsive table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center align-middle">No</th>
                            <th class="text-center align-middle">Persentase (%)</th>
                            <th class="text-center align-middle">Nama</th>
                            <th class="text-center align-middle">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($persen as $j)
                        <tr>
                            <td class="text-center align-middle">{{$loop->iteration}}</td>
                            <td class="text-center align-middle">
                                <form action="{{route('database.persentase-awal-update', $j)}}" method="post"
                                id="updateJabatan">
                                @csrf
                                @method('patch')
                                <input type="number" class="form-control" name="persentase" id="persentase-{{$j->id}}"
                                    aria-describedby="helpId" placeholder="" value="{{$j->persentase}}" readonly>
                            </td>
                            <td class="text-center align-middle">

                                    <input type="text" class="form-control" name="nama"
                                        id="nama-{{$j->id}}" aria-describedby="helpId" placeholder=""
                                        value="{{$j->nama}}" readonly>


                            </td>
                            <td class="text-center align-middle">
                                {{-- button to submit form #updateJabatan --}}
                                <div class="btn-group m-3" role="group" aria-label="Save or cancel"
                                        id="buttonJabatan-{{$j->id}}" hidden>
                                        <button type="submit" class="btn btn-success">Simpan</button>
                                        <a onclick="toggleNamaJabatan({{$j->id}})" type="button"
                                            class="btn btn-secondary">Batal</a>
                                    </div>
                                </form>
                                <a onclick="toggleNamaJabatan({{$j->id}})" class="btn btn-warning"><i
                                        class="fa fa-edit"></i></a>
                                {{-- form delete with confirmation --}}
                                <form action="{{route('database.persentase-awal-destroy', $j->id)}}" method="post"
                                    class="d-inline">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Apakah anda yakin untuk menghapus jabatan ini?')"><i
                                            class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <hr>
                <form action="{{route('database.persentase-awal-store')}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" name="nama" id="nama" aria-describedby="helpId" required
                                placeholder="">
                        </div>
                        <div class="col-4">
                            <label for="persentase" class="form-label">Persentase</label>
                            <input type="number" class="form-control" name="persentase" id="persentase" required
                                aria-describedby="helpId" placeholder="">
                        </div>
                        <div class="col-4">
                            <label for="persentase" class="form-label">&nbsp;</label>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Tambah</button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
