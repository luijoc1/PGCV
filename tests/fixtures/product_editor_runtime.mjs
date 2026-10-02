import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../dist/js/product-editor.js', import.meta.url), 'utf8');

// Contract doubles for the adapter, not a browser DOM or the Jodit library.
// Parsed text is supplied explicitly: these tests do not test HTML parsing.
function element() {
  return {
    attributes: {}, focused: false,
    setAttribute(name, value) { this.attributes[name] = value; },
    removeAttribute(name) { delete this.attributes[name]; },
    focus() { this.focused = true; }
  };
}

export function editorRuntime({ ids = ['editor1'], required = true, library = true } = {}) {
  const fields = {}, editors = {}, errors = {}, timers = [], parsedText = new Map();
  for (const id of ids) {
    const listeners = {};
    fields[id] = {
      required, value: '',
      form: {
        listeners,
        addEventListener(name, handler) {
          (listeners[name] ??= []).push(handler);
        }
      }
    };
  }
  let creations = 0;
  const Jodit = {
    make(textarea, options) {
      creations++;
      const id = Object.keys(fields).find(key => fields[key] === textarea);
      const handlers = {};
      const editor = {
        value: textarea.value, editor: element(), options,
        container: { insertAdjacentElement(position, error) { errors[id] = error; } },
        events: { on(name, handler) { handlers[name] = handler; } },
        change(value) { this.value = value; handlers.change(); }
      };
      editors[id] = editor;
      return editor;
    }
  };
  const window = library ? { Jodit } : {};
  const context = vm.createContext({
    window, Jodit,
    document: { getElementById: id => fields[id] ?? null, createElement: element },
    DOMParser: class {
      parseFromString(html, type) {
        if (type !== 'text/html' || !parsedText.has(html)) {
          throw new Error('Supply parsed text explicitly for this fixture');
        }
        return { body: { textContent: parsedText.get(html) } };
      }
    },
    setTimeout(callback) { timers.push(callback); }
  });
  vm.runInContext(source, context, { filename: 'dist/js/product-editor.js' });
  return {
    api: window.PGCVProductEditors, fields, editors, errors, parsedText,
    get creations() { return creations; },
    enableLibrary() { window.Jodit = Jodit; },
    flushTimers() { while (timers.length) timers.shift()(); },
    dispatch(id, name) {
      const event = { prevented: false, preventDefault() { this.prevented = true; } };
      for (const handler of fields[id].form.listeners[name] ?? []) handler(event);
      return event;
    }
  };
}
