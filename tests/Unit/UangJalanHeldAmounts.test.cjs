const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.resolve(__dirname, '../../resources/views/billing/kas-uang-jalan/keluar.blade.php'), 'utf8');
const calculate = source.match(/function updateUangJalanKotor\(\) \{[\s\S]*?\n        \}/)[0];

for (const [transfer, expected] of [[300000, '370.000'], [350000, '420.000']]) {
    test(`display includes both funds for transfer ${transfer}`, () => {
        let result;
        const context = {
            nominal: {getRawValue: () => String(transfer)},
            $: (selector) => {
                if (selector === '#status_uj_ditahan') return {val: () => '1'};
                if (selector === '#rute_id') return {find: () => ({attr: (name) => name === 'data-nominal-ban' ? '20000' : '50000'})};
                if (selector === '#uang_jalan_kotor') return {val: (value) => { result = value; }};
                throw new Error(`Unexpected selector ${selector}`);
            },
        };
        vm.runInNewContext(calculate + '\nupdateUangJalanKotor();', context);
        assert.equal(result, expected);
    });
}
