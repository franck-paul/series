/*global $, dotclear, metaEditor, mergeDeep, getData */
'use strict';

mergeDeep(dotclear.msg, getData('editor_series_msg'));

$(function() {
  $('#edit-entry').onetabload(function() {
    const series_edit = $('#series-edit');
    let post_id = $('#id');
    let meta_field = null;
    let mEdit = null;

    if (series_edit.length > 0) {
      post_id = (post_id.length > 0) ? post_id.get(0).value : false;
      if (post_id == false) {
        meta_field = $('<input type="hidden" name="post_series" />');
        meta_field.val($('#post_series').val());
      }
      mEdit = new metaEditor(series_edit, meta_field, 'serie', getData('editor_series_options'));
      mEdit.meta_url = 'plugin.php?p=series&m=serie_posts&amp;serie=';
      mEdit.displayMeta('serie', post_id, 'post_meta_serie_input');

      // mEdit object reference for toolBar
      window.dc_serie_editor = mEdit;
    }

    $('#post_meta_serie_input').autocomplete(mEdit.service_uri, {
      extraParams: {
        'f': 'searchMeta',
        'metaType': 'serie'
      },
      delay: 1000,
      multiple: true,
      multipleSeparator: ', ',
      matchSubset: false,
      matchContains: true,
      parse: function(xml) {
        let results = [];
        $(xml).find('meta').each(function() {
          results[results.length] = {
            data: {
              'id': $(this).text(),
              'count': $(this).attr('count'),
              'percent': $(this).attr('roundpercent')
            },
            result: $(this).text()
          };
        });
        return results;
      },
      formatItem: function(serie) {
        return serie.id + ' <em>(' +
          dotclear.msg.series_autocomplete.
        replace('%p', serie.percent).
        replace('%e', serie.count + ' ' +
            (serie.count > 1 ?
              dotclear.msg.entries :
              dotclear.msg.entry)
          ) +
          ')</em>';
      },
      formatResult: function(serie) {
        return serie.result;
      }
    });
  });

  $('h5 .s-series').toggleWithLegend($('.s-series').not('label'), {
    user_pref: 'post_series',
    legend_click: true
  });

});
