$(function() {

	function htmlspecialchars_decode (string, quoteStyle) {
		// from: http://locutus.io/php/strings/htmlspecialchars_decode/
		var optTemp = 0;
		var i = 0;
		var noquotes = false;

		if (typeof quoteStyle === 'undefined') {
			quoteStyle = 2;
		}
		string = string.toString()
			.replace(/&lt;/g, '<')
			.replace(/&gt;/g, '>');
		var OPTS = {
			'ENT_NOQUOTES': 0,
			'ENT_HTML_QUOTE_SINGLE': 1,
			'ENT_HTML_QUOTE_DOUBLE': 2,
			'ENT_COMPAT': 2,
			'ENT_QUOTES': 3,
			'ENT_IGNORE': 4
		};
		if (quoteStyle === 0) {
			noquotes = true;
		}
		if (typeof quoteStyle !== 'number') {
			// Allow for a single string or an array of string flags
			quoteStyle = [].concat(quoteStyle);
			for (i = 0; i < quoteStyle.length; i++) {
				// Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
				if (OPTS[quoteStyle[i]] === 0) {
					noquotes = true;
				} else if (OPTS[quoteStyle[i]]) {
					optTemp = optTemp | OPTS[quoteStyle[i]];
				}
			}
			quoteStyle = optTemp;
		}
		if (quoteStyle & OPTS.ENT_HTML_QUOTE_SINGLE) {
			// PHP doesn't currently escape if more than one 0, but it should:
			string = string.replace(/&#0*39;/g, "'");
			// This would also be useful here, but not a part of PHP:
			// string = string.replace(/&apos;|&#x0*27;/g, "'");
		}
		if (!noquotes) {
			string = string.replace(/&quot;/g, '"');
		}
		// Put this in last place to avoid escape being double-decoded
		string = string.replace(/&amp;/g, '&');

		return string;
	}

	function loadTemplateFile(template_file) {
		var content = null;
		var params = {
			f: 'getCompiledTemplate',
			xd_check: dotclear.nonce,
			file: template_file
		};
		$.ajaxSetup({async: false,timeout: 3000,cache: false});
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
//debugger;
		var template_file = $(e.target).text();
		// Open template file content in a modal iframe
		if (template_file !== undefined) {
			var content = loadTemplateFile(template_file);
			if (content !== undefined && content !== null) {
				var src =
					'<div class="tpl_compiled_view" style="width: auto; background: #fff;">' +
					'<h1 style="font-weight: bold; width: initial; text-indent: 1em;">' +
						template_file +
					'</h1>' +
					'<textarea id="tpl_compiled_source" style="resize: none; width: calc(100% - 2em); height: 90vh; margin: 1em">' +
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
								var editor = CodeMirror.fromTextArea(document.getElementById('tpl_compiled_source'),options);
							}
						}
					}
				});
			}
		}
	});

});
