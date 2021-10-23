<?php
/**
 * @brief sysInfo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

$checklists = [
    __('Compiled templates')         => 'templates',
    __('Plugins repository (cache)') => 'dcrepo-plugins-cache',
    __('Plugins repository')         => 'dcrepo-plugins',
    __('Themes repository (cache)')  => 'dcrepo-themes-cache',
    __('Themes repository')          => 'dcrepo-themes',
    __('Template paths')             => 'tplpaths',
    __('URL handlers')               => 'urlhandlers',
    __('Behaviours')                 => 'behaviours',
    __('DC Constants')               => 'constants',
    __('Admin URLs')                 => 'adminurls',
    __('Editors and Syntaxes')       => 'formaters',
    __('Plugins')                    => 'plugins',
    __('REST methods')               => 'rest',
    __('PHP info')                   => 'phpinfo'
];

if ($core->plugins->moduleExists('staticCache')) {
    if (defined('DC_SC_CACHE_ENABLE') && DC_SC_CACHE_ENABLE) {
        if (defined('DC_SC_CACHE_DIR')) {
            if (dcStaticCacheControl::cacheCurrentBlog()) {
                $checklists[__('Static cache')] = 'sc';
            }
        }
    }
}

$undefined = '<!-- undefined -->';
$constants = [
    'DC_ADMIN_CONTEXT'        => defined('DC_ADMIN_CONTEXT') ? (DC_ADMIN_CONTEXT ? 'true' : 'false') : $undefined,
    'DC_ADMIN_MAILFROM'       => defined('DC_ADMIN_MAILFROM') ? DC_ADMIN_MAILFROM : $undefined,
    'DC_ADMIN_SSL'            => defined('DC_ADMIN_SSL') ? (DC_ADMIN_SSL ? 'true' : 'false') : $undefined,
    'DC_ADMIN_URL'            => defined('DC_ADMIN_URL') ? DC_ADMIN_URL : $undefined,
    'DC_AKISMET_SUPER'        => defined('DC_AKISMET_SUPER') ? (DC_AKISMET_SUPER ? 'true' : 'false') : $undefined,
    'DC_ALLOW_MULTI_MODULES'  => defined('DC_ALLOW_MULTI_MODULES') ? (DC_ALLOW_MULTI_MODULES ? 'true' : 'false') : $undefined,
    'DC_ANTISPAM_CONF_SUPER'  => defined('DC_ANTISPAM_CONF_SUPER') ? (DC_ANTISPAM_CONF_SUPER ? 'true' : 'false') : $undefined,
    'DC_AUTH_PAGE'            => defined('DC_AUTH_PAGE') ? DC_AUTH_PAGE : $undefined,
    'DC_AUTH_SESS_ID'         => defined('DC_AUTH_SESS_ID') ? DC_AUTH_SESS_ID : $undefined,
    'DC_AUTH_SESS_UID'        => defined('DC_AUTH_SESS_UID') ? DC_AUTH_SESS_UID : $undefined,
    'DC_BACKUP_PATH'          => defined('DC_BACKUP_PATH') ? DC_BACKUP_PATH : $undefined,
    'DC_BLOG_ID'              => defined('DC_BLOG_ID') ? DC_BLOG_ID : $undefined,
    'DC_CONTEXT_ADMIN'        => defined('DC_CONTEXT_ADMIN') ? (DC_CONTEXT_ADMIN ? 'true' : 'false') : $undefined,
    'DC_CONTEXT_MODULE'       => defined('DC_CONTEXT_MODULE') ? (DC_CONTEXT_MODULE ? 'true' : 'false') : $undefined,
    'DC_CRYPT_ALGO'           => defined('DC_CRYPT_ALGO') ? DC_CRYPT_ALGO : $undefined,
    'DC_DBDRIVER'             => defined('DC_DBDRIVER') ? DC_DBDRIVER : $undefined,
    'DC_DBHOST'               => defined('DC_DBHOST') ? DC_DBHOST : $undefined,
    'DC_DBNAME'               => defined('DC_DBNAME') ? DC_DBNAME : $undefined,
    'DC_DBPASSWORD'           => defined('DC_DBPASSWORD') ? '********* ' . __('(see inc/config.php)') /* DC_DBPASSWORD */ : $undefined,
    'DC_DBPREFIX'             => defined('DC_DBPREFIX') ? DC_DBPREFIX : $undefined,
    'DC_DBUSER'               => defined('DC_DBUSER') ? DC_DBUSER : $undefined,
    'DC_DEBUG'                => defined('DC_DEBUG') ? (DC_DEBUG ? 'true' : 'false') : $undefined,
    'DC_DEFAULT_JQUERY'       => defined('DC_DEFAULT_JQUERY') ? DC_DEFAULT_JQUERY : $undefined,
    'DC_DEFAULT_TPLSET'       => defined('DC_DEFAULT_TPLSET') ? DC_DEFAULT_TPLSET : $undefined,
    'DC_DEV'                  => defined('DC_DEV') ? (DC_DEV ? 'true' : 'false') : $undefined,
    'DC_DIGESTS'              => defined('DC_DIGESTS') ? DC_DIGESTS : $undefined,
    'DC_DISTRIB_PLUGINS'      => defined('DC_DISTRIB_PLUGINS') ? DC_DISTRIB_PLUGINS : $undefined,
    'DC_DISTRIB_THEMES'       => defined('DC_DISTRIB_THEMES') ? DC_DISTRIB_THEMES : $undefined,
    'DC_DNSBL_SUPER'          => defined('DC_DNSBL_SUPER') ? (DC_DNSBL_SUPER ? 'true' : 'false') : $undefined,
    'DC_FAIRTRACKBACKS_FORCE' => defined('DC_FAIRTRACKBACKS_FORCE') ? (DC_FAIRTRACKBACKS_FORCE ? 'true' : 'false') : $undefined,
    'DC_FORCE_SCHEME_443'     => defined('DC_FORCE_SCHEME_443') ? (DC_FORCE_SCHEME_443 ? 'true' : 'false') : $undefined,
    'DC_L10N_ROOT'            => defined('DC_L10N_ROOT') ? DC_L10N_ROOT : $undefined,
    'DC_L10N_UPDATE_URL'      => defined('DC_L10N_UPDATE_URL') ? DC_L10N_UPDATE_URL : $undefined,
    'DC_MASTER_KEY'           => defined('DC_MASTER_KEY') ? '********* ' . __('(see inc/config.php)') /* DC_MASTER_KEY */ : $undefined,
    'DC_MAX_UPLOAD_SIZE'      => defined('DC_MAX_UPLOAD_SIZE') ? DC_MAX_UPLOAD_SIZE : $undefined,
    'DC_NEXT_REQUIRED_PHP'    => defined('DC_NEXT_REQUIRED_PHP') ? DC_NEXT_REQUIRED_PHP : $undefined,
    'DC_NOT_UPDATE'           => defined('DC_NOT_UPDATE') ? (DC_NOT_UPDATE ? 'true' : 'false') : $undefined,
    'DC_PLUGINS_ROOT'         => defined('DC_PLUGINS_ROOT') ? DC_PLUGINS_ROOT : $undefined,
    'DC_RC_PATH'              => defined('DC_RC_PATH') ? DC_RC_PATH : $undefined,
    'DC_ROOT'                 => defined('DC_ROOT') ? DC_ROOT : $undefined,
    'DC_SESSION_NAME'         => defined('DC_SESSION_NAME') ? DC_SESSION_NAME : $undefined,
    'DC_SESSION_TTL'          => defined('DC_SESSION_TTL') ? (is_null(DC_SESSION_TTL) ? 'null' : DC_SESSION_TTL) : $undefined,
    'DC_SHOW_HIDDEN_DIRS'     => defined('DC_SHOW_HIDDEN_DIRS') ? (DC_SHOW_HIDDEN_DIRS ? 'true' : 'false') : $undefined,
    'DC_START_TIME'           => defined('DC_START_TIME') ? DC_START_TIME : $undefined,
    'DC_TPL_CACHE'            => defined('DC_TPL_CACHE') ? DC_TPL_CACHE : $undefined,
    'DC_UPDATE_URL'           => defined('DC_UPDATE_URL') ? DC_UPDATE_URL : $undefined,
    'DC_UPDATE_VERSION'       => defined('DC_UPDATE_VERSION') ? DC_UPDATE_VERSION : $undefined,
    'DC_VAR'                  => defined('DC_VAR') ? DC_VAR : $undefined,
    'DC_VENDOR_NAME'          => defined('DC_VENDOR_NAME') ? DC_VENDOR_NAME : $undefined,
    'DC_VERSION'              => defined('DC_VERSION') ? DC_VERSION : $undefined,
    'DC_XMLRPC_URL'           => defined('DC_XMLRPC_URL') ? DC_XMLRPC_URL : $undefined,
    'CLEARBRICKS_VERSION'     => defined('CLEARBRICKS_VERSION') ? CLEARBRICKS_VERSION : $undefined
];

if ($core->plugins->moduleExists('staticCache')) {
    $constants['DC_SC_CACHE_ENABLE']    = defined('DC_SC_CACHE_ENABLE') ? (DC_SC_CACHE_ENABLE ? 'true' : 'false') : $undefined;
    $constants['DC_SC_CACHE_DIR']       = defined('DC_SC_CACHE_DIR') ? DC_SC_CACHE_DIR : $undefined;
    $constants['DC_SC_CACHE_BLOGS_ON']  = defined('DC_SC_CACHE_BLOGS_ON') ? DC_SC_CACHE_BLOGS_ON : $undefined;
    $constants['DC_SC_CACHE_BLOGS_OFF'] = defined('DC_SC_CACHE_BLOGS_OFF') ? DC_SC_CACHE_BLOGS_OFF : $undefined;
    $constants['DC_SC_EXCLUDED_URL']    = defined('DC_SC_EXCLUDED_URL') ? DC_SC_EXCLUDED_URL : $undefined;
}

$publicPrepend = function () {
    // Emulate public prepend
    global $core;

    $core->tpl    = new dcTemplate(DC_TPL_CACHE, '$core->tpl', $core);
    $core->themes = new dcThemes($core);
    $core->themes->loadModules($core->blog->themes_path);
    if (!isset($__theme)) {
        $__theme = $core->blog->settings->system->theme;
    }
    if (!$core->themes->moduleExists($__theme)) {
        $__theme = $core->blog->settings->system->theme = 'default';
    }
    $tplset         = $core->themes->moduleInfo($__theme, 'tplset');
    $__parent_theme = $core->themes->moduleInfo($__theme, 'parent');
    if ($__parent_theme) {
        if (!$core->themes->moduleExists($__parent_theme)) {
            $__theme        = $core->blog->settings->system->theme        = 'default';
            $__parent_theme = null;
        }
    }
    $__theme_tpl_path = [
        $core->blog->themes_path . '/' . $__theme . '/tpl'
    ];
    if ($__parent_theme) {
        $__theme_tpl_path[] = $core->blog->themes_path . '/' . $__parent_theme . '/tpl';
        if (empty($tplset)) {
            $tplset = $core->themes->moduleInfo($__parent_theme, 'tplset');
        }
    }
    if (empty($tplset)) {
        $tplset = DC_DEFAULT_TPLSET;
    }
    $main_plugins_root = explode(':', DC_PLUGINS_ROOT);
    $core->tpl->setPath(
        $__theme_tpl_path,
        $main_plugins_root[0] . '/../inc/public/default-templates/' . $tplset,
        $core->tpl->getPath());

    // Looking for default-templates in each plugin's dir
    $plugins = $core->plugins->getModules();
    foreach ($plugins as $k => $v) {
        $plugin_root = $core->plugins->moduleInfo($k, 'root');
        if ($plugin_root) {
            $core->tpl->setPath($core->tpl->getPath(), $plugin_root . '/default-templates/' . $tplset);
            // To be exhaustive add also direct directory (without templateset)
            $core->tpl->setPath($core->tpl->getPath(), $plugin_root . '/default-templates');
        }
    }

    return $tplset;
};

$checklist = !empty($_POST['checklist']) ? $_POST['checklist'] : '';

if (!empty($_POST['deltplaction'])) {
    // Cope with cache file deletion
    try {
        if (empty($_POST['tpl'])) {
            throw new Exception(__('No cache file selected'));
        }
        $root_cache = path::real(DC_TPL_CACHE) . '/cbtpl/';
        foreach ($_POST['tpl'] as $k => $v) {
            $cache_file = $root_cache . sprintf('%s/%s', substr($v, 0, 2), substr($v, 2, 2)) . '/' . $v;
            if (file_exists($cache_file)) {
                unlink($cache_file);
            }
        }
    } catch (Exception $e) {
        $checklist = 'templates';
        $core->error->add($e->getMessage());
    }
    if (!$core->error->flag()) {
        dcPage::addSuccessNotice(__('Selected cache files have been deleted.'));
        http::redirect($p_url . '&tpl=1');
    }
}

if (!empty($_POST['delscaction'])) {
    // Cope with static cache file deletion
    try {
        if (empty($_POST['sc'])) {
            throw new Exception(__('No cache file selected'));
        }
        foreach ($_POST['sc'] as $k => $cache_file) {
            if (file_exists($cache_file)) {
                unlink($cache_file);
            }
        }
    } catch (Exception $e) {
        $checklist = 'sc';
        $core->error->add($e->getMessage());
    }
    if (!$core->error->flag()) {
        dcPage::addSuccessNotice(__('Selected cache files have been deleted.'));
        http::redirect($p_url . '&sc=1');
    }
}

# Get interface setting
$core->auth->user_prefs->addWorkspace('interface');
$user_ui_colorsyntax       = $core->auth->user_prefs->interface->colorsyntax;
$user_ui_colorsyntax_theme = $core->auth->user_prefs->interface->colorsyntax_theme;

?>
<html>
<head>
  <title><?php echo __('System Information'); ?></title>
<?php
echo
dcPage::cssLoad(urldecode(dcPage::getPF('sysInfo/sysinfo.css')), 'screen', $core->getVersion('sysInfo')) .
dcPage::jsJson('sysinfo', [
    'colorsyntax'       => $user_ui_colorsyntax,
    'colorsyntax_theme' => $user_ui_colorsyntax_theme,
    'msg'               => [
        'confirm_del_tpl' => __('Are you sure you want to remove selected template cache files?'),
        'confirm_del_sc'  => __('Are you sure you want to remove selected static cache files?'),
        'tpl_not_found'   => __('Compiled template file not found or unreadable'),
        'sc_not_found'    => __('Static cache file not found or unreadable')
    ]
]) .
dcPage::jsModal() .
dcPage::jsLoad(urldecode(dcPage::getPF('sysInfo/sysinfo.js')), $core->getVersion('sysInfo'));
if ($user_ui_colorsyntax) {
    echo
    dcPage::jsLoadCodeMirror($user_ui_colorsyntax_theme);
}
?>
</head>

<body>
<?php
echo
dcPage::breadcrumb(
    [
        __('System')             => '',
        __('System Information') => ''
    ]);
echo dcPage::notices();

if (!empty($_GET['tpl'])) {
    $checklist = 'templates';
}

if (!empty($_GET['sc'])) {
    $checklist = 'sc';
}

echo
    '<form action="' . $p_url . '" method="post">';

echo
'<p class="field"><label for="checklist">' . __('Select a checklist:') . '</label> ' .
form::combo('checklist', $checklists, $checklist) . ' ' .
$core->formNonce() . '<input type="submit" value="' . __('Check') . '" /></p>' .
    '</form>';

// Display required information
switch ($checklist) {

    case 'rest':
        $methods = $core->rest->functions;

        echo '<table id="chk-table-result" class="sysinfo">';
        echo '<caption>' . __('REST methods') . '</caption>';
        echo '<thead>' .
        '<tr>' .
        '<th scope="col" class="nowrap">' . __('Method') . '</th>' .
        '<th scope="col" class="maximal">' . __('Callback') . '</th>' .
            '</tr>' .
            '</thead>';
        echo '<tbody>';
        foreach ($methods as $method => $callback) {
            echo '<tr><td class="nowrap">' . $method . '</td><td class="maximal"><code>';
            if (is_array($callback)) {
                if (count($callback) > 1) {
                    echo $callback[0] . '::' . $callback[1];
                } else {
                    echo $callback[0];
                }
            } else {
                echo $callback;
            }
            echo '()</code></td></tr>';
        }
        echo '</tbody></table>';

        break;

    case 'plugins':
        // Affichage de la liste des plugins (et de leurs propriétés)
        $plugins = $core->plugins->getModules();

        echo '<table id="chk-table-result" class="sysinfo">';
        echo '<caption>' . __('Plugins (in loading order)') . '</caption>';
        echo '<thead>' .
        '<tr>' .
        '<th scope="col" class="nowrap">' . __('Plugin id') . '</th>' .
        '<th scope="col" class="maximal">' . __('Properties') . '</th>' .
            '</tr>' .
            '</thead>';
        echo '<tbody>';
        foreach ($plugins as $id => $m) {
            echo '<tr><td class="nowrap">' . $id . '</td><td class="maximal">';
            echo '<pre class="sysinfo">' . print_r($m, true) . '</pre></td></tr>';
        }
        echo '</tbody></table>';

        break;

    case 'formaters':
        // Affichage de la liste des éditeurs et des syntaxes par éditeur
        $formaters = $core->getFormaters();

        echo '<table id="chk-table-result" class="sysinfo">';
        echo '<caption>' . __('Editors and their supported syntaxes') . '</caption>';
        echo '<thead>' .
        '<tr>' .
        '<th scope="col" class="nowrap">' . __('Editor') . '</th>' .
        '<th scope="col" class="maximal">' . __('Syntax') . '</th>' .
            '</tr>' .
            '</thead>';
        echo '<tbody>';
        foreach ($formaters as $e => $s) {
            echo '<tr><td class="nowrap">' . $e . '</td>';
            $newline = false;
            if (is_array($s)) {
                foreach ($s as $f) {
                    echo($newline ? '</tr><tr><td></td>' : '') . '<td class="maximal">';
                    echo $f;
                    echo '</td>';
                    $newline = true;
                }
            }
            echo '</tr>';
        }
        echo '</tbody></table>';

        break;

    case 'constants':
        // Affichage des constantes remarquables de Dotclear
        echo '<table id="chk-table-result" class="sysinfo">';
        echo '<caption>' . __('Dotclear constants') . '</caption>';
        echo '<thead>' .
        '<tr>' .
        '<th scope="col" class="nowrap">' . __('Constant') . '</th>' .
        '<th scope="col" class="maximal">' . __('Value') . '</th>' .
            '</tr>' .
            '</thead>';
        echo '<tbody>';
        foreach ($constants as $c => $v) {
            echo '<tr><td class="nowrap">' .
                '<img src="images/' . ($v != $undefined ? 'check-on.png' : 'check-off.png') . '" /> <code>' . $c . '</code></td>' .
                '<td class="maximal">';
            if ($v != $undefined) {
                echo $v;
            }
            echo '</td></tr>';
        }
        echo '</tbody></table>';

        break;

    case 'behaviours':
        // Affichage de la liste des behaviours inscrits
        $bl = $core->getBehaviors('');

        echo '<table id="chk-table-result" class="sysinfo">';
        echo '<caption>' . __('Behaviours list') . '</caption>';
        echo '<thead>' .
        '<tr>' .
        '<th scope="col" class="nowrap">' . __('Behavior') . '</th>' .
        '<th scope="col" class="maximal">' . __('Callback') . '</th>' .
            '</tr>' .
            '</thead>';
        echo '<tbody>';
        foreach ($bl as $b => $f) {
            echo '<tr><td class="nowrap">' . $b . '</td>';
            $newline = false;
            if (is_array($f)) {
                foreach ($f as $fi) {
                    echo($newline ? '</tr><tr><td></td>' : '') . '<td class="maximal"><code>';
                    if (is_array($fi)) {
                        if (is_object($fi[0])) {
                            echo get_class($fi[0]) . '-&gt;' . $fi[1];
                        } else {
                            echo $fi[0] . '::' . $fi[1];
                        }
                    } else {
                        echo $fi . '()';
                    }
                    echo '()</code></td>';
                    $newline = true;
                }
            } else {
                echo '<td><code>' . $f . '()</code></td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';

        echo '<p><a id="sysinfo-preview" href="' . $core->blog->url . $core->url->getURLFor('sysinfo') . '/behaviours' . '">' . __('Display public behaviours') . '</a></p>';

        break;

    case 'urlhandlers':
        // Récupération des types d'URL enregistrées
        $urls = $core->url->getTypes();

        // Tables des URLs non gérées par le menu
        //$excluded = ['rsd','xmlrpc','preview','trackback','feed','spamfeed','hamfeed','pagespreview','tag_feed'];
        $excluded = [];

        echo '<table id="urls" class="sysinfo"><caption>' . __('List of known URLs') . '</caption>';
        echo '<thead><tr><th scope="col">' . __('Type') . '</th>' .
        '<th scope="col">' . __('base URL') . '</th>' .
        '<th scope="col" class="maximal">' . __('Regular expression') . '</th></tr></thead>';
        echo '<tbody>';
        echo '<tr>' .
            '<td scope="row">' . 'home' . '</td>' .
            '<td>' . '' . '</td>' .
            '<td class="maximal"><code>' . '^$' . '</code></td>' .
            '</tr>';
        foreach ($urls as $type => $param) {
            if (!in_array($type, $excluded)) {
                echo '<tr>' .
                    '<td scope="row">' . $type . '</td>' .
                    '<td>' . $param['url'] . '</td>' .
                    '<td class="maximal"><code>' . $param['representation'] . '</code></td>' .
                    '</tr>';
            }
        }
        echo '</tbody>';
        echo '</table>';

        break;

    case 'adminurls':
        // Récupération de la liste des URLs d'admin enregistrées
        $urls = $core->adminurl->dumpUrls();

        echo '<table id="urls" class="sysinfo"><caption>' . __('Admin registered URLs') . '</caption>';
        echo '<thead><tr><th scope="col" class="nowrap">' . __('Name') . '</th>' .
        '<th scope="col">' . __('URL') . '</th>' .
        '<th scope="col" class="maximal">' . __('Query string') . '</th></tr></thead>';
        echo '<tbody>';
        foreach ($urls as $name => $url) {
            echo '<tr>' .
            '<td scope="row" class="nowrap">' . $name . '</td>' .
            '<td><code>' . $url['url'] . '</code></td>' .
            '<td class="maximal"><code>' . http_build_query($url['qs']) . '</code></td>' .
                '</tr>';
        }
        echo '</tbody>';
        echo '</table>';

        break;

    case 'phpinfo':
        ob_start();
        phpinfo(INFO_GENERAL + INFO_CONFIGURATION + INFO_MODULES + INFO_ENVIRONMENT + INFO_VARIABLES);
        $phpinfo = ['phpinfo' => []];
        if (preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (strlen($match[1])) {
                    $phpinfo[$match[1]] = [];
                } elseif (isset($match[3])) {
                    @$phpinfo[end(array_keys($phpinfo))][$match[2]] = isset($match[4]) ? [$match[3], $match[4]] : $match[3];
                } else {
                    @$phpinfo[end(array_keys($phpinfo))][] = $match[2];
                }
            }
        }
        foreach ($phpinfo as $name => $section) {
            echo "<h3>$name</h3>\n<table class=\"sysinfo\">\n";
            foreach ($section as $key => $val) {
                if (is_array($val)) {
                    echo "<tr><td>$key</td><td>$val[0]</td><td>$val[1]</td></tr>\n";
                } elseif (is_string($key)) {
                    echo "<tr><td>$key</td><td>$val</td></tr>\n";
                } else {
                    echo "<tr><td>$val</td></tr>\n";
                }
            }
            echo "</table>\n";
        }

        break;

    case 'templates':
        $tplset = $publicPrepend();

        // Get installation info
        $document_root = (!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');
        $cache_path    = path::real(DC_TPL_CACHE);
        if (substr($cache_path, 0, strlen($document_root)) == $document_root) {
            $cache_path = substr($cache_path, strlen($document_root));
        } elseif (substr($cache_path, 0, strlen(DC_ROOT)) == DC_ROOT) {
            $cache_path = substr($cache_path, strlen(DC_ROOT));
        }
        $blog_host = $core->blog->host;
        if (substr($blog_host, -1) != '/') {
            $blog_host .= '/';
        }
        $blog_url = $core->blog->url;
        if (substr($blog_url, 0, strlen($blog_host)) == $blog_host) {
            $blog_url = substr($blog_url, strlen($blog_host));
        }

        $paths = $core->tpl->getPath();

        echo
            '<form action="' . $p_url . '" method="post" id="tplform">';

        echo '<table id="chk-table-result" class="sysinfo">';
        echo '<caption>' . __('List of compiled templates in cache') . ' ' . $cache_path . '/cbtpl' . '</caption>';
        echo '<thead>' .
        '<tr>' .
        '<th scope="col">' . __('Template path') . '</th>' .
        '<th scope="col" class="nowrap">' . __('Template file') . '</th>' .
        '<th scope="col" class="nowrap">' . __('Cache subpath') . '</th>' .
        '<th scope="col" class="nowrap">' . __('Cache file') . '</th>' .
            '</tr>' .
            '</thead>';
        echo '<tbody>';

        // Template stack
        $stack = [];
        // Loop on template paths
        foreach ($paths as $path) {
            $sub_path = path::real($path, false);
            if (substr($sub_path, 0, strlen($document_root)) == $document_root) {
                $sub_path = substr($sub_path, strlen($document_root));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            } elseif (substr($sub_path, 0, strlen(DC_ROOT)) == DC_ROOT) {
                $sub_path = substr($sub_path, strlen(DC_ROOT));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            }
            $path_displayed = false;
            // Don't know exactly why but need to cope with */default-templates !
            $md5_path = (!strstr($path, '/default-templates/' . $tplset) ? $path : path::real($path));
            $files    = files::scandir($path);
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (preg_match('/^(.*)\.(html|xml|xsl)$/', $file, $matches)) {
                        if (isset($matches[1])) {
                            if (!in_array($file, $stack)) {
                                $stack[]        = $file;
                                $cache_file     = md5($md5_path . '/' . $file) . '.php';
                                $cache_subpath  = sprintf('%s/%s', substr($cache_file, 0, 2), substr($cache_file, 2, 2));
                                $cache_fullpath = path::real(DC_TPL_CACHE) . '/cbtpl/' . $cache_subpath;
                                $file_check     = $cache_fullpath . '/' . $cache_file;
                                $file_exists    = file_exists($file_check);
                                $file_url       = http::getHost() . $cache_path . '/cbtpl/' . $cache_subpath . '/' . $cache_file;
                                echo '<tr>' .
                                '<td>' . ($path_displayed ? '' : $sub_path) . '</td>' .
                                '<td scope="row" class="nowrap">' . $file . '</td>' .
                                '<td class="nowrap">' . '<img src="images/' . ($file_exists ? 'check-on.png' : 'check-off.png') . '" /> ' . $cache_subpath . '</td>' .
                                '<td class="nowrap">' .
                                form::checkbox(['tpl[]'], $cache_file, false,
                                    ($file_exists) ? 'tpl_compiled' : '', '', !($file_exists)) . ' ' .
                                    '<label class="classic">' .
                                    ($file_exists ? '<a class="tpl_compiled" href="' . '#' . '">' : '') .
                                    $cache_file .
                                    ($file_exists ? '</a>' : '') .
                                    '</label></td>' .
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
        '<div class="two-cols">' .
        '<p class="col checkboxes-helpers"></p>' .
        '<p class="col right">' . $core->formNonce() . '<input type="submit" class="delete" id="deltplaction" name="deltplaction" value="' . __('Delete selected cache files') . '" /></p>' .
            '</div>' .
            '</form>';

        break;

    case 'tplpaths':
        $tplset        = $publicPrepend();
        $paths         = $core->tpl->getPath();
        $document_root = (!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');

        echo '<table id="chk-table-result" class="sysinfo">';
        echo '<caption>' . __('List of template paths') . '</caption>';
        echo '<thead>' .
        '<tr>' .
        '<th scope="col">' . __('Path') . '</th>' .
            '</tr>' .
            '</thead>';
        echo '<tbody>';
        foreach ($paths as $path) {
            $sub_path = path::real($path, false);
            if (substr($sub_path, 0, strlen($document_root)) == $document_root) {
                $sub_path = substr($sub_path, strlen($document_root));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            } elseif (substr($sub_path, 0, strlen(DC_ROOT)) == DC_ROOT) {
                $sub_path = substr($sub_path, strlen(DC_ROOT));
                if (substr($sub_path, 0, 1) == '/') {
                    $sub_path = substr($sub_path, 1);
                }
            }
            echo '<tr><td>' . $sub_path . '</td><tr>';
        }
        echo '</tbody></table>';

        echo '<p><a id="sysinfo-preview" href="' . $core->blog->url . $core->url->getURLFor('sysinfo') . '/templatetags' . '">' . __('Display template tags') . '</a></p>';

        break;

    case 'sc':

        $blog_host = $core->blog->host;
        if (substr($blog_host, -1) != '/') {
            $blog_host .= '/';
        }
        $cache_dir = path::real(DC_SC_CACHE_DIR, false);
        $cache_key = md5(http::getHostFromURL($blog_host));
        $cache     = new dcStaticCache(DC_SC_CACHE_DIR, $cache_key);

        if (!is_dir($cache_dir)) {
            break;
        }
        if (!is_readable($cache_dir)) {
            break;
        }
        $k          = str_split($cache_key, 2);
        $cache_root = $cache_dir;
        $cache_dir  = sprintf('%s/%s/%s/%s/%s', $cache_dir, $k[0], $k[1], $k[2], $cache_key);

        // Add a static cache URL convertor
        echo
        '<p class="fieldset">' .
        '<label for="sccalc_url" class="classic">' . __('URL:') . '</label>' . ' ' .
        form::field('sccalc_url', 50, 255, html::escapeHTML($core->blog->url)) . ' ' .
        '<input type="button" id="getscaction" name="getscaction" value="' . __(' → ') . '" />' .
            ' <span id="sccalc_res"></span><a id="sccalc_preview" href="#" data-dir="' . $cache_dir . '"></a>' .
            '</p>';

        // List of existing cache files
        echo
            '<form action="' . $p_url . '" method="post" id="scform">';

        echo '<table id="chk-table-result" class="sysinfo">';
        echo '<caption>' . __('List of static cache files in') . ' ' . substr($cache_dir, strlen($cache_root)) .
        ', ' . __('last update:') . ' ' . date('Y-m-d H:i:s', $cache->getMtime()) . '</caption>';
        echo '<thead>' .
        '<tr>' .
        '<th scope="col" class="nowrap" colspan="3">' . __('Cache subpath') . '</th>' .
        '<th scope="col" class="nowrap maximal">' . __('Cache file') . '</th>' .
            '</tr>' .
            '</thead>';
        echo '<tbody>';

        $files = files::scandir($cache_dir);
        if (is_array($files)) {
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && $file !== 'mtime') {
                    $cache_fullpath = $cache_dir . '/' . $file;
                    if (is_dir($cache_fullpath)) {
                        echo '<tr>' .
                        '<td class="nowrap">' .
                        '<a class="sc_dir" href="#">' . $file . '</a>' .
                        '</td>' .                                     // 1st level
                        '<td class="nowrap">' . __('…') . '</td>' . // 2nd level (loaded via getStaticCacheDir REST)
                        '<td class="nowrap"></td>' .                  // 3rd level (loaded via getStaticCacheList REST)
                        '<td class="nowrap maximal"></td>' .          // cache file (loaded via getStaticCacheList REST too)
                        '</tr>' . "\n";
                    }
                }
            }
        }

        echo '</tbody></table>';
        echo
        '<div class="two-cols">' .
        '<p class="col checkboxes-helpers"></p>' .
        '<p class="col right">' . $core->formNonce() . '<input type="submit" class="delete" id="delscaction" name="delscaction" value="' . __('Delete selected cache files') . '" /></p>' .
            '</div>' .
            '</form>';

        break;

    case 'dcrepo-plugins':
    case 'dcrepo-plugins-cache':
        // Get installation info
        $cache_path = path::real(DC_TPL_CACHE);
        $xml_url    = $core->blog->settings->system->store_plugin_url;
        $in_cache   = false;

        if ($checklist === 'dcrepo-plugins-cache') {
            // Get XML cache file for plugins
            $ser_file = sprintf('%s/%s/%s/%s/%s.ser',
                $cache_path,
                'dcrepo',
                substr(md5($xml_url), 0, 2),
                substr(md5($xml_url), 2, 2),
                md5($xml_url)
            );
            if (file_exists($ser_file)) {
                $in_cache = true;
            }
        }
        $parser    = dcStoreReader::quickParse($xml_url, DC_TPL_CACHE, !$in_cache);
        $raw_datas = !$parser ? [] : $parser->getModules();
        dcUtils::lexicalKeySort($raw_datas);

        echo '<h3>' . __('Repository plugins list') . __(' from: ') . ($in_cache ? __('cache') : $xml_url) . '</h3>';
        echo '<details id="expand-all"><summary>' . __('Plugin ID') . '</summary></details>';
        $url_fmt = '<a href="%1$s">%1$s</a>';
        foreach ($raw_datas as $id => $infos) {
            echo '<details><summary>' . $id . '</summary>';
            echo '<ul>';
            foreach ($infos as $key => $value) {
                $val = (in_array($key, ['file', 'details', 'support', 'sshot']) && $value ? sprintf($url_fmt, $value) : $value);
                echo '<li>' . $key . ' = ' . $val . '</li>';
            }
            echo '</ul>';
            echo '</details>';
        }

        break;

    case 'dcrepo-themes':
    case 'dcrepo-themes-cache':
        // Get installation info
        $cache_path = path::real(DC_TPL_CACHE);
        $xml_url    = $core->blog->settings->system->store_theme_url;
        $in_cache   = false;

        if ($checklist === 'dcrepo-themes-cache') {
            // Get XML cache file for themes
            $ser_file = sprintf('%s/%s/%s/%s/%s.ser',
                $cache_path,
                'dcrepo',
                substr(md5($xml_url), 0, 2),
                substr(md5($xml_url), 2, 2),
                md5($xml_url)
            );
            if (file_exists($ser_file)) {
                $in_cache = true;
            }
        }
        $parser    = dcStoreReader::quickParse($xml_url, DC_TPL_CACHE, !$in_cache);
        $raw_datas = !$parser ? [] : $parser->getModules();
        dcUtils::lexicalKeySort($raw_datas);

        echo '<h3>' . __('Repository themes list') . __(' from: ') . ($in_cache ? __('cache') : $xml_url) . '</h3>';
        echo '<details id="expand-all"><summary>' . __('Theme ID') . '</summary></details>';
        $url_fmt = '<a href="%1$s">%1$s</a>';
        foreach ($raw_datas as $id => $infos) {
            echo '<details><summary>' . $id . '</summary>';
            echo '<ul>';
            foreach ($infos as $key => $value) {
                $val = (in_array($key, ['file', 'details', 'support', 'sshot']) && $value ? sprintf($url_fmt, $value) : $value);
                echo '<li>' . $key . ' = ' . $val . '</li>';
            }
            echo '</ul>';
            echo '</details>';
        }

        break;

    default:
        // Display PHP version and DB version
        $quotes = [
            __('Live long and prosper.'),
            __('To infinity and beyond.'),
            __('So long, and thanks for all the fish.'),
            __('Find a needle in a haystack.'),
            __('A clever person solves a problem. A wise person avoids it.')
        ];
        $q = rand(0, count($quotes) - 1);
        echo '<blockquote class="sysinfo"><p>' . $quotes[$q] . '</p></blockquote>';

        echo '<details open><summary>' . __('System info') . '</summary>';
        echo '<ul>';
        echo '<li>' . __('PHP Version: ') . '<strong>' . phpversion() . '</strong></li>';
        echo '<li>' . __('DB driver: ') . '<strong>' . $core->con->driver() . '</strong> ' . __('version') . ' <strong>' . $core->con->version() . '</strong></li>';
        echo '</ul>';
        echo '</details>';

        break;
}

?>
</body>
</html>
