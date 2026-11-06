@foreach ($rows as $row)
<tr>
    <td class="text-center align-middle">{{ $offset + $loop->iteration }}</td>
    @foreach ($row['cells'] as $key => $cell)
        <td class="text-center align-middle">
            @if ($key === 'Kode' && ($row['linkInCode'] ?? false))
                <a class="js-readonly-detail" href="{{ $row['url'] }}"><strong>{{ $cell }}</strong></a>
            @elseif ($key === 'Kode' && ($row['linkInCode'] ?? false))
                <a class="js-readonly-detail" href="{{ $row['url'] }}"><strong>{{ $cell }}</strong></a>
            @else
                {{ $cell === null || $cell === '' ? '—' : $cell }}
            @endif
        </td>
    @endforeach
    @if ($row['url'] && !($row['linkInCode'] ?? false))
        <td><a class="btn btn-outline-primary btn-sm text-nowrap {{ ($page ?? '') !== 'invoice' ? 'js-readonly-detail' : '' }}" href="{{ $row['url'] }}"><i class="fa fa-eye me-1" aria-hidden="true"></i>Detail</a></td>
    @endif
</tr>
@endforeach
