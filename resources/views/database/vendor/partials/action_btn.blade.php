<div class="d-flex justify-content-center">
    <a href="{{route('uj.vendor.biodata-vendor', $d->id)}}" target="_blank" class="btn btn-success btn-sm me-2">PDF</a>
    <a href="{{route('vendor.edit', $d->id)}}" class="btn btn-warning btn-sm me-2">Ubah</a>
    <form action="{{route('vendor.destroy', $d->id)}}" method="post" class="d-inline">
        @csrf
        @method('delete')
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</button>
    </form>
</div>
