<div class="d-flex flex-nowrap gap-1" aria-label="Dokumen pegawai">
@foreach (['foto_diri' => ['Foto', 'fa-user'], 'foto_ktp' => ['KTP', 'fa-id-card-o'], 'perjanjian_kerja' => ['Perjanjian', 'fa-file-text-o']] as $field => [$label, $icon])
    @if($employee->$field)
        <a href="{{ route('karyawan.document', ['karyawan' => $employee->id, 'document' => $field]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" aria-label="Lihat {{ $label }} {{ $employee->nama }}"><i class="fa {{ $icon }} me-1" aria-hidden="true"></i>{{ $label }}</a>
    @else
        <span class="badge bg-light text-muted border" title="{{ $label }} belum tersedia">{{ $label }} —</span>
    @endif
@endforeach
</div>
