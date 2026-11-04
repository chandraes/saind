const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.resolve(__dirname, '../../public/assets/js/load-note-review.js'), 'utf8');

function screen({confirmed = true, fail = false} = {}) {
    let ready, submit;
    const calls = {fetch: 0, dialogs: [], redirects: [], reloads: 0};
    const button = {disabled: false};
    const form = {
        dataset: {confirm: 'Proses nota?', confirmDetails: 'Unit 288; Rp 350.000; rekening 123.', successUrl: '/transaksi/nota-bongkar'},
        action: '/billing/nota-muat/1/process',
        closest: () => null, checkValidity: () => true, querySelector: () => null,
        hasAttribute: name => name === 'data-note-process',
        addEventListener: (_, callback) => { submit = callback; },
    };
    const document = {
        body: {querySelectorAll: () => [button]},
        addEventListener: (_, callback) => { ready = callback; },
        getElementById: () => null,
        querySelectorAll: selector => selector === '[data-note-review]' ? [form] : [],
    };
    vm.runInNewContext(source, {
        document, TypeError, SyntaxError, FormData: class {},
        Swal: {fire: async options => { calls.dialogs.push(options); return {isConfirmed: confirmed}; }, showLoading() {}},
        fetch: async () => { calls.fetch++; if (fail) throw new TypeError('Network unavailable'); return {ok: true, json: async () => ({message: 'Tersimpan'})}; },
        window: {location: {assign: url => calls.redirects.push(url), reload: () => { calls.reloads++; }}},
    });
    ready();
    return {calls, button, submit: () => submit({preventDefault() {}})};
}

test('cancelling the one cash confirmation sends no request', async () => {
    const page = screen({confirmed: false});
    await page.submit();
    assert.equal(page.calls.fetch, 0);
    assert.equal(page.calls.redirects.length, 0);
    assert.equal(page.button.disabled, false);
});

test('one confirmation processes once, blocks duplicate submit and goes to Nota Bongkar', async () => {
    const page = screen();
    await Promise.all([page.submit(), page.submit()]);
    assert.equal(page.calls.fetch, 1);
    assert.equal(page.calls.dialogs.filter(dialog => dialog.showCancelButton).length, 1);
    assert.equal(page.calls.dialogs[0].text, 'Unit 288; Rp 350.000; rekening 123.');
    assert.deepEqual(page.calls.redirects, ['/transaksi/nota-bongkar']);
    assert.equal(page.calls.reloads, 0);
});

test('uncertain network result stays on note with clear feedback and releases controls', async () => {
    const page = screen({fail: true});
    await page.submit();
    assert.equal(page.calls.redirects.length, 0);
    assert.equal(page.button.disabled, false);
    assert.equal(page.calls.dialogs.at(-1).icon, 'error');
    assert.match(page.calls.dialogs.at(-1).text, /Muat ulang nota/);
});
