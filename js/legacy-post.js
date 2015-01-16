// Toolbar button for series
jsToolBar.prototype.elements.serieSpace = {type: 'space',
	format: {
		wysiwyg: true,
		wiki: true,
		xhtml: true,
		markdown: true
	}
};

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
jsToolBar.prototype.elements.serie.fn.markdown = function() {
	var url = this.elements.serie.url;
	this.encloseSelection('','',function(str) {
		if (str == '') { window.alert(dotclear.msg.no_selection); return ''; }
		if (str.indexOf(',') != -1) {
			return str;
		} else {
			window.dc_serie_editor.addMeta(str);
			return '['+str+']('+this.stripBaseURL(url+'/'+str)+')';
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