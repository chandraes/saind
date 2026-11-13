                        @forelse($details as $d)
                        <tr>
                            <td class="text-center align-middle">{{ ($offset ?? 0) + $loop->iteration }}</td>
                            <td class="align-middle">{{ $d->created_at->format('d-m-Y') }}</td>
                            <td class="align-middle">
                                {{ $d->keterangan }}
                                @if($d->payout_origin === 'payout_reversal')<div><span class="badge bg-primary">Transaksi pembalik</span></div>@endif
                                @if($d->cancellation)
                                    <div class="mt-2"><span class="badge bg-danger">Dibatalkan</span><div class="small text-muted mt-1">{{ $d->cancellation->reason }}<br>Oleh {{ $d->cancellation->actor?->name ?? '-' }} · {{ $d->cancellation->created_at->format('d-m-Y H:i') }}</div></div>
                                @endif
                                @if($d->driver)
                                    <br><small class="text-muted">Driver: {{ $d->driver->nama }}</small>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if($d->bank && $d->no_rekening)
                                    <strong>{{ $d->bank }}</strong> - {{ $d->no_rekening }}<br>
                                    <small>a.n {{ $d->nama_rekening }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <!-- Kolom Bukti PDF Baru -->
                            <td class="text-center align-middle">
                                @if(!($driverLedger ?? false) && $d->file_pdf)
                                    <a href="{{ Storage::url($d->file_pdf) }}" target="_blank" class="btn btn-sm btn-outline-danger shadow-sm" title="Lihat Dokumen PDF">
                                        <i class="fa fa-file me-1"></i> PDF
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <!-- Kolom Uang Masuk -->
                            <td class="text-right align-middle font-weight-bold text-success">
                                {{ $d->jenis == 'masuk' ? 'Rp ' . number_format($d->nominal, 0, ',', '.') : '-' }}
                            </td>

                            <!-- Kolom Uang Keluar -->
                            <td class="text-right align-middle font-weight-bold text-danger">
                                {{ $d->jenis == 'keluar' ? 'Rp ' . number_format($d->nominal, 0, ',', '.') : '-' }}
                            </td>
                            @unless ($readOnlyLedger ?? $driverLedger ?? false)
                            <td class="align-middle">
                                @if(in_array(auth()->user()?->role, ['admin', 'su'], true) && $d->isManualPayout() && ! $d->cancellation)
                                <button type="button" class="btn btn-sm btn-outline-danger text-nowrap" data-payout-cancel-url="{{ route('billing.uj-ditahan.cancel', ['fund' => 'sparepart', 'detail' => $d->id]) }}" data-payout-amount="Rp {{ number_format($d->nominal, 0, ',', '.') }}"><i class="fa fa-undo me-1" aria-hidden="true"></i>Batalkan</button>
                                @else<span class="text-muted">—</span>@endif
                            </td>
                            @endunless
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ ($readOnlyLedger ?? $driverLedger ?? false) ? 7 : 8 }}" class="text-center py-4">Belum ada histori transaksi untuk data ini.</td>
                        </tr>
                        @endforelse
