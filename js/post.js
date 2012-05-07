$(function() {
	$('#edit-entry').onetabload(function() {
		var series_edit = $('#series-edit');
		var post_id = $('#id');
		var meta_field = null;
		
		if (series_edit.length > 0) {
			post_id = (post_id.length > 0) ? post_id.get(0).value : false;
			if (post_id == false) {
				meta_field = $('<input type="hidden" name="post_series" />');
				meta_field.val($('#post_series').val());
			}
			var mEdit = new metaEditor(series_edit,meta_field,'serie');
			mEdit.displayMeta('serie',post_id);
			
			// mEdit object reference for toolBar
			window.dc_serie_editor = mEdit;
		}
		
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
});

// Toolbar button for series
jsToolBar.prototype.elements.serieSpace = {type: 'space'};

jsToolBar.prototype.elements.serie = {type: 'button', title: 'Keyword', fn:{} };
jsToolBar.prototype.elements.serie.context = 'post';
jsToolBar.prototype.elements.serie.icon = 'index.php?pf=series/img/serie-add.png';
jsToolBar.prototype.elements.serie.fn.wiki = function() {
	this.encloseSelection('','',function(str) {
		if (str == '') { window.alert(dotclear.msg.no_selection); return ''; }
		if (str.indexOf(',') != -1) {
			return str;
		} else {
			window.dc_serie_editor.addMeta(str);
			return '['+str+'|serie:'+str+']';
		}
	});
};
jsToolBar.prototype.elements.serie.fn.xhtml = function() {
	var url = this.elements.serie.url;
	this.encloseSelection('','',function(str) {
		if (str == '') { window.alert(dotclear.msg.no_selection); return ''; }
		if (str.indexOf(',') != -1) {
			return str;
		} else {
			window.dc_serie_editor.addMeta(str);
			return '<a href="'+this.stripBaseURL(url+'/'+str)+'">'+str+'</a>';
		}
	});
};
jsToolBar.prototype.elements.serie.fn.wysiwyg = function() {
	var t = this.getSelectedText();
	
	if (t == '') { window.alert(dotclear.msg.no_selection); return; }
	if (t.indexOf(',') != -1) { return; }
	
	var n = this.getSelectedNode();
	var a = document.createElement('a');
	a.href = this.stripBaseURL(this.elements.serie.url+'/'+t);
	a.appendChild(n);
	this.insertNode(a);
	window.dc_serie_editor.addMeta(t);
};