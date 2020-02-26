/*global $, dotclear, mergeDeep, getData */
'use strict';

mergeDeep(dotclear.msg, getData('posts_series_msg'));

$(function() {
  $('#serie_delete').on('submit', function() {
    return window.confirm(dotclear.msg.confirm_serie_delete);
  });
});
