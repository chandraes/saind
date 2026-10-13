<div class="d-flex justify-content-center gap-2 text-nowrap">
    <a href="{{ route('karyawan.show', ['karyawan' => $employee->id, 'format' => 'html']) }}" class="btn btn-sm btn-outline-primary" aria-label="Detail pegawai {{ $employee->nama }}"><i class="fa fa-eye me-1"></i> Detail</a>
    <a href="{{ route('karyawan.edit', $employee->id) }}" class="btn btn-sm btn-outline-success" aria-label="Ubah pegawai {{ $employee->nama }}"><i class="fa fa-pencil me-1"></i> Ubah</a>
    <button type="button" class="btn btn-sm btn-outline-danger employee-delete" data-url="{{ route('karyawan.destroy', $employee->id) }}" data-name="{{ $employee->nama }}" aria-label="Hapus pegawai {{ $employee->nama }}"><i class="fa fa-trash-o me-1"></i> Hapus</button>
</div>
