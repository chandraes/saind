@foreach ($rows as $row)
<tr>
    <td class="text-center align-middle">{{ $offset + $loop->iteration }}</td>
    @foreach ($row['cells'] as $label => $value)
        <td class="text-center align-middle">
            @if ($label === 'Kode')
                <a class="js-readonly-detail" href="{{ $row['url'] }}"><strong>{{ $value }}</strong></a>
            @else
                {{ $value === null || $value === '' ? '—' : $value }}
            @endif
        </td>
    @endforeach
</tr>
@endforeach
