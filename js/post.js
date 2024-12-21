/*global $, dotclear, metaEditor */
'use strict';

dotclear.mergeDeep(dotclear.msg, dotclear.getData('editor_series_msg'));

dotclear.ready(() => {
  const series_edit = $('#series-edit');
  let post_id = $('#id');
  let meta_field = null;
  let mEdit = null;

  if (series_edit.length > 0) {
    post_id = post_id.length > 0 ? post_id.get(0).value : false;
    if (post_id === false) {
      meta_field = $('<input type="hidden" name="post_series">');
      meta_field.val($('#post_series').val());
    }

    const data = dotclear.getData('editor_series_options');

    mEdit = new metaEditor(series_edit, meta_field, 'serie', data);
    mEdit.displayMeta('serie', post_id, 'post_meta_serie_input');

    // mEdit object reference for toolBar
    window.dc_serie_editor = mEdit;
  }

  $('#post_meta_serie_input').autocomplete(mEdit.service_uri, {
    extraParams: {
      f: 'searchMetadata',
      metaType: 'serie',
      json: 1,
    },
    delay: 1000,
    multiple: true,
    multipleSeparator: ', ',
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
              percent: elt.roundpercent,
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

  $('h5 .s-series').toggleWithLegend($('.s-series').not('label'), {
    user_pref: 'post_series',
    legend_click: true,
  });
});
