/*global $, CKEDITOR, dotclear */
'use strict';

Object.assign(dotclear.msg, dotclear.getData('ck_editor_series'));

(() => {
  CKEDITOR.plugins.add('dcseries', {
    init(editor) {
      editor.addCommand('dcSeriesCommand', {
        exec(editor) {
          if (editor.getSelection().getNative().toString().replace(/\s*/, '') != '') {
            const str = editor.getSelection().getNative().toString().replace(/\s*/, '');
            const url = dotclear.msg.serie_url;
            window.dc_serie_editor.addMeta(str);
            const link = `<a href="${$.stripBaseURL(`${url}/${str}`)}">${str}</a>`;
            const element = CKEDITOR.dom.element.createFromHtml(link);
            editor.insertElement(element);
          }
        },
      });

      editor.ui.addButton('dcSeries', {
        label: dotclear.msg.serie_title,
        command: 'dcSeriesCommand',
        toolbar: 'insert',
        icon: `${this.path}serie.png`,
      });
    },
  });
})();
