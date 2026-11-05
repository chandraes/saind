                    @forelse($notes as $note)
                    <tr>
                        <td class="text-center text-nowrap">{{ $notes->firstItem() + $loop->index }}</td>
                        <td class="text-break"><strong>{{ $note->ticket_number }}</strong><br><small class="text-muted">{{ $note->loaded_on->format('d-m-Y') }}</small></td>
                        <td class="text-break">{{ $note->vendor?->nama ?? '-' }}<br><small class="text-muted">Unit {{ $note->vehicle_number }}</small></td>
                        <td class="text-break">{{ $note->mine_name }}</td>
                        <td class="text-nowrap">{{ number_format($note->net_kg / 1000, 3, ',', '.') }}</td>
                        <td class="text-break">{{ $note->creator?->name ?? '-' }}<br><small class="text-muted">{{ $note->created_at->format('d-m-Y H:i') }}</small></td>
                        <td>@include('billing.nota-muat.status')</td>
                        <td><a href="{{ route('billing.nota-muat.show', ['note' => $note, ...\Illuminate\Support\Arr::except($filters, ['page'])]) }}" class="btn btn-outline-primary btn-sm" aria-label="Lihat detail nota {{ $note->ticket_number }}">Detail</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">Belum ada nota yang sesuai. Nota akan tampil setelah vendor mengirimkannya dari aplikasi mobile.</td></tr>
                    @endforelse
