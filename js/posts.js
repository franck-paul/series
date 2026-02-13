/*global $, dotclear */
'use strict';

dotclear.mergeDeep(dotclear.msg, dotclear.getData('posts_series_msg'));

dotclear.ready(() => {
  $('#serie_delete').on('submit', () => globalThis.confirm(dotclear.msg.confirm_serie_delete));
});
