@php($statusClass = match ($note->status) { 'processed' => 'bg-success', 'void', 'rejected', 'cancelled' => 'bg-danger', default => 'bg-warning text-dark' })
<span class="badge {{ $statusClass }} p-2">{{ $note->statusLabel() }}</span>
