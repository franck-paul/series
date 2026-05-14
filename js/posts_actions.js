/*global jQuery, dotclear */
'use strict';

dotclear.ready(() => {
  // DOM ready and content loaded

  const serie_field = document.getElementById('new_series');

  serie_field.after(dotclear.htmlToNode('<div id="series_list"></div>'));
  serie_field.style.display = 'none';

  const target = document.getElementById('series_list');
  const meta_editor = new dotclear.MetaEditor(target, serie_field, 'serie', dotclear.getData('editor_series_options'));

  meta_editor.meta_url = 'index.php?process=Plugin&p=series&m=serie_posts&amp;serie=';

  meta_editor.meta_dialog = dotclear.htmlToNode('<input type="text">');
  meta_editor.meta_dialog.setAttribute('title', meta_editor.text_add_meta.replace(/%s/, meta_editor.meta_type));
  meta_editor.meta_dialog.setAttribute('id', 'post_meta_serie_input');
  meta_editor.meta_dialog.style.width = '90%';

  meta_editor.addMetaDialog();

  const save_series = document.querySelector('input[name="save_series"]');
  save_series?.addEventListener('click', () => {
    const serie_input = document.getElementById('post_meta_serie_input');
    serie_field.value = serie_input?.value;
  });

  const serie_input = document.getElementById('post_meta_serie_input');
  $(serie_input).autocomplete(meta_editor.service_uri, {
    extraParams: {
      f: 'searchMetadata',
      metaType: 'serie',
      json: 1,
    },
    delay: 1000,
    multiple: true,
    matchSubset: false,
    matchContains: true,
    parse(data) {
      const results = [];
      if (data.success) {
        for (const elt of data.payload) {
          results[results.length] = {
            data: {
              id: elt.meta_id,
              count: elt.count,
            },
            result: elt.meta_id,
          };
        }
      }
      return results;
    },
    formatItem(serie) {
      return serie.id;
    },
    formatResult(serie) {
      return serie.result;
    },
  });
});
