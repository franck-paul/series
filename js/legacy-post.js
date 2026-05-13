/*global dotclear */
'use strict';

// Toolbar button for series
dotclear.ToolBar.prototype.elements.serie = {
  group: 'metadata',
  type: 'button',
  title: 'Serie',
  key: 's',
  shortkey_name: 'S',
  fn: {},
};

dotclear.mergeDeep(dotclear.ToolBar.prototype.elements, dotclear.getData('legacy_editor_series'));

dotclear.ToolBar.prototype.elements.serie.context = 'post';
dotclear.ToolBar.prototype.elements.serie.fn.wiki = function () {
  this.encloseSelection('', '', (str) => {
    if (str === '') {
      globalThis.alert(dotclear.msg.no_selection);
      return '';
    }
    if (str.includes(',')) {
      return str;
    }
    dotclear.meta_editor_series.addMeta(str);
    return `[${str}|serie:${str}]`;
  });
};
dotclear.ToolBar.prototype.elements.serie.fn.markdown = function () {
  const { url } = this.elements.serie;
  this.encloseSelection('', '', function (str) {
    if (str === '') {
      globalThis.alert(dotclear.msg.no_selection);
      return '';
    }
    if (str.includes(',')) {
      return str;
    }
    dotclear.meta_editor_series.addMeta(str);
    const uri = this.stripBaseURL(`${url}/${str}`);
    return `[${str}](${uri})`;
  });
};
dotclear.ToolBar.prototype.elements.serie.fn.xhtml = function () {
  const { url } = this.elements.serie;
  this.encloseSelection('', '', function (str) {
    if (str === '') {
      globalThis.alert(dotclear.msg.no_selection);
      return '';
    }
    if (str.includes(',')) {
      return str;
    }
    dotclear.meta_editor_series.addMeta(str);
    const uri = this.stripBaseURL(`${url}/${str}`);
    return `<a href="${uri}">${str}</a>`;
  });
};
dotclear.ToolBar.prototype.elements.serie.fn.wysiwyg = function () {
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
  dotclear.meta_editor_series.addMeta(t);
};
