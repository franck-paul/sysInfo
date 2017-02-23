$(function() {

	var dotclearAjax = function(method, args, fn) {
		var content = null;
		var params = {
			xd_check: dotclear.nonce,
			f: method
		};
		$.extend(params, args);
		var promise = $.ajax({
  			url: 'services.php',
			data: params,
			dataType: 'xml'
		});
		promise.done(function(data) {
			if ($('rsp[status=failed]', data).length > 0) {
				// For debugging purpose only:
				// console.log($('rsp',data).attr('message'));
				console.log('Dotclear REST server error');
			} else {
				// ret -> status (true/false)
				// msg -> REST method return value
				var ret = Number($('rsp>sysinfo', data).attr('ret'));
				content = $('rsp>sysinfo', data).attr('msg');
				if (ret && fn !== undefined && $.isFunction(fn)) {
					// Call callback function with returned value
					fn(content);
				}
			}
		});
	}

	var getStaticCacheFilename = function(url, fn) {
		dotclearAjax('getStaticCacheName', { url: url }, fn);
	}

	var loadStaticCacheDirs = function(dir, fn) {
		dotclearAjax('getStaticCacheDir', { root: dir }, fn);
	}

	var loadStaticCacheList = function(dir, fn) {
		dotclearAjax('getStaticCacheList', { root: dir }, fn);
	}

	var loadServerFile = function(filename, type, fn) {
		switch (type) {
			case 'tpl':
				dotclearAjax('getCompiledTemplate', { file: filename }, fn);
				break;
			case 'sc':
				dotclearAjax('getStaticCacheFile', { file: filename }, fn);
				break;
		}
	}

	var viewSource = function(prefix, filename, content) {
		var src =
			'<div class="' + prefix + '_view">' +
			'<h1>' + filename + '</h1>' +
			'<textarea id="' + prefix + '_source">' + $.parseJSON(window.atob(content)) + '</textarea>' +
			'</div>';
		$.magnificPopup.open({
			items: {
				src: src,
				type: 'inline'
			},
			callbacks: {
				open: function() {
					if (dotclear.colorsyntax) {
						// Popup opened, format textarea with codemirror
						var options = {
							mode: 'text/html', // 'application/x-httpd-php',
							tabMode: 'indent',
							lineWrapping: "true",
							lineNumbers: "true",
							matchBrackets: "true",
							autoCloseBrackets: "true",
							readOnly: "true"
						};
						if (dotclear.colorsyntax_theme !== '') {
							options.theme = dotclear.colorsyntax_theme;
						}
						var textarea = document.getElementById(prefix + '_source');
						var editor = CodeMirror.fromTextArea(textarea, options);
					}
				}
			}
		});
	}

	// Compiled template preview
	$('a.tpl_compiled').click(function(e) {
		e.preventDefault();
		var template_file = $(e.target).text();
		// Open template file content in a modal iframe
		if (template_file !== undefined) {
			loadServerFile(template_file, 'tpl', function(content) {
				viewSource('tpl_compiled', template_file, content);
			});
		}
	});

	// Static cache dir expand (load 2nd level subdirs via Ajax)
	$('a.sc_dir').click(function(e) {
		e.preventDefault();
		var main_dir = $(e.target).text();
		loadStaticCacheDirs(main_dir, function(dirs) {
			// Insert list and remove previous raw
			var r = $(e.target).parent().parent();
			r.after(dirs).remove();
			// Static cache subdir expand (load 3rd level subdirs and cache file list via Ajax)
			$('a.sc_subdir').click(function(f) {
				f.preventDefault();
				var sub_dir = $(f.target).text();
				loadStaticCacheList(main_dir + '/' + sub_dir, function(list) {
					// Insert list and remove previous raw
					var s = $(f.target).parent().parent();
					s.after(list).remove();
					dotclear.condSubmit('#scform td input[type=checkbox]', '#scform #delscaction');
					// Static cache file preview
					$('a.sc_compiled').click(function(g) {
						g.preventDefault();
						var cache_file = $(g.target).attr('data-file');
						// Open static cache file content in a modal iframe
						if (cache_file !== undefined) {
							loadServerFile(cache_file, 'sc', function(content) {
								viewSource('sc_compiled', $(g.target).text(), content);
							});
						}
					});
				});
			});
		});
	});

	// Autosubmit on checklist change
	$('#checklist').change(function() {
		this.form.submit();
	});

	// Static cache calculator
	$('#getscaction').click(function(e) {
		e.preventDefault();
		$('#sccalc_res').text('');
		var url = $('#sccalc_url').val();
		if (url !== undefined && url !== '') {
			getStaticCacheFilename(url, function(res) {
				var text = String.fromCharCode(160) + res.slice(0,2) + ' / ' + res.slice(2,4) + ' / ' + res.slice(4,6) + ' / ' + res;
				$('#sccalc_res').text(text);
			});
		}
	})

	// Checkboxes helpers

	// Template cache files
	$('#tplform .checkboxes-helpers').each(function() {
		dotclear.checkboxesHelpers(this, undefined, '#tplform td input[type=checkbox]:enabled', '#tplform #deltplaction');
	});
	$('#tplform td input[type=checkbox]').enableShiftClick();
	dotclear.condSubmit('#tplform td input[type=checkbox]', '#tplform #deltplaction');
	$('form input[type=submit][name=deltplaction]').click(function() {
		return window.confirm(dotclear.msg.confirm_del_tpl);
	});

	// Static cache files
	$('#scform .checkboxes-helpers').each(function() {
		dotclear.checkboxesHelpers(this, undefined, '#scform td input[type=checkbox]:enabled', '#scform #delscaction');
	});
	$('#scform td input[type=checkbox]').enableShiftClick();
	dotclear.condSubmit('#scform td input[type=checkbox]', '#scform #delscaction');
	$('form input[type=submit][name=delscaction]').click(function() {
		return window.confirm(dotclear.msg.confirm_del_sc);
	});

});
