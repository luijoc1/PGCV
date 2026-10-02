import test from 'node:test';
import assert from 'node:assert/strict';
import { editorRuntime } from './fixtures/product_editor_runtime.mjs';

test('keeps the ordinary required textarea usable when Jodit is unavailable', () => {
  const run = editorRuntime({ library: false });
  run.fields.editor1.value = 'Original';
  run.api.init();
  assert.equal(run.creations, 0);
  assert.equal(run.fields.editor1.required, true);
  assert.equal(run.fields.editor1.value, 'Original');
  run.enableLibrary();
  run.api.init();
  assert.equal(run.creations, 1);
  assert.equal(run.editors.editor1.value, 'Original');
});

test('ignores absent fields and does not create duplicate editors or submit listeners', () => {
  const run = editorRuntime();
  run.api.init();
  run.api.init();
  assert.equal(run.creations, 1);
  assert.equal(run.fields.editor1.form.listeners.submit.length, 1);
  assert.equal(run.fields.editor1.form.listeners.reset.length, 1);
});

test('handles a page without product editor fields', () => {
  const run = editorRuntime({ ids: [] });
  assert.doesNotThrow(() => run.api.init());
  assert.equal(run.creations, 0);
});

test('gives the visible editor accessible validation attributes', () => {
  const run = editorRuntime();
  run.api.init();
  const attrs = run.editors.editor1.editor.attributes;
  assert.equal(run.fields.editor1.required, false);
  assert.equal(attrs.role, 'textbox');
  assert.equal(attrs['aria-label'], 'Descripción del producto');
  assert.equal(attrs['aria-multiline'], 'true');
  assert.equal(attrs['aria-describedby'], run.errors.editor1.id);
  assert.equal(run.errors.editor1.attributes.role, 'alert');
  assert.equal(run.errors.editor1.hidden, true);
});

test('supports an editor without a parent form', () => {
  const run = editorRuntime();
  run.fields.editor1.form = null;
  assert.doesNotThrow(() => run.api.init());
  run.editors.editor1.change('Description');
  assert.equal(run.fields.editor1.value, 'Description');
});

test('setValue works before initialization and updates both fields afterward', () => {
  const run = editorRuntime();
  run.api.setValue('editor1', '<p>Before</p>');
  run.api.init();
  assert.equal(run.editors.editor1.value, '<p>Before</p>');
  run.api.setValue('editor1', '<p>After</p>');
  assert.equal(run.editors.editor1.value, '<p>After</p>');
  assert.equal(run.fields.editor1.value, '<p>After</p>');
});

test('setValue clears unsupported values and safely ignores missing fields', () => {
  const run = editorRuntime();
  run.api.init();
  for (const value of [null, undefined, 12, {}, ['text']]) {
    run.api.setValue('editor1', value);
    assert.equal(run.fields.editor1.value, '');
    assert.equal(run.editors.editor1.value, '');
  }
  assert.doesNotThrow(() => run.api.setValue('missing', 'Text'));
});

for (const [label, html, text] of [
  ['empty content', '', ''],
  ['markup without text', '<p><br></p>', ''],
  ['whitespace and zero-width spaces', '<p>  </p>', ' \n\t\u00a0\u200b']
]) {
  test(`blocks required ${label} and focuses the visible editor`, () => {
    const run = editorRuntime();
    run.api.init();
    run.editors.editor1.value = html;
    run.parsedText.set(html, text);
    assert.equal(run.dispatch('editor1', 'submit').prevented, true);
    assert.equal(run.fields.editor1.value, html);
    assert.equal(run.errors.editor1.hidden, false);
    assert.equal(run.errors.editor1.textContent, 'Escribe la descripción del producto.');
    assert.equal(run.editors.editor1.editor.attributes['aria-invalid'], 'true');
    assert.equal(run.editors.editor1.editor.focused, true);
  });
}

test('submits the latest editor content instead of stale textarea content', () => {
  const run = editorRuntime();
  run.api.init();
  run.fields.editor1.value = 'Stale';
  run.editors.editor1.value = '<p>Motor</p>';
  run.parsedText.set('<p>Motor</p>', 'Motor');
  assert.equal(run.dispatch('editor1', 'submit').prevented, false);
  assert.equal(run.fields.editor1.value, '<p>Motor</p>');
});

test('allows an empty optional description', () => {
  const run = editorRuntime({ required: false });
  run.api.init();
  run.parsedText.set('', '');
  assert.equal(run.dispatch('editor1', 'submit').prevented, false);
  assert.equal(run.errors.editor1.hidden, true);
});

test('editing clears the previous validation error and synchronizes the textarea', () => {
  const run = editorRuntime();
  run.api.init();
  run.parsedText.set('', '');
  run.dispatch('editor1', 'submit');
  run.editors.editor1.change('<p>Updated</p>');
  assert.equal(run.fields.editor1.value, '<p>Updated</p>');
  assert.equal(run.errors.editor1.hidden, true);
  assert.equal(run.editors.editor1.editor.attributes['aria-invalid'], undefined);
});

test('waits for the native form reset before copying the restored value', () => {
  const run = editorRuntime();
  run.api.init();
  run.editors.editor1.change('Modified');
  run.dispatch('editor1', 'reset');
  assert.equal(run.editors.editor1.value, 'Modified');
  run.fields.editor1.value = 'Default';
  run.flushTimers();
  assert.equal(run.editors.editor1.value, 'Default');
});

test('keeps add and edit forms independent', () => {
  const run = editorRuntime({ ids: ['editor1', 'editor2'] });
  run.api.init();
  run.editors.editor1.change('Add description');
  run.api.setValue('editor2', 'Edit description');
  assert.equal(run.creations, 2);
  assert.equal(run.fields.editor1.value, 'Add description');
  assert.equal(run.fields.editor2.value, 'Edit description');
  assert.notEqual(run.errors.editor1.id, run.errors.editor2.id);
});
