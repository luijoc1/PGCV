(function () {
  'use strict';
  var instances = Object.create(null);
  var buttons = ['bold', 'italic', 'underline', 'strikethrough', '|', 'ul', 'ol',
    'paragraph', 'table', '|', 'undo', 'redo', 'eraser'];

  function init() {
    if (!window.Jodit) return; // Keep the ordinary textarea usable if loading fails.
    ['editor1', 'editor2'].forEach(function (id) {
      var textarea = document.getElementById(id);
      if (!textarea || instances[id]) return;
      var required = textarea.required;
      var editor = Jodit.make(textarea, {
        language: 'es', height: 260, minHeight: 180,
        placeholder: 'Escribe la descripción del producto',
        buttons: buttons, buttonsMD: buttons, buttonsSM: buttons, buttonsXS: buttons,
        toolbarAdaptive: false, toolbarSticky: false,
        showCharsCounter: false, showWordsCounter: false, showXPathInStatusbar: false,
        disablePlugins: ['image', 'video', 'file', 'file-browser', 'fullsize', 'source'],
        cleanHTML: {
          denyTags: 'script,style,iframe,object,embed,svg,math,template,noscript',
          removeEventAttributes: true, safeJavaScriptLink: true
        }
      });
      instances[id] = editor;
      textarea.required = false; // Validate the visible editor, not the hidden textarea.
      editor.editor.setAttribute('role', 'textbox');
      editor.editor.setAttribute('aria-label', 'Descripción del producto');
      editor.editor.setAttribute('aria-multiline', 'true');
      var error = document.createElement('p');
      error.id = id + '-error';
      error.className = 'product-editor-error';
      error.setAttribute('role', 'alert');
      error.hidden = true;
      editor.container.insertAdjacentElement('afterend', error);
      editor.editor.setAttribute('aria-describedby', error.id);
      editor.events.on('change', function () {
        textarea.value = editor.value;
        error.hidden = true;
        editor.editor.removeAttribute('aria-invalid');
      });
      if (textarea.form) {
        textarea.form.addEventListener('submit', function (event) {
          textarea.value = editor.value;
          var documentValue = new DOMParser().parseFromString(textarea.value, 'text/html');
          if (required && !documentValue.body.textContent.replace(/[\s\u200b]/g, '')) {
            event.preventDefault();
            error.textContent = 'Escribe la descripción del producto.';
            error.hidden = false;
            editor.editor.setAttribute('aria-invalid', 'true');
            editor.editor.focus();
          }
        });
        textarea.form.addEventListener('reset', function () {
          setTimeout(function () { editor.value = textarea.value; }, 0);
        });
      }
    });
  }

  window.PGCVProductEditors = {
    init: init,
    setValue: function (id, html) {
      var value = typeof html === 'string' ? html : '';
      var textarea = document.getElementById(id);
      if (instances[id]) instances[id].value = value;
      if (textarea) textarea.value = value;
    }
  };
}());
