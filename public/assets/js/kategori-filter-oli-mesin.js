document.addEventListener('DOMContentLoaded', async () => {
    const reopenModal = () => {
        document.querySelectorAll('[data-reopen-modal]').forEach((element) => {
            document.querySelector(`[data-bs-target="#${element.id}"]`)?.click();
        });
    };

    document.querySelectorAll('[data-category-form]').forEach((form) => {
        let confirming = false;
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (confirming) return;
            confirming = true;
            try {
                if (!form.checkValidity()) {
                    const invalid = form.querySelector(':invalid');
                    await Swal.fire({
                        icon: 'error', title: 'Periksa kembali isian',
                        text: invalid?.name === 'nama'
                            ? 'Nama kategori wajib diisi, maksimal 255 karakter.'
                            : 'Limit ritase harus berupa bilangan bulat antara 1 dan 4294967295 rit.',
                        confirmButtonText: 'Perbaiki',
                    });
                    invalid?.focus();
                    return;
                }
                const result = await Swal.fire({
                    icon: 'question', title: 'Simpan kategori?',
                    text: `${form.elements.nama.value} dengan limit ${form.elements.limit_ritase.value} rit.`,
                    showCancelButton: true, confirmButtonText: 'Ya, simpan',
                    cancelButtonText: 'Batal', confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#64748b', focusCancel: true,
                });
                if (result.isConfirmed) {
                    form.querySelector('[type="submit"]').disabled = true;
                    Swal.fire({
                        title: 'Menyimpan kategori…', allowOutsideClick: false,
                        allowEscapeKey: false, didOpen: () => Swal.showLoading(),
                    });
                    HTMLFormElement.prototype.submit.call(form);
                }
            } finally {
                confirming = false;
            }
        });
    });

    document.querySelectorAll('.modal').forEach((element) => {
        element.addEventListener('shown.bs.modal', () => {
            element.querySelector('.is-invalid, input:not([type="hidden"])')?.focus();
        });
    });

    const notification = document.querySelector('[data-category-notification]');
    if (notification) {
        await Swal.fire({
            icon: notification.dataset.icon,
            title: notification.dataset.icon === 'success' ? 'Berhasil!' : 'Gagal menyimpan',
            text: notification.dataset.message,
            confirmButtonText: 'OK', confirmButtonColor: '#2563eb',
        });
    }
    reopenModal();
});
