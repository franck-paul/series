/*global dotclear, jsToolBar */
'use strict';

// Toolbar button for series
jsToolBar.prototype.elements.serie = {
  group: 'metadata',
  type: 'button',
  title: 'Serie',
  key: 's',
  shortkey_name: 'S',
  fn: {},
};

dotclear.mergeDeep(jsToolBar.prototype.elements, dotclear.getData('legacy_editor_series'));

jsToolBar.prototype.elements.serie.context = 'post';
jsToolBar.prototype.elements.serie.fn.wiki = function () {
  this.encloseSelection('', '', (str) => {
    if (str === '') {
      globalThis.alert(dotclear.msg.no_selection);
      return '';
    }
    if (str.includes(',')) {
      return str;
    }
    globalThis.dc_serie_editor.addMeta(str);
    return `[${str}|serie:${str}]`;
  });
};
jsToolBar.prototype.elements.serie.fn.markdown = function () {
  const { url } = this.elements.serie;
  this.encloseSelection('', '', function (str) {
    if (str === '') {
      globalThis.alert(dotclear.msg.no_selection);
      return '';
    }
    if (str.includes(',')) {
      return str;
    }
    globalThis.dc_serie_editor.addMeta(str);
    const uri = this.stripBaseURL(`${url}/${str}`);
    return `[${str}](${uri})`;
  });
};
jsToolBar.prototype.elements.serie.fn.xhtml = function () {
  const { url } = this.elements.serie;
  this.encloseSelection('', '', function (str) {
    if (str === '') {
      globalThis.alert(dotclear.msg.no_selection);
      return '';
    }
    if (str.includes(',')) {
      return str;
    }
    globalThis.dc_serie_editor.addMeta(str);
    const uri = this.stripBaseURL(`${url}/${str}`);
    return `<a href="${uri}">${str}</a>`;
  });
};
jsToolBar.prototype.elements.serie.fn.wysiwyg = function () {
  const t = this.getSelectedText();

  if (t === '') {
    globalThis.alert(dotclear.msg.no_selection);
    return;
  }
  if (t.includes(',')) {
    return;
  }

  const n = this.getSelectedNode();
  const a = document.createElement('a');
  a.href = this.stripBaseURL(`${this.elements.serie.url}/${t}`);
  a.appendChild(n);
  this.insertNode(a);
  globalThis.dc_serie_editor.addMeta(t);
};
