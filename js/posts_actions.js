$(function() {
	var serie_field = $('#new_series');

	serie_field.after('<div id="series_list"></div>');
	serie_field.hide();

	var target = $('#series_list');
	var mEdit = new metaEditor(target,serie_field,'serie',editor_series_options);
	mEdit.meta_url = 'plugin.php?p=series&m=serie_posts&amp;serie=';

	mEdit.meta_dialog = $('<input type="text" />');
	mEdit.meta_dialog.attr('title',mEdit.text_add_meta.replace(/%s/,mEdit.meta_type));
	mEdit.meta_dialog.attr('id','post_meta_input');
	mEdit.meta_dialog.css('width','90%');

	mEdit.addMetaDialog();

	$('input[name="save_series"]').click(function() {
		serie_field.val($('#post_meta_input').val());
	});

	$('#post_meta_input').autocomplete(mEdit.service_uri, {
		extraParams: {
			'f': 'searchMeta',
			'metaType': 'serie'
		},
		delay: 1000,
		multiple: true,
		matchSubset: false,
		matchContains: true,
		parse: function(xml) {
			var results = [];
			$(xml).find('meta').each(function(){
				results[results.length] = {
					data: {
						"id": $(this).text(),
						"count": $(this).attr("count"),
						"percent":  $(this).attr("roundpercent")
					},
					result: $(this).text()
				};
			});
			return results;
		},
		formatItem: function(serie) {
			return serie.id + ' <em>(' +
			dotclear.msg.series_autocomplete.
				replace('%p',serie.percent).
				replace('%e',serie.count + ' ' +
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
