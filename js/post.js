/*global jQuery, dotclear */
'use strict';

dotclear.ready(() => {
  // DOM ready and content loaded

  document.getElementById('edit-entry')?.addEventListener('onetabload', () => {
    const series_node = document.getElementById('series-edit');
    const id = document.getElementById('id');

    let meta_field = null;
    let meta_editor = null;

    if (series_node) {
      const post_id = id ? id.value : 0;
      if (!post_id) {
        meta_field = dotclear.htmlToNode('<input type="hidden" name="post_series">');
      }

      meta_editor = new dotclear.MetaEditor(series_node, meta_field, 'serie', dotclear.getData('editor_series_options'));
      meta_editor.displayMeta('serie', post_id, 'post_meta_serie_input');

      // mEdit object reference for toolBar
      dotclear.meta_editor_series = meta_editor;
    }

    const serie_input = document.getElementById('post_meta_serie_input');
    jQuery(serie_input).autocomplete(meta_editor.service_uri, {
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
  });

  const target = document.querySelector('h5 .s-series');
  if (target) {
    const siblings = document.querySelectorAll('.s-series:not(label)');
    if (siblings) {
      dotclear.toggleWithLegend(target, siblings, {
        user_pref: 'post_series',
        legend_click: true,
      });
    }
  }
});
