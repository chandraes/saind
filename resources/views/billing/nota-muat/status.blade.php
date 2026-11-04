@php($statusClass = match ($note->status) { 'processed' => 'bg-success', 'rejected' => 'bg-danger', 'cancelled' => 'bg-secondary', default => 'bg-warning text-dark' })
<span class="badge {{ $statusClass }} p-2">{{ $note->statusLabel() }}</span>
