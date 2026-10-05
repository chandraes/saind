<template id="filterOliLimitAlert">
    <p class="text-muted text-start mb-3">Pengeluaran Uang Jalan belum dapat diproses. Periksa kategori berikut dan lakukan penggantian filter atau oli mesin.</p>
    <div class="table-responsive"><table class="table table-bordered table-hover align-middle mb-0 text-start">
        <thead class="table-success"><tr><th>No</th><th>Filter / Kategori</th><th class="text-nowrap">Ritase saat ini</th><th class="text-nowrap">Limit ritase</th><th>Keterangan</th></tr></thead>
        <tbody>
            @foreach (session('filter_oli_limit_issues', []) as $issue)
                <tr><td>{{ $loop->iteration }}</td><td class="fw-semibold">{{ $issue['category'] }}</td><td class="text-nowrap">{{ $issue['ritase'] === null ? '—' : number_format($issue['ritase'], 1, ',', '.').' rit' }}</td><td class="text-nowrap">{{ $issue['limit'] }} rit</td><td><span class="badge {{ $issue['ritase'] === null ? 'bg-warning text-dark' : 'bg-danger' }}">{{ $issue['reason'] }}</span></td></tr>
            @endforeach
        </tbody>
    </table></div>
</template>
@push('js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            icon: 'error', title: 'Periksa Filter & Oli Mesin',
            html: document.getElementById('filterOliLimitAlert').innerHTML,
            width: 960, confirmButtonText: 'OK, saya mengerti',
        });
    });
</script>
@endpush
