<div class="d-flex justify-content-center gap-2">
    <a href="{{ $type === 'aki' ? route('rekap.maintenance.aki.show', $invoice->id) : route('billing.otorisasi-maintenance.filter-oli.show', $invoice->id) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
    <form action="{{ route('billing.otorisasi-maintenance.'.$type.'.approve', $invoice->id) }}" method="post" data-maintenance-form data-confirm="Setujui invoice ini? Histori dan pembayaran akan diproses.">
        @csrf
        @if ($warnings)
            <template data-authorization-warning>
                <p>{{ $type === 'aki' ? 'Aki berikut berumur kurang dari 365 hari saat diganti:' : 'Penggantian berikut dilakukan sebelum limit ritase:' }}</p>
                <div class="table-responsive"><table class="table table-sm"><thead class="table-success"><tr><th>{{ $type === 'aki' ? 'Posisi' : 'Kategori' }}</th><th>Keterangan</th></tr></thead><tbody>
                @foreach ($warnings as $warning)<tr><td>{{ $warning['label'] }}</td><td>{{ $warning['description'] }}</td></tr>@endforeach
                </tbody></table></div><p>Tetap setujui penggantian?</p>
            </template>
        @endif
        <button type="submit" class="btn btn-sm btn-success">Approve</button>
    </form>
    <form action="{{ route('billing.otorisasi-maintenance.'.$type.'.reject', $invoice->id) }}" method="post" data-maintenance-form data-confirm="Tolak invoice ini? Saldo tidak akan dipotong.">@csrf<button type="submit" class="btn btn-sm btn-danger">Reject</button></form>
</div>
