/*global $, dotclear */
'use strict';

dotclear.mergeDeep(dotclear.msg, dotclear.getData('posts_series_msg'));

$(function () {
  $('#serie_delete').on('submit', function () {
    return window.confirm(dotclear.msg.confirm_serie_delete);
  });
});
