                <thead class="table-success border-bottom">
                    <tr>
                        @if($vendorReadonly ?? false)<th class="text-center" style="width: 45px;">No</th>@endif
                        <th class="text-center" style="width: 110px;">Tanggal</th>
                        <th class="text-center" style="width: 180px;">Vendor</th>
                        <th class="text-center" style="width: 160px;">Invoice</th>
                        <th class="text-center" style="width: 130px;">Jatuh Tempo</th>
                        @if($vendorReadonly ?? false)<th class="text-center" style="width: 170px;">Status</th>@endif
                        @if(($vendorReadonly ?? false) && ($showTotal ?? false))<th class="text-end" style="width: 180px;">Total Bayar</th>@endif
                        @unless($vendorReadonly ?? false)<th class="text-end" style="width: 180px;">Total Bayar</th>@endunless
                        @if (!($vendorReadonly ?? false) && ! auth()->user()->isVendorOwner())
                        <th class="text-center" style="width: 120px;">Pembayaran</th>
                        @endif
                        @if (!($vendorReadonly ?? false) && (Auth::user()->role == 'su' || Auth::user()->role == 'admin'))
                        <th class="text-center" style="width: 100px;">Aksi</th>
                        @endif
                    </tr>
                </thead>
