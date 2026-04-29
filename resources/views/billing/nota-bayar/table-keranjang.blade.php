<div class="container-fluid mt-3 table-responsive ">
    <table class="table table-bordered table-hover" id="notaTable">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Tanggal UJ</th>
                <th class="text-center align-middle">Kode</th>
                <th class="text-center align-middle">Nomor Lambung</th>
                <th class="text-center align-middle">Vendor</th>
                <th class="text-center align-middle">Rute</th>
                <th class="text-center align-middle">Jarak</th>
                <th class="text-center align-middle">Harga</th>
                <th class="text-center align-middle">Tanggal Muat</th>
                <th class="text-center align-middle">Nota Muat</th>
                <th class="text-center align-middle">Tonase Muat</th>
                <th class="text-center align-middle">Tanggal Bongkar</th>
                <th class="text-center align-middle">Nota Bongkar</th>
                <th class="text-center align-middle">Tonase Bongkar</th>
                <th class="text-center align-middle">Selisih (Ton)</th>
                <th class="text-center align-middle">Selisih (%)</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            @php
                $d = $d->transaksi;
            @endphp
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->tanggal}}</td>
                <td class="align-middle">
                       <strong>UJ{{sprintf("%02d",
                                $d->kas_uang_jalan->nomor_uang_jalan)}}</strong>
                </td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->vehicle->nomor_lambung}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->vendor->nickname}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->nama}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->jarak}}</td>
                <td class="text-center align-middle">{{$invoice->nf_dpp}}</td>
                <td class="text-center align-middle">{{$d->id_tanggal_muat}}</td>
                <td class="text-center align-middle">{{$d->nota_muat}}</td>
                <td class="text-center align-middle">{{$d->tonase}}</td>
                <td class="text-center align-middle">{{$d->id_tanggal_bongkar}}</td>
                <td class="text-center align-middle">{{$d->nota_bongkar}}</td>
                <td class="text-center align-middle">{{$d->timbangan_bongkar}}</td>
                <td class="text-center align-middle">{{number_format($d->tonase - $d->timbangan_bongkar, 2, ',','.')}}
                </td>
                <td class="text-center align-middle">{{number_format(($d->tonase - $d->timbangan_bongkar)*0.1, 2,
                    ',','.')}}</td>

            </tr>
            @endforeach
        </tbody>
    </table>


</div>
<div class="container mt-4">
    <div class="card shadow-sm border-secondary-subtle">
        <div class="card-header bg-dark text-white fw-bold">
            <i class="fa fa-calculator me-2"></i> Ringkasan Perhitungan Per Rute
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Nama Rute</th>
                        <th class="text-center" style="width: 120px;">Jarak (km)</th>
                        <th class="text-center" style="width: 140px;">Total Muatan</th>
                        <th class="text-end" style="width: 180px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach($groupedData as $customerName => $customerData)

                        <!-- Header Nama Customer -->
                        <tr class="table-primary">
                            <td colspan="5" class="fw-bold">
                                <i class="fa fa-building me-2"></i>{{ $customerName }}
                            </td>
                        </tr>

                        <!-- Detail Rute -->
                        @foreach($customerData['rutes'] as $ruteName => $ruteData)
                        <tr>
                            <td class="text-center text-muted">{{ $no++ }}</td>
                            <td>
                                {{ $ruteName }}
                                <span class="badge bg-light text-dark border ms-2">
                                    {{ $ruteData['jumlah_trx'] }} Trx
                                </span>
                            </td>
                            <td class="text-center">{{ number_format($ruteData['jarak'], 2, ',', '.') }}</td>
                            <td class="text-center fw-bold">{{ number_format($ruteData['total_muatan'], 2, ',', '.') }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($ruteData['subtotal'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach

                        <!-- Subtotal Customer -->
                        <tr class="table-info">
                            <td colspan="4" class="text-end fw-bold">Subtotal {{ $customerName }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($customerData['subtotal_customer'], 0, ',', '.') }}</td>
                        </tr>

                    @endforeach
                </tbody>
               <tfoot class="table-success">
                    <tr>
                        <th colspan="4" class="text-end">Subtotal (DPP)</th>
                        <th class="text-end">Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</th>
                    </tr>
                    @if($ppn > 0)
                    <tr>
                        <th colspan="4" class="text-end">PPN (11%)</th>
                        <th class="text-end">Rp {{ number_format($ppn, 0, ',', '.') }}</th>
                    </tr>
                    @endif
                    @if($pph > 0)
                    <tr>
                        <th colspan="4" class="text-end">PPH ({{ $vendor->pph_val }}%)</th>
                        <th class="text-end text-danger">- Rp {{ number_format($pph, 0, ',', '.') }}</th>
                    </tr>
                    @endif
                    <tr>
                        <th colspan="4" class="text-end fs-5">TOTAL REKAP</th>
                        <th class="text-end fs-5">Rp {{ number_format($totalAkhir, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
    </div>
</div>
