<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2011 Franck Paul
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------

$checklists = array(
	__('Compiled templates') => 'templates',
	__('URL handlers') => 'urlhandlers',
	__('Behaviours') => 'behaviours',
	__('DC Constants') => 'constants',
	__('PHP info') => 'phpinfo'
);

$undefined = '<!-- undefined -->';
$constants = array(
	'DC_ADMIN_MAILFROM' => defined('DC_ADMIN_MAILFROM') ? DC_ADMIN_MAILFROM : $undefined,
	'DC_ADMIN_SSL' => defined('DC_ADMIN_SSL') ? (DC_ADMIN_SSL ? 'true' : 'false') : $undefined,
	'DC_ADMIN_URL' => defined('DC_ADMIN_URL') ? DC_ADMIN_URL : $undefined,
	'DC_AUTH_PAGE' => defined('DC_AUTH_PAGE') ? DC_AUTH_PAGE : $undefined,
	'DC_AUTH_SESS_ID' => defined('DC_AUTH_SESS_ID') ? DC_AUTH_SESS_ID : $undefined,
	'DC_AUTH_SESS_UID' => defined('DC_AUTH_SESS_UID') ? DC_AUTH_SESS_UID : $undefined,
	'DC_BACKUP_PATH' => defined('DC_BACKUP_PATH') ? DC_BACKUP_PATH : $undefined,
	'DC_BLOG_ID' => defined('DC_BLOG_ID') ? DC_BLOG_ID : $undefined,
	'DC_CONTEXT_ADMIN' => defined('DC_CONTEXT_ADMIN') ? DC_CONTEXT_ADMIN : $undefined,
/* (add a / at the beginning of this line to uncomment the following lines)
	'DC_DBDRIVER' => defined('DC_DBDRIVER') ? DC_DBDRIVER : $undefined,
	'DC_DBHOST' => defined('DC_DBHOST') ? DC_DBHOST : $undefined,
	'DC_DBNAME' => defined('DC_DBNAME') ? DC_DBNAME : $undefined,
	'DC_DBPASSWORD' => defined('DC_DBPASSWORD') ? DC_DBPASSWORD : $undefined,
	'DC_DBPREFIX' => defined('DC_DBPREFIX') ? DC_DBPREFIX : $undefined,
	'DC_DBUSER' => defined('DC_DBUSER') ? DC_DBUSER : $undefined,
//*/
	'DC_DEBUG' => defined('DC_DEBUG') ? (DC_DEBUG ? 'true' : 'false') : $undefined,
	'DC_DIGESTS' => defined('DC_DIGESTS') ? DC_DIGESTS : $undefined,
	'DC_FORCE_SCHEME_443' => defined('DC_FORCE_SCHEME_443') ? (DC_FORCE_SCHEME_443 ? 'true' : 'false') : $undefined,
	'DC_L10N_ROOT' => defined('DC_L10N_ROOT') ? DC_L10N_ROOT : $undefined,
	'DC_L10N_UPDATE_URL' => defined('DC_L10N_UPDATE_URL') ? DC_L10N_UPDATE_URL : $undefined,
	'DC_MASTER_KEY' => defined('DC_MASTER_KEY') ? '*********' /* DC_MASTER_KEY */ : $undefined,
	'DC_MAX_UPLOAD_SIZE' => defined('DC_MAX_UPLOAD_SIZE') ? DC_MAX_UPLOAD_SIZE : $undefined,
	'DC_PLUGINS_ROOT' => defined('DC_PLUGINS_ROOT') ? DC_PLUGINS_ROOT : $undefined,
	'DC_RC_PATH' => defined('DC_RC_PATH') ? DC_RC_PATH : $undefined,
	'DC_ROOT' => defined('DC_ROOT') ? DC_ROOT : $undefined,
	'DC_SESSION_NAME' => defined('DC_SESSION_NAME') ? DC_SESSION_NAME : $undefined,
	'DC_TPL_CACHE' => defined('DC_TPL_CACHE') ? DC_TPL_CACHE : $undefined,
	'DC_UPDATE_URL' => defined('DC_UPDATE_URL') ? DC_UPDATE_URL : $undefined,
	'DC_UPDATE_VERSION' => defined('DC_UPDATE_VERSION') ? DC_UPDATE_VERSION : $undefined,
	'DC_VENDOR_NAME' => defined('DC_VENDOR_NAME') ? DC_VENDOR_NAME : $undefined,
	'DC_VERSION' => defined('DC_VERSION') ? DC_VERSION : $undefined,
	'DC_XMLRPC_URL' => defined('DC_XMLRPC_URL') ? DC_XMLRPC_URL : $undefined,
	'CLEARBRICKS_VERSION'=> defined('CLEARBRICKS_VERSION') ? CLEARBRICKS_VERSION : $undefined
);

$checklist = !empty($_POST['checklist']) ? $_POST['checklist'] : '';

if (!empty($_POST['deltplaction'])) {
	// Cope with cache file deletion
	try {
		if (empty($_POST['tpl'])) {
			throw new Exception(__('No cache file selected'));
		}
		$root_cache = path::real(DC_TPL_CACHE).'/cbtpl/';
		foreach ($_POST['tpl'] as $k => $v)
		{
			$cache_file = $root_cache.sprintf('%s/%s',substr($v,0,2),substr($v,2,2)).'/'.$v;
			if (file_exists($cache_file)) {
				unlink($cache_file);
			}
		}
	} catch (Exception $e) {
		$checklist = 'templates';
		$core->error->add($e->getMessage());
	}
	if (!$core->error->flag()) {
		http::redirect($p_url.'&deltpl=1');
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

if (!empty($_GET['deltpl'])) {
	dcPage::message(__('Selected cache files have been deleted.'));
	$checklist = 'templates';
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
	
	case 'constants':
		// Affichage des constantes remarquables de Dotclear
		echo '<h3>'.__('Dotclear constants').'</h3>';
		echo '<dl>';
		foreach ($constants as $c => $v) {
			echo '<dt>'.'<img src="images/'.($v != $undefined ? 'check-on.png' : 'check-off.png').'" /> <code>'.$c.'</code></dt>';
			if ($v != $undefined) {
				echo '<dd>'.$v.'</dd>';
			}
		}
		echo '</dl>';
		break;

	case 'behaviours':
		// Affichage de la liste des behaviours inscrits
		echo '<h3>'.__('Behaviours list').'</h3>';
		echo '<ul>';
		$bl = $core->getBehaviors('');
		foreach ($bl as $b => $f) {
			echo '<li>'.$b.' : ';
			if (is_array($f)) {
				echo '<ul>';
				foreach ($f as $fi) {
					echo '<li><code>';
					if (is_array($fi)) {
						echo $fi[0].'::'.$fi[1].'()';
					} else {
						echo $fi.'()';
					}
					echo '</code></li>';
				}
				echo '</ul>';
			} else {
				echo $f.'()';
			}
			echo '</li>';
		}
		echo '</ul>';
		echo '<p>'.'<a id="sysinfo-preview" onclick="window.open(this.href);return false;" href="'.$core->blog->url.$core->url->getBase('sysinfo').'/'.'behaviours'.'">'.__('Display public behaviours').' ('.__('new window').')'.'</a>'.'</p>';
		break;
	
	case 'urlhandlers':
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
		               '<td><code>'.$param['representation'].'</code></td>'.
		               '</tr>';
		     }
		}
		echo '</tbody>';
		echo '</table>';
		break;
	
	case 'phpinfo':
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

	case 'templates':
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
		
		// Looking for default-templates in each plugin's dir
		$plugins = $core->plugins->getModules();
		foreach ($plugins as $k => $v) {
			$plugin_root = $core->plugins->moduleInfo($k,'root');
			if ($plugin_root) {
				$core->tpl->setPath($core->tpl->getPath(),$plugin_root.'/default-templates');
			}
		}
		
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

		echo
		'<form action="'.$p_url.'" method="post">';

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
			// Don't know exactly why but need to cope with */default-templates !
			$md5_path = (!strstr($path,'/default-templates') ? $path : path::real($path));
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
									'<td>'.'<label class="classic">'.
										form::checkbox(array('tpl[]'),$cache_file,false,'','',!($file_exists)).' '.
										$cache_file.'</label></td>'.
									'</tr>';
								$path_displayed = true;
							}
						}
					}
				}
			}
		}
		echo '</tbody></table>';
		echo
		'<p>'.$core->formNonce().'<input type="submit" class="delete" name="deltplaction" value="'.__('Delete selected cache files').'" '.
			'onclick="return window.confirm(\''.html::escapeJS(__('Are you sure you want to remove selected cache files?')).'\');"/></p>'.
		'</form>';
		break;

	default:
		if (rand(0,1)) {
			echo '<p class="form-note">'.__('Live long and prosper.').'</p>';
		} else {
			echo '<p class="form-note">'.__('To infinity and beyond.').'</p>';
		}
		break;
}
echo '</fieldset>';

?>
</body>
</html>