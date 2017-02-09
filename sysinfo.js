$(function() {

	function loadTemplateFile(template_file) {
		var content = null;
		var params = {
			f: 'getCompiledTemplate',
			xd_check: dotclear.nonce,
			file: template_file
		};
		$.ajaxSetup({ async: false, timeout: 3000, cache: false });
		$.get('services.php', params, function(data) {
			if ($('rsp[status=failed]', data).length > 0) {
				// For debugging purpose only:
				// console.log($('rsp',data).attr('message'));
				console.log('Dotclear REST server error');
			} else {
				// ret -> status (true/false)
				// msg -> file content
				var ret = Number($('rsp>sysinfo', data).attr('ret'));
				content = $('rsp>sysinfo', data).attr('msg');
			}
		});
		return content;
	}

	function loadStaticCacheFile(cache_file) {
		var content = null;
		var params = {
			f: 'getStaticCacheFile',
			xd_check: dotclear.nonce,
			file: cache_file
		};
		$.ajaxSetup({ async: false, timeout: 3000, cache: false });
		$.get('services.php', params, function(data) {
			if ($('rsp[status=failed]', data).length > 0) {
				// For debugging purpose only:
				// console.log($('rsp',data).attr('message'));
				console.log('Dotclear REST server error');
			} else {
				// ret -> status (true/false)
				// msg -> file content
				var ret = Number($('rsp>sysinfo', data).attr('ret'));
				content = $('rsp>sysinfo', data).attr('msg');
			}
		});
		return content;
	}

	// Compiled template preview
	$('a.tpl_compiled').click(function(e) {
		e.preventDefault();
		var template_file = $(e.target).text();
		// Open template file content in a modal iframe
		if (template_file !== undefined) {
			var content = loadTemplateFile(template_file);
			if (content !== undefined && content !== null) {
				var src =
					'<div class="tpl_compiled_view">' +
					'<h1>' +
					template_file +
					'</h1>' +
					'<textarea id="tpl_compiled_source">' +
					window.atob(content) +
					'</textarea>' +
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
								var textarea = document.getElementById('tpl_compiled_source');
								var editor = CodeMirror.fromTextArea(textarea, options);
							}
						}
					}
				});
			}
		}
	});

	// Static cache file preview
	$('a.sc_compiled').click(function(e) {
		e.preventDefault();
		var cache_file = $(e.target).attr('data-file');
		// Open static cache file content in a modal iframe
		if (cache_file !== undefined) {
			var content = loadStaticCacheFile(cache_file);
			if (content !== undefined && content !== null) {
				var src =
					'<div class="sc_compiled_view">' +
					'<h1>' +
					cache_file +
					'</h1>' +
					'<textarea id="sc_compiled_source">' +
					window.atob(content) +
					'</textarea>' +
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
								var textarea = document.getElementById('sc_compiled_source');
								var editor = CodeMirror.fromTextArea(textarea, options);
							}
						}
					}
				});
			}
		}
	});

	// Autosubmit on checklist change
	$('#checklist').change(function() {
		this.form.submit();
	});

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
