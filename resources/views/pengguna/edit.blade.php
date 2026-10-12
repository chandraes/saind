<div class="row g-3">
    <div class="col-md-6">
        <label for="user-name" class="form-label fw-bold">Nama <span class="text-danger">*</span></label>
        <input id="user-name" name="name" class="form-control" required minlength="3" maxlength="255" autocomplete="name" placeholder="Nama lengkap">
        <div class="invalid-feedback" data-error="name"></div>
    </div>
    <div class="col-md-6">
        <label for="user-username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
        <input id="user-username" name="username" class="form-control" required maxlength="255" autocomplete="off" placeholder="Username untuk login">
        <div class="invalid-feedback" data-error="username"></div>
    </div>
    <div class="col-12">
        <label for="user-role" class="form-label fw-bold">Role <span class="text-danger">*</span></label>
        <select id="user-role" name="role" class="form-select" required>
            @foreach ($roles as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
        </select>
        <div class="invalid-feedback" data-error="role"></div>
    </div>
    @foreach (['vendor' => 'Vendor', 'customer' => 'Customer'] as $type => $label)
        <div class="col-12" id="user-{{ $type }}-group" hidden>
            <label for="user-{{ $type }}" class="form-label fw-bold">{{ $label }} <span class="text-danger">*</span></label>
            <select id="user-{{ $type }}" name="{{ $type }}_id" class="form-select" disabled><option value=""></option></select>
            <div class="invalid-feedback" data-error="{{ $type }}_id"></div>
        </div>
    @endforeach
    <div class="col-md-6">
        <label for="user-email" class="form-label fw-bold">Email <span class="text-muted fw-normal small">(opsional)</span></label>
        <input type="email" id="user-email" name="email" class="form-control" maxlength="255" autocomplete="email" placeholder="nama@contoh.com">
        <div class="invalid-feedback" data-error="email"></div>
    </div>
    <div class="col-md-6">
        <label for="user-password" class="form-label fw-bold">Password</label>
        <div class="input-group">
            <input type="password" id="user-password" name="password" class="form-control" minlength="6" maxlength="255" autocomplete="new-password" aria-describedby="user-password-help" required>
            <button type="button" id="user-password-toggle" class="btn btn-outline-secondary" aria-label="Tampilkan password"><i class="fa fa-eye" aria-hidden="true"></i></button>
        </div>
        <div class="form-text" id="user-password-help">Gunakan minimal 6 karakter.</div>
        <div class="invalid-feedback" data-error="password"></div>
    </div>
</div>
