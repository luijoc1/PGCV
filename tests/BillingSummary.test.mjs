import test from 'node:test';
import assert from 'node:assert/strict';
import { createRequire } from 'node:module';
const { formatTotal } = createRequire(import.meta.url)('../dist/js/billing-summary.js');

for (const [value, expected] of [
  [230000, '$ 230,000.00'], ['230000', '$ 230,000.00'],
  [0, '$ 0.00'], ['0.00', '$ 0.00'], [79.99, '$ 79.99'],
  [79.992, '$ 79.99'], [1234.567, '$ 1,234.57']
]) {
  test(`formats valid total ${JSON.stringify(value)}`, () => {
    assert.equal(formatTotal(value), expected);
  });
}

for (const [label, value] of [
  ['null', null], ['undefined', undefined], ['empty text', ''],
  ['whitespace', '  '], ['negative', -1], ['NaN', NaN],
  ['infinity', Infinity], ['negative infinity', -Infinity],
  ['partially numeric text', '230000oops'], ['currency text', '$ 230,000.00'],
  ['array', [230000]], ['object', {}], ['boolean', true]
]) {
  test(`rejects ${label} instead of displaying a misleading total`, () => {
    assert.equal(formatTotal(value), null);
  });
}
