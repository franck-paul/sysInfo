<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$checklists = array(
	__('Compiled templates') => 'compil-tpl',
	__('PHP info') => 'php-info',
	__('URL handlers') => 'url-handler'
);

$checklist = '';
if (!empty($_POST))
{
	try
	{
		$checklist = isset($_POST['checklist']) ? $_POST['checklist'] : '';
		switch ($checklist) {

			case 'url-handler':
				break;

			case 'php-info':
				break;

			case 'compil-tpl':
				// Cope with cache file deletion
				// ...
				break;
				
			default:
				break;
		}
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

?>
<html>
<head>
	<title><?php echo __('System Information'); ?></title>
</head>

<body>
<?php
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('System Information').'</h2>';

if (!empty($_GET['chk'])) {
	echo '<p class="message">'.__('to infinity and beyond.').'</p>';
}

echo
'<form action="'.$p_url.'" method="post">';

echo
'<p class="field"><label for="checklist">'.__('Select a checklist:').' '.
form::combo('checklist',$checklists,$checklist).'</label>'.'</p>';

echo
'<p>'.$core->formNonce().'<input type="submit" value="'.__('Check').'" /></p>'.
'</form>';

// Display required information
echo '<fieldset>';
switch ($checklist) {
	
	case 'url-handler':
		// Récupération des types d'URL enregistrées
		$urls = $core->url->getTypes();

		// Tables des URLs non gérées par le menu
		//$excluded = array('rsd','xmlrpc','preview','trackback','feed','spamfeed','hamfeed','pagespreview','tag_feed');
		$excluded = array();

		echo '<table id="urls"><caption>'.__('List of known URLs').'</caption>';
		echo '<thead><tr><th scope="col">'.__('Type').'</th>'.
			'<th scope="col">'.__('base URL').'</th>'.
			'<th scope="col">'.__('Regular expression').'</th></tr></thead>';
		echo '<tbody>';
		echo '<tr>'.
		     '<td scope="row">'.'home'.'</td>'.
		     '<td>'.''.'</td>'.
		     '<td>'.'^$'.'</td>'.
		     '</tr>';
		foreach ($urls as $type => $param) {
		     if (!in_array($type,$excluded))
		     {
		               echo '<tr>'.
		               '<td scope="row">'.$type.'</td>'.
		               '<td>'.$param['url'].'</td>'.
		               '<td>'.$param['representation'].'</td>'.
		               '</tr>';
		     }
		}
		echo '</tbody>';
		echo '</table>';
		break;
	
	case 'php-info':
		ob_start();
		phpinfo(INFO_GENERAL + INFO_CONFIGURATION + INFO_MODULES + INFO_ENVIRONMENT + INFO_VARIABLES);
		$phpinfo = array('phpinfo' => array());
		if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s',ob_get_clean(),$matches,PREG_SET_ORDER))
		{
			foreach($matches as $match) {
				if(strlen($match[1])) {
					$phpinfo[$match[1]] = array();
				} elseif(isset($match[3])) {
					@$phpinfo[end(array_keys($phpinfo))][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
				} else {
					@$phpinfo[end(array_keys($phpinfo))][] = $match[2];
				}
			}
		}
		foreach($phpinfo as $name => $section) {
			echo "<h3>$name</h3>\n<table>\n";
			foreach($section as $key => $val) {
				if(is_array($val)) {
					echo "<tr><td>$key</td><td>$val[0]</td><td>$val[1]</td></tr>\n";
				} elseif(is_string($key)) {
					echo "<tr><td>$key</td><td>$val</td></tr>\n";
				} else {
					echo "<tr><td>$val</td></tr>\n";
				}
			}
			echo "</table>\n";
		}
		break;

	case 'compil-tpl':
		// Emulate public prepend
		$core->tpl = new dcTemplate(DC_TPL_CACHE,'$core->tpl',$core);
		$core->themes = new dcThemes($core);
		$core->themes->loadModules($core->blog->themes_path);
		if (!isset($__theme)) {
			$__theme = $core->blog->settings->system->theme;
		}
		if (!$core->themes->moduleExists($__theme)) {
			$__theme = $core->blog->settings->system->theme = 'default';
		}
		$__parent_theme = $core->themes->moduleInfo($__theme,'parent');
		if ($__parent_theme) {
			if (!$core->themes->moduleExists($__parent_theme)) {
				$__theme = $core->blog->settings->system->theme = 'default';
				$__parent_theme = null;
			}
		}
		$__theme_tpl_path = array(
			$core->blog->themes_path.'/'.$__theme.'/tpl'
		);
		if ($__parent_theme) {
			$__theme_tpl_path[] = $core->blog->themes_path.'/'.$__parent_theme.'/tpl';
		}
		$main_plugins_root = explode(':',DC_PLUGINS_ROOT);
		$core->tpl->setPath(
			$__theme_tpl_path,
			$main_plugins_root[0].'/../inc/public/default-templates',
			$core->tpl->getPath());
		
		// Get installation info
		$document_root = (!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');
		$cache_path = path::real(DC_TPL_CACHE);
		if (substr($cache_path,0,strlen($document_root)) == $document_root) {
			$cache_path = substr($cache_path,strlen($document_root));
		} elseif (substr($cache_path,0,strlen(DC_ROOT)) == DC_ROOT) {
			$cache_path = substr($cache_path,strlen(DC_ROOT));
		}
		$blog_host = $core->blog->host;
		if (substr($blog_host,-1) != '/') {
			$blog_host .= '/';
		}
		$blog_url = $core->blog->url;
		if (substr($blog_url,0,strlen($blog_host)) == $blog_host) {
			$blog_url = substr($blog_url,strlen($blog_host));
		}

		$paths = $core->tpl->getPath();
		/*
		echo '<p>'.__('List of template paths').'</p>'.'<ul>';
		foreach ($paths as $path) {
			echo '<li>'.$path.'<li>';
		}
		echo '</ul>';
		*/
		
		echo '<table id="chk-table-result">';
		echo '<caption>'.__('List of compiled templates in cache').' '.$cache_path.'/cbtpl'.'</caption>';
		echo '<thead>'.
			'<tr>'.
			'<th scope="col">'.__('Template path').'</th>'.
			'<th scope="col">'.__('Template file').'</th>'.
			'<th scope="col">'.__('Cache subpath').'</th>'.
			'<th scope="col">'.__('Cache file').'</th>'.
			'</tr>'.
			'</thead>';
		echo '<tbody>';

		// Template stack
		$stack = array();
		// Loop on template paths
		foreach ($paths as $path) {
			$sub_path = path::real($path,false);
			if (substr($sub_path,0,strlen($document_root)) == $document_root) {
				$sub_path = substr($sub_path,strlen($document_root));
				if (substr($sub_path,0,1) == '/') $sub_path = substr($sub_path,1);
			} elseif (substr($sub_path,0,strlen(DC_ROOT)) == DC_ROOT) {
				$sub_path = substr($sub_path,strlen(DC_ROOT));
				if (substr($sub_path,0,1) == '/') $sub_path = substr($sub_path,1);
			}
			$path_displayed = false;
			// Don't know exactly why but need to cope with inc/public/default-templates !
			$md5_path = (!strstr($path,'inc/public/default-templates') ? $path : path::real($path));
			$files = files::scandir($path);
			if (is_array($files)) {
				foreach ($files as $file) {
					if (preg_match('/^(.*)\.(html|xml|xsl)$/',$file,$matches)) {
						if (isset($matches[1])) {
							if (!in_array($file,$stack)) {
								$stack[] = $file;
								$cache_file = md5($md5_path.'/'.$file).'.php';
								$cache_subpath = sprintf('%s/%s',substr($cache_file,0,2),substr($cache_file,2,2));
								$cache_fullpath = path::real(DC_TPL_CACHE).'/cbtpl/'.$cache_subpath;
								$file_check = $cache_fullpath.'/'.$cache_file;
								$file_exists = file_exists($file_check);
								$file_url = http::getHost().$cache_path.'/cbtpl/'.$cache_subpath.'/'.$cache_file;
								echo '<tr>'.
									'<td>'.($path_displayed ? '' : $sub_path).'</td>'.
									'<td scope="row">'.$file.'</td>'.
									'<td>'.'<img src="images/'.($file_exists ? 'check-on.png' : 'check-off.png').'" /> '.$cache_subpath.'</td>'.
									'<td>'.$cache_file.'</td>'.
									'</tr>';
								$path_displayed = true;
							}
						}
					}
				}
			}
		}
		echo '</tbody></table>';
		break;

	default:
		echo '<p class="form-note">'.__('Live long and prosper.').'</p>';
		break;
}
echo '</fieldset>';

?>
</body>
</html>