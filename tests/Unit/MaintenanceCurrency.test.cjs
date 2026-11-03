const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const root = path.resolve(__dirname, '../..');

function checkout(paymentMethod = 'uj_ditahan', initialValue = '') {
    const listeners = new Map();
    const input = {
        value: initialValue, selectionStart: 0, selectionEnd: 0,
        addEventListener(name, listener) {
            const handlers = listeners.get(name) || [];
            handlers.push(listener);
            listeners.set(name, handlers);
        },
        setSelectionRange() {},
    };
    const canonical = {value: ''};
    const bankFields = {hidden: true, classList: {toggle() {}}, querySelectorAll: () => [input, canonical]};
    const payment = {value: paymentMethod};
    const selectors = {'[data-maintenance-money]': input, '[data-maintenance-money-value]': canonical, '[data-payment-method]': payment, '[data-bank-fields]': bankFields};
    const document = {
        activeElement: null,
        querySelector: (selector) => selectors[selector] || null,
        querySelectorAll: () => [],
        addEventListener(name, listener) { if (name === 'DOMContentLoaded') this.ready = listener; },
    };
    input.ownerDocument = document;
    const context = {document, navigator: {userAgent: 'Node'}, console, setTimeout, clearTimeout};
    context.window = context;
    context.jQuery = () => ({each() {}, on() {}});
    vm.createContext(context);
    vm.runInContext(fs.readFileSync(path.join(root, 'public/assets/js/cleave.min.js'), 'utf8'), context);
    vm.runInContext(fs.readFileSync(path.join(root, 'public/assets/js/filter-oli-maintenance.js'), 'utf8'), context);
    document.ready();
    return {
        input, canonical,
        type(digits) {
            for (const digit of digits) {
                input.value += digit;
                for (const handler of listeners.get('input') || []) handler({target: input, inputType: 'insertText'});
            }
        },
    };
}

for (const payment of ['kas_besar', 'uj_ditahan', 'uj_ditahan_ban']) {
    test(`${payment}: typing past thousands preserves whole rupiah`, () => {
        const page = checkout(payment);
        page.type('1000');
        assert.equal(page.input.value, '1.000');
        assert.equal(page.canonical.value, '1000');
        page.type('000');
        assert.equal(page.input.value, '1.000.000');
        assert.equal(page.canonical.value, '1000000');
    });
    test(`${payment}: restores an integer amount without losing its magnitude`, () => {
        const page = checkout(payment, '1250000');
        assert.equal(page.input.value, '1.250.000');
        assert.equal(page.canonical.value, '1250000');
        page.type('0');
        assert.equal(page.input.value, '12.500.000');
        assert.equal(page.canonical.value, '12500000');
    });
}
