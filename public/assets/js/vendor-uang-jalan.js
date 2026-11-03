document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-vendor-money]').forEach((input) => {
        const raw = document.getElementById(input.dataset.rawField);
        const formatter = new Cleave(input, {
            numeral: true, numeralThousandsGroupStyle: 'thousand', delimiter: '.',
            numeralDecimalMark: ',', numeralDecimalScale: 0, numeralPositiveOnly: true,
        });
        const sync = () => { raw.value = formatter.getRawValue(); };
        input.addEventListener('input', sync);
        sync();
    });
    const form = document.getElementById('formUangJalan');
    let submitting = false;
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (submitting) return;
        submitting = true;
        const result = await Swal.fire({
            icon: 'question', title: 'Simpan kesepakatan uang jalan?',
            text: 'Pastikan nominal uang jalan, UJ Ditahan Sparepart, dan UJ Ditahan Ban sudah sesuai.',
            showCancelButton: true, confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal', focusCancel: true,
        });
        if (!result.isConfirmed) { submitting = false; return; }
        form.querySelector('[type="submit"]').disabled = true;
        Swal.fire({title: 'Menyimpan…', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading()});
        HTMLFormElement.prototype.submit.call(form);
    });
});
