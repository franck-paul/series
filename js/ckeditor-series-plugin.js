/*global $, CKEDITOR, dotclear, getData */
'use strict';

Object.assign(dotclear.msg, getData('ck_editor_series'));

(function() {
  CKEDITOR.plugins.add('dcseries', {
    init: function(editor) {
      editor.addCommand('dcSeriesCommand', {
        exec: function(editor) {
          if (editor.getSelection().getNative().toString().replace(/\s*/, '') != '') {
            const str = editor.getSelection().getNative().toString().replace(/\s*/, '');
            const url = dotclear.msg.serie_url;
            window.dc_serie_editor.addMeta(str);
            const link = `<a href="${$.stripBaseURL(url + '/' + str)}">${str}</a>`;
            const element = CKEDITOR.dom.element.createFromHtml(link);
            editor.insertElement(element);
          }
        }
      });

      editor.ui.addButton('dcSeries', {
        label: dotclear.msg.serie_title,
        command: 'dcSeriesCommand',
        toolbar: 'insert',
        icon: this.path + 'serie.png'
      });
    }
  });
})();
