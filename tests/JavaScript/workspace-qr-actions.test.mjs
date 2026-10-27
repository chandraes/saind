import { test } from 'node:test';
import assert from 'node:assert/strict';
import { shareWorkspaceQr, qrFilename } from '../../resources/js/workspace-qr-actions.js';

const file = new File(['test-png'], 'qr-hauling.png', { type: 'image/png' });
const connection = { file, name: 'Hauling', address: 'https://dev-saind.ds-groups.com' };

test('shares the PNG file and public connection information only', async () => {
    let shared;
    const browser = { canShare: ({ files }) => files[0] === file, share: async (data) => { shared = data; } };
    assert.equal(await shareWorkspaceQr(browser, connection), 'shared');
    assert.equal(shared.files[0], file);
    assert.equal(shared.title, 'Hubungkan DSGroup Hauling — Hauling');
    assert.match(shared.text, /https:\/\/dev-saind.ds-groups.com/);
});
test('falls back to sharing the website when files are unsupported', async () => {
    let shared;
    const browser = { canShare: () => false, share: async (data) => { shared = data; } };
    assert.equal(await shareWorkspaceQr(browser, connection), 'shared-link');
    assert.equal(shared.url, connection.address);
    assert.equal(shared.files, undefined);
});
test('copies the address when native sharing is unavailable', async () => {
    let copied;
    const browser = { clipboard: { writeText: async (text) => { copied = text; } } };
    assert.equal(await shareWorkspaceQr(browser, connection), 'copied');
    assert.equal(copied, connection.address);
});
test('cancelling share does not copy or retry without consent', async () => {
    let copied = false;
    const browser = { canShare: () => true, share: async () => { throw new DOMException('cancelled', 'AbortError'); }, clipboard: { writeText: async () => { copied = true; } } };
    assert.equal(await shareWorkspaceQr(browser, connection), 'cancelled');
    assert.equal(copied, false);
});
test('blocked share or clipboard provides download fallback without claiming success', async () => {
    assert.equal(await shareWorkspaceQr({ share: async () => { throw new Error('blocked'); } }, connection), 'unsupported');
    assert.equal(await shareWorkspaceQr({ clipboard: { writeText: async () => { throw new Error('denied'); } } }, connection), 'unsupported');
    assert.equal(await shareWorkspaceQr({}, connection), 'unsupported');
});
test('download name identifies the workspace host with a PNG extension', () => {
    assert.equal(qrFilename('https://dev-saind.ds-groups.com'), 'qr-dsgroup-hauling-dev-saind.ds-groups.com.png');
});
