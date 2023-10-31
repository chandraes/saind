<div class="modal fade" id="modal-edit-{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Ubah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

                <form action="{{route('pengguna.update', $d->id)}}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama</label>
                                <input type="text" class="form-control" name="name" id="name" aria-describedby="name"
                                    placeholder="" value="{{$d->name}}">
                            </div>
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="username" required
                                    aria-describedby="username" placeholder="" value="{{$d->username}}">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" name="role" id="role-{{$d->id}}" onchange="roleChangeEdit()">
                                    <option value="admin" {{$d->role == 'admin' ? 'selected' : ''}}>Admin</option>
                                    <option value="vendor" {{$d->role == 'vendor' ? 'selected' : ''}}>Vendor</option>
                                    <option value="user" {{$d->role == 'user' ? 'selected' : ''}}>User</option>
                                </select>
                            </div>
                            <div class="col-md-12 mt-3" id="divVendor-{{$d->id}}">
                                <div class="mb-3">
                                    <label for="vendor_id" class="form-label">Vendor</label>
                                    <select class="form-select" name="vendor_id" id="vendor_id-{{$d->id}}">
                                        <option value=""> -- Pilih Vendor -- </option>
                                        @foreach ($vendor as $v)
                                        <option value="{{$v->id}}" @if ($d->vendor_id == $v->id)
                                            selected
                                        @endif>{{$v->nama}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <label for="email" class="form-label">E-Mail</label>
                                <input type="email" class="form-control" name="email" id="email"
                                    aria-describedby="email" placeholder="boleh kosong">
                                <div class="form-group mt-2">
                                    <label for="passwordInput">Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" class="form-control" id="passwordInput" placeholder="Kosongkan Jika Tidak Mengganti">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
        </div>
    </div>
</div>
<script>
    function roleChangeEdit() {

        var role = document.getElementById('role-{{$d->id}}').value;
        if (role === 'vendor') {
            document.getElementById('divVendor-{{$d->id}}').hidden = false;
            // vendor_id required
            document.getElementById('vendor_id-{{$d->id}}').required = true;
        } else {
            document.getElementById('divVendor-{{$d->id}}').hidden = true;
            // vendor_id not required
            document.getElementById('vendor_id-{{$d->id}}').required = false;
        }
    }
</script>
