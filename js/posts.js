/*global $, dotclear */
'use strict';

dotclear.mergeDeep(dotclear.msg, dotclear.getData('posts_series_msg'));

$(() => {
  $('#serie_delete').on('submit', () => window.confirm(dotclear.msg.confirm_serie_delete));
});
