(function() {
	CKEDITOR.plugins.add('dcseries', {
		init: function(editor) {
			editor.addCommand('dcSeriesCommand', {
				exec: function(editor) {
					if (editor.getSelection().getNative().toString().replace(/\s*/,'')!='') {
						var str = editor.getSelection().getNative().toString().replace(/\s*/,'');
						var url = jsToolBar.prototype.elements.serie.url;
						window.dc_serie_editor.addMeta(str);
						var link = '<a href="'+$.stripBaseURL(url+'/'+str)+'">'+str+'</a>';
						var element = CKEDITOR.dom.element.createFromHtml(link);
						editor.insertElement(element);
					}
				}
			});

			editor.ui.addButton('dcSeries', {
				label: jsToolBar.prototype.elements.serie.title,
				command: 'dcSeriesCommand',
				toolbar: 'insert',
				icon: this.path + 'serie.png'
			});
		}
	});
})();
