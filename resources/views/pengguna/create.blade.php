<div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
        aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-l" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('pengguna.store')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama</label>
                                <input type="text" class="form-control" name="name" id="name" aria-describedby="name"
                                    placeholder="">
                            </div>
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="username" required
                                    aria-describedby="username" placeholder="">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" name="role" id="role" onchange="roleChange()">
                                    <option selected>Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="vendor">Vendor</option>
                                    <option value="user">User</option>
                                    <option value="customer">Customer</option>
                                </select>
                            </div>
                            <div class="col-md-12 mt-3" id="divVendor" hidden>
                                <div class="mb-3">
                                    <label for="vendor_id" class="form-label">Vendor</label>
                                    <select class="form-select" name="vendor_id" id="vendor_id">
                                        <option value=""> -- Pilih Vendor -- </option>
                                        @foreach ($vendor as $v)
                                        <option value="{{$v->id}}">{{$v->nama}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3" id="divCustomer" hidden>
                                <div class="mb-3">
                                    <label for="customer_id" class="form-label">Customer</label>
                                    <select class="form-select" name="customer_id" id="customer_id">
                                        <option value=""> -- Pilih Customer -- </option>
                                        @foreach ($customer as $c)
                                        <option value="{{$c->id}}">{{$c->nama}}</option>
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
                                        <input type="password" name="password" class="form-control" id="passwordInput" required>
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
        function roleChange() {
            var role = document.getElementById('role').value;
            if (role == 'vendor') {
                document.getElementById('divVendor').hidden = false;
                document.getElementById('divCustomer').hidden = true;
                // vendor_id required
                document.getElementById('vendor_id').disabled = false;
                document.getElementById('vendor_id').required = true;
                document.getElementById('customer_id').required = false;
            } else if(role == 'customer') {
                document.getElementById('divCustomer').hidden = false;
                document.getElementById('divVendor').hidden = true;
                // vendor_id required
                document.getElementById('customer_id').disabled = false;
                document.getElementById('customer_id').required = true;
                document.getElementById('vendor_id').required = false;


            } else {
                document.getElementById('divVendor').hidden = true;
                document.getElementById('divCustomer').hidden = true;
                // vendor_id not required
                // disable vendor_id and customer_id

                document.getElementById('customer_id').disabled = true;
                document.getElementById('vendor_id').disabled = true;

                document.getElementById('customer_id').required = false;
                document.getElementById('vendor_id').required = false;
            }
        }
    </script>
