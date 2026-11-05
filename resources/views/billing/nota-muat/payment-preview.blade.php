@php($isFinal = $preview['is_final'] ?? false)
<section class="card mb-4" aria-labelledby="note-payment-title">
    <div class="note-payment-header">
        <h2 class="h6 fw-bold mb-0" id="note-payment-title"><i class="fa fa-truck me-2" aria-hidden="true"></i>{{ $isFinal ? 'Uang jalan tercatat' : 'Rencana uang jalan' }}</h2>
        <span class="badge {{ $preview['held'] ? 'bg-success' : 'bg-secondary' }}">{{ $isFinal ? 'Data transaksi' : 'UJ ditahan '.($preview['held'] ? 'aktif' : 'nonaktif') }}</span>
    </div>
    <div class="card-body">
        <div class="note-money-summary">
            <div>
                <p class="note-caption mb-1">{{ $preview['held'] ? 'Sisa transfer (netto)' : 'Uang jalan ditransfer' }}</p>
                <div class="note-transfer-value">{{ $preview['transfer'] === null ? 'Belum tersedia' : 'Rp '.number_format($preview['transfer'], 0, ',', '.') }}</div>
                <span class="note-source-pill mt-2">{{ $isFinal ? 'Pencatatan transaksi #'.$note->transaksi_id : ($preview['is_custom'] ? 'Nominal khusus nota ini' : 'Kesepakatan vendor–rute') }}</span>
            </div>
            @if ($note->status === 'draft')
                @can('adjustPayment', $note)
                    <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#note-payment-modal"><i class="fa fa-pencil" aria-hidden="true"></i>Atur nominal</button>
                @else
                    @can('reviewAny', \App\Models\LoadNoteDraft::class)
                    <p class="note-action-note mb-0">Perubahan nominal hanya oleh admin atau su.</p>
                    @endcan
                @endcan
            @endif
        </div>
        @foreach ($preview['warnings'] as $warning)
            <div class="alert alert-warning py-2" role="alert"><i class="fa fa-exclamation-triangle me-2" aria-hidden="true"></i>{{ $warning }}</div>
        @endforeach
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <caption class="visually-hidden">Rincian estimasi uang jalan untuk nota {{ $note->ticket_number }}</caption>
                        <thead class="table-success"><tr><th scope="col">Rincian uang jalan</th><th scope="col" class="text-end">Nominal</th></tr></thead>
                        <tbody>
                            @foreach (['gross' => 'Total uang jalan (kotor)', 'sparepart' => 'UJ Ditahan Sparepart', 'ban' => 'UJ Ditahan Ban', 'transfer' => 'Sisa transfer (netto)'] as $key => $label)
                            <tr>
                                <th scope="row" class="fw-normal">{{ $label }}@if (in_array($key, ['sparepart', 'ban']) && ! $preview['held'] && ! $isFinal)<span class="d-block small text-muted">Tidak diaktifkan pada kendaraan</span>@endif</th>
                                <td class="text-end text-nowrap {{ $key === 'transfer' ? 'fw-bold text-success' : '' }}">{{ $preview[$key] === null ? 'Belum tersedia' : 'Rp '.number_format($preview[$key], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($preview['is_custom'])
                    <p class="note-caption mt-3 mb-0">Nominal dasar vendor–rute: <strong>{{ $preview['system_transfer'] === null ? 'Belum tersedia' : 'Rp '.number_format($preview['system_transfer'], 0, ',', '.') }}</strong>. Perubahan khusus nota ini tercatat pada riwayat.</p>
                @endif
            </div>
            <div class="col-lg-5">
                <div class="note-driver-panel">
                    <h3 class="h6 fw-bold mb-3"><i class="fa fa-user me-2" aria-hidden="true"></i>Driver dan tujuan transfer</h3>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">{{ $isFinal ? 'Pengemudi pada nota' : 'Driver terdaftar' }}</dt><dd class="col-sm-7">{{ $preview['driver'] ?: 'Belum diatur' }}</dd>
                        <dt class="col-sm-5">Telepon</dt><dd class="col-sm-7">{{ $preview['driver_phone'] ?: 'Belum tersedia' }}</dd>
                        <dt class="col-sm-5">Nama rekening</dt><dd class="col-sm-7">{{ $preview['recipient'] ?: 'Belum tersedia' }}</dd>
                    </dl>
                    <div class="note-account"><span class="small text-muted">{{ $preview['bank'] ?: 'Bank belum tersedia' }}</span><strong>{{ $preview['account'] ?: 'Nomor rekening belum tersedia' }}</strong></div>
                    <p class="note-caption mt-3 mb-0">{{ match ($preview['account_source']) { 'transaction' => 'Rekening pada transaksi, sesuai pencatatan saat uang jalan diproses.', 'driver' => 'Tujuan transfer dari rekening driver terhubung.', default => 'Tujuan transfer dari database kendaraan.' } }}</p>
                </div>
            </div>
        </div>
        <p class="note-caption mt-3 mb-0"><i class="fa fa-info-circle me-1" aria-hidden="true"></i>{{ $isFinal ? 'Nominal dan rekening diambil dari pencatatan transaksi, bukan pengaturan kendaraan atau kesepakatan saat ini.' : 'Belum ada pencairan dari halaman ini. Tahanan mengikuti pengaturan aktif saat ini; kelayakan dan nominal akhir diperiksa kembali di form uang jalan keluar. Rencana ini bukan bukti nominal historis transaksi.' }}</p>
    </div>
</section>
@if ($note->status === 'draft')
@can('adjustPayment', $note)
<div class="modal fade" id="note-payment-modal" tabindex="-1" aria-labelledby="note-payment-modal-title" aria-hidden="true" data-system-transfer="{{ $preview['system_transfer'] }}" data-sparepart="{{ $preview['sparepart'] }}" data-ban="{{ $preview['ban'] }}">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
        <div class="modal-header"><h2 class="modal-title fs-5" id="note-payment-modal-title">Atur uang jalan nota</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <form method="POST" action="{{ route('billing.nota-muat.payment.update', $note) }}" data-note-review data-confirm="Simpan nominal uang jalan untuk nota {{ $note->ticket_number }}?" novalidate>
            @csrf @method('PATCH')<input type="hidden" name="revision" value="{{ $note->revision }}"><input type="hidden" name="use_system_amount" id="note-use-system-value" value="0">
            <div class="modal-body">
                <p class="small text-muted">Nota <strong>{{ $note->ticket_number }}</strong> · Unit <strong>{{ $note->vehicle_number }}</strong>. Perubahan berlaku untuk nota ini dan tidak mengubah kesepakatan vendor–rute.</p>
                <label for="note-transfer-input" class="form-label">{{ $preview['held'] ? 'Sisa transfer (netto)' : 'Uang jalan' }} <span class="text-danger">*</span></label>
                <div class="input-group mb-2"><span class="input-group-text">Rp</span><input id="note-transfer-input" class="form-control" inputmode="numeric" autocomplete="off" value="{{ $preview['transfer'] }}" required><input type="hidden" name="transfer_amount" id="note-transfer-raw" value="{{ $preview['transfer'] }}"></div>
                <p class="small text-muted">Isi rupiah bulat. Tahanan sparepart dan ban mengikuti pengaturan kendaraan; keduanya tidak diubah di sini.</p>
                <div class="form-check mb-3"><input type="checkbox" class="form-check-input" id="note-use-system"><label class="form-check-label" for="note-use-system">Kembalikan ke nominal kesepakatan vendor–rute</label></div>
                <div class="note-edit-total mb-3">Total uang jalan (kotor): <strong id="note-payment-total" aria-live="polite">Belum tersedia</strong></div>
                <label for="note-payment-reason" class="form-label">Alasan perubahan <span class="text-danger">*</span></label><textarea id="note-payment-reason" class="form-control" name="reason" maxlength="1000" rows="3" required placeholder="Jelaskan penyesuaian nominal atau pengembalian ke pengaturan sistem"></textarea>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kembali</button><button type="submit" class="btn btn-primary">Simpan nominal</button></div>
        </form>
    </div></div>
</div>
@endcan
@endif
