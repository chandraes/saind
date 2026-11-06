@foreach($rows as $row)
<tr>
    <td class="text-center text-muted">{{ $offset + $loop->iteration }}</td>
    <td class="text-center text-muted">{{ $row['tanggal'] ?? '—' }}</td>
    <td class="text-center fw-semibold">{{ $row['vendor'] }}</td>
    <td class="text-center"><a href="{{ $row['url'] }}" class="invoice-vendor-link">{{ $row['invoice'] }}</a></td>
    <td class="text-center">@if($row['tempo'])<span class="badge invoice-vendor-due {{ $row['tempo'] < date('Y-m-d') ? 'invoice-vendor-due-overdue' : 'invoice-vendor-due-upcoming' }}">{{ $row['tempo'] }}</span>@else<span class="text-muted">—</span>@endif</td>
    <td class="text-center">
        <span class="badge invoice-vendor-status {{ $row['paid'] ? 'invoice-vendor-status-paid' : 'invoice-vendor-status-unpaid' }}"><i class="fa {{ $row['paid'] ? 'fa-check-circle' : 'fa-clock-o' }}" aria-hidden="true"></i>{{ $row['paid'] ? 'Lunas' : 'Belum lunas' }}</span>
        @if($row['paid'])<small class="invoice-vendor-paid-date">{{ $row['paid_on'] ? 'Tanggal pelunasan: '.$row['paid_on'] : 'Tanggal belum tercatat' }}</small>@endif
    </td>
</tr>
@endforeach
