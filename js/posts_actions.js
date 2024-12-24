/*global $, dotclear, metaEditor */
'use strict';

dotclear.ready(() => {
  const serie_field = $('#new_series');

  serie_field.after('<div id="series_list"></div>');
  serie_field.hide();

  const target = $('#series_list');
  const mEdit = new metaEditor(target, serie_field, 'serie', dotclear.getData('editor_series_options'));

  mEdit.meta_url = 'index.php?process=Plugin&p=series&m=serie_posts&amp;serie=';

  mEdit.meta_dialog = $('<input type="text">');
  mEdit.meta_dialog.attr('title', mEdit.text_add_meta.replace(/%s/, mEdit.meta_type));
  mEdit.meta_dialog.attr('id', 'post_meta_serie_input');
  mEdit.meta_dialog.css('width', '90%');

  mEdit.addMetaDialog();

  $('input[name="save_series"]').on('click', () => {
    serie_field.val($('#post_meta_serie_input').val());
  });

  $('#post_meta_serie_input').autocomplete(mEdit.service_uri, {
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
