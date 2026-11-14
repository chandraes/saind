@php
    $periodQuery = ['bulan' => $period['month'], 'tahun' => $period['year']];
    $money = fn (int $value) => number_format($value, 0, ',', '.');
@endphp
                @forelse ($rows as $row)
                <tr>
                    <td class="text-center">{{ $rows->firstItem() + $loop->index }}</td>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ $row['jabatan'] }}</td>
                    <td class="text-end">
                        @if ($row['has_detail'])
                            <button type="button" class="btn btn-link text-danger p-0 fw-semibold text-decoration-underline" data-deduction-detail="{{ route('hrd.attendance-deductions.show', ['key' => $row['key']] + $periodQuery) }}" aria-label="Lihat rincian potongan absensi {{ $row['nama'] }}">{{ $money($row['potongan_absensi']) }}</button>
                        @elseif ($row['potongan_absensi'] > 0)
                            <span class="text-danger fw-semibold">{{ $money($row['potongan_absensi']) }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">{{ $filtered ? 'Tidak ada pegawai yang sesuai pencarian atau filter.' : 'Belum ada pegawai pada periode ini.' }}</td></tr>
                @endforelse
