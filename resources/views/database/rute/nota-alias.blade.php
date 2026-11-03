<section class="card shadow-sm border-0 mt-4" aria-labelledby="note-alias-title">
    <div class="card-body">
        <h3 id="note-alias-title" class="h5">Referensi rute pada nota muat</h3>
        <p class="text-muted">Satu rute dapat memiliki beberapa alias. Alias harus unik di seluruh rute; spasi, kapitalisasi, dan tanda baca diabaikan, sedangkan angka tetap dibedakan.</p>
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif
        @if ($routeChoices->isNotEmpty())
            @php($selectedAliasRoute = $routeChoices->firstWhere('id', (int) old('alias_rute_id')) ?? $routeChoices->first())
            <form method="POST" action="{{ route('rute.nota-alias.store', $selectedAliasRoute->id) }}" class="row g-3 align-items-end mb-4">
                @csrf
                <div class="col-md-5">
                    <label for="note-alias-route" class="form-label">Rute SAIND</label>
                    <select id="note-alias-route" name="alias_rute_id" class="form-select" required onchange="this.form.action = this.selectedOptions[0].dataset.action">
                        @foreach ($routeChoices as $routeChoice)
                            <option value="{{ $routeChoice->id }}" @selected($selectedAliasRoute->id === $routeChoice->id) data-action="{{ route('rute.nota-alias.store', $routeChoice->id) }}">{{ $routeChoice->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="note-alias-name" class="form-label">Alias pada nota</label>
                    <input id="note-alias-name" class="form-control" name="name" value="{{ old('name') }}" maxlength="191" placeholder="Contoh: STP CPP-02 - SDJ" required>
                </div>
                <div class="col-md-2"><button class="btn btn-primary" type="submit">Tambah alias</button></div>
            </form>
        @else
            <p>Tambahkan rute terlebih dahulu untuk mengatur alias nota.</p>
        @endif
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Alias pada nota</th><th>Rute SAIND</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($noteAliases as $noteAlias)
                        <tr>
                            <td>{{ $noteAlias->name }}</td><td>{{ $noteAlias->rute->nama }}</td>
                            <td><form method="POST" action="{{ route('rute.nota-alias.destroy', [$noteAlias->rute_id, $noteAlias->id]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus alias</button>
                            </form></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">Belum ada alias nota yang terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $noteAliases->links() }}
    </div>
</section>
