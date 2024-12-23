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
declare(strict_types=1);

namespace Dotclear\Plugin\sysInfo\Helper;

use Dotclear\App;
use Dotclear\Plugin\sysInfo\CoreHelper;

class Constants
{
    /**
     * Return list of Dotclear constants
     *
     * @return     string
     */
    public static function render(): string
    {
        [$undefined, $constants] = self::getConstants();

        // Affichage des constantes remarquables de Dotclear
        $str = '<table id="constants" class="sysinfo"><caption>' . __('Dotclear constants') . ' (' . sprintf('%d', count($constants)) . ')' . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Constant') . '</th>' .
            '<th scope="col" class="maximal">' . __('Value') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        App::lexical()->lexicalKeySort($constants, App::lexical()::ADMIN_LOCALE);
        foreach ($constants as $c => $v) {
            $str .= '<tr><td class="nowrap"><img class="mark mark-' . ($v != $undefined ? 'check-on' : 'check-off') . '" src="images/' . ($v != $undefined ? 'check-on.svg' : 'check-off.svg') . '"> <code>' . $c . '</code></td>' .
                '<td class="maximal">';
            if ($v != $undefined) {
                if (is_string($v)) {
                    $v = CoreHelper::simplifyFilename($v);
                }

                $str .= $v;
            }

            $str .= '</td></tr>';
        }

        return $str . '</tbody></table>';
    }

    /**
     * Get current list of Dotclear constants and their values
     *
     * @return     array{0: string, array<string, string>}  array[0] = undefined value, array[1] = list of constants
     */
    private static function getConstants(): array
    {
        $undefined = '<!-- undefined -->';
        $constants = [
            'DC_ADMIN_CONTEXT'        => defined('DC_ADMIN_CONTEXT') ? (constant('DC_ADMIN_CONTEXT') ? 'true' : 'false') : $undefined,
            'DC_ADMIN_MAILFROM'       => defined('DC_ADMIN_MAILFROM') ? DC_ADMIN_MAILFROM : $undefined,
            'DC_ADMIN_SSL'            => defined('DC_ADMIN_SSL') ? (DC_ADMIN_SSL ? 'true' : 'false') : $undefined,
            'DC_ADMIN_URL'            => defined('DC_ADMIN_URL') ? DC_ADMIN_URL : $undefined,
            'DC_AKISMET_SUPER'        => defined('DC_AKISMET_SUPER') ? (constant('DC_AKISMET_SUPER') ? 'true' : 'false') : $undefined,
            'DC_ALLOW_MULTI_MODULES'  => defined('DC_ALLOW_MULTI_MODULES') ? (DC_ALLOW_MULTI_MODULES ? 'true' : 'false') : $undefined,
            'DC_ALLOW_REPOSITORIES'   => defined('DC_ALLOW_REPOSITORIES') ? (DC_ALLOW_REPOSITORIES ? 'true' : 'false') : $undefined,
            'DC_ANTISPAM_CONF_SUPER'  => defined('DC_ANTISPAM_CONF_SUPER') ? (DC_ANTISPAM_CONF_SUPER ? 'true' : 'false') : $undefined,
            'DC_AUTH_PAGE'            => defined('DC_AUTH_PAGE') ? constant('DC_AUTH_PAGE') : $undefined,
            'DC_AUTH_SESS_ID'         => defined('DC_AUTH_SESS_ID') ? constant('DC_AUTH_SESS_ID') : $undefined,
            'DC_AUTH_SESS_UID'        => defined('DC_AUTH_SESS_UID') ? constant('DC_AUTH_SESS_UID') : $undefined,
            'DC_BACKUP_PATH'          => defined('DC_BACKUP_PATH') ? DC_BACKUP_PATH : $undefined,
            'DC_BLOG_ID'              => defined('DC_BLOG_ID') ? DC_BLOG_ID : $undefined,
            'DC_CONTEXT_ADMIN'        => defined('DC_CONTEXT_ADMIN') ? (constant('DC_CONTEXT_ADMIN') ? 'true' : 'false') : $undefined,
            'DC_CONTEXT_MODULE'       => defined('DC_CONTEXT_MODULE') ? (constant('DC_CONTEXT_MODULE') ? 'true' : 'false') : $undefined,
            'DC_CRYPT_ALGO'           => defined('DC_CRYPT_ALGO') ? DC_CRYPT_ALGO : $undefined,
            'DC_CSP_LOGFILE'          => defined('DC_CSP_LOGFILE') ? DC_CSP_LOGFILE : $undefined,
            'DC_STORE_NOT_UPDATE'     => defined('DC_STORE_NOT_UPDATE') ? (DC_STORE_NOT_UPDATE ? 'true' : 'false') : $undefined,
            'DC_DBDRIVER'             => defined('DC_DBDRIVER') ? DC_DBDRIVER : $undefined,
            'DC_DBHOST'               => defined('DC_DBHOST') ? DC_DBHOST : $undefined,
            'DC_DBNAME'               => defined('DC_DBNAME') ? DC_DBNAME : $undefined,
            'DC_DBPASSWORD'           => defined('DC_DBPASSWORD') ? '********* ' . __('(see inc/config.php)') /* DC_DBPASSWORD */ : $undefined,
            'DC_DBPREFIX'             => defined('DC_DBPREFIX') ? DC_DBPREFIX : $undefined,
            'DC_DBUSER'               => defined('DC_DBUSER') ? DC_DBUSER : $undefined,
            'DC_DEBUG'                => defined('DC_DEBUG') ? (DC_DEBUG ? 'true' : 'false') : $undefined,
            'DC_DEFAULT_JQUERY'       => defined('DC_DEFAULT_JQUERY') ? DC_DEFAULT_JQUERY : $undefined,
            'DC_DEFAULT_THEME'        => defined('DC_DEFAULT_THEME') ? DC_DEFAULT_THEME : $undefined,
            'DC_DEFAULT_TPLSET'       => defined('DC_DEFAULT_TPLSET') ? DC_DEFAULT_TPLSET : $undefined,
            'DC_DEV'                  => defined('DC_DEV') ? (DC_DEV ? 'true' : 'false') : $undefined,
            'DC_DIGESTS'              => defined('DC_DIGESTS') ? DC_DIGESTS : $undefined,
            'DC_DISTRIB_PLUGINS'      => defined('DC_DISTRIB_PLUGINS') ? DC_DISTRIB_PLUGINS : $undefined,
            'DC_DISTRIB_THEMES'       => defined('DC_DISTRIB_THEMES') ? DC_DISTRIB_THEMES : $undefined,
            'DC_DNSBL_SUPER'          => defined('DC_DNSBL_SUPER') ? (constant('DC_DNSBL_SUPER') ? 'true' : 'false') : $undefined,
            'DC_FAIRTRACKBACKS_FORCE' => defined('DC_FAIRTRACKBACKS_FORCE') ? (DC_FAIRTRACKBACKS_FORCE ? 'true' : 'false') : $undefined,
            'DC_FORCE_SCHEME_443'     => defined('DC_FORCE_SCHEME_443') ? (DC_FORCE_SCHEME_443 ? 'true' : 'false') : $undefined,
            'DC_L10N_ROOT'            => defined('DC_L10N_ROOT') ? DC_L10N_ROOT : $undefined,
            'DC_L10N_UPDATE_URL'      => defined('DC_L10N_UPDATE_URL') ? DC_L10N_UPDATE_URL : $undefined,
            'DC_MASTER_KEY'           => defined('DC_MASTER_KEY') ? '********* ' . __('(see inc/config.php)') /* DC_MASTER_KEY */ : $undefined,
            'DC_MAX_UPLOAD_SIZE'      => defined('DC_MAX_UPLOAD_SIZE') ? DC_MAX_UPLOAD_SIZE : $undefined,
            'DC_MIGRATE'              => defined('DC_MIGRATE') ? (DC_MIGRATE ? 'true' : 'false') : $undefined,
            'DC_NEXT_REQUIRED_PHP'    => defined('DC_NEXT_REQUIRED_PHP') ? DC_NEXT_REQUIRED_PHP : $undefined,
            'DC_NOT_UPDATE'           => defined('DC_NOT_UPDATE') ? (DC_NOT_UPDATE ? 'true' : 'false') : $undefined,
            'DC_PLUGINS_ROOT'         => defined('DC_PLUGINS_ROOT') ? DC_PLUGINS_ROOT : $undefined,
            'DC_QUERY_TIMEOUT'        => defined('DC_QUERY_TIMEOUT') ? DC_QUERY_TIMEOUT . ' ' . __('seconds') : $undefined,
            'DC_RC_PATH'              => defined('DC_RC_PATH') ? DC_RC_PATH : $undefined,
            'DC_REST_SERVICES'        => defined('DC_REST_SERVICES') ? (DC_REST_SERVICES ? 'true' : 'false') : $undefined,
            'DC_ROOT'                 => defined('DC_ROOT') ? DC_ROOT : $undefined,
            'DC_SESSION_NAME'         => defined('DC_SESSION_NAME') ? DC_SESSION_NAME : $undefined,
            'DC_SESSION_TTL'          => defined('DC_SESSION_TTL') ? DC_SESSION_TTL : $undefined,
            'DC_SHOW_HIDDEN_DIRS'     => defined('DC_SHOW_HIDDEN_DIRS') ? (DC_SHOW_HIDDEN_DIRS ? 'true' : 'false') : $undefined,
            'DC_START_TIME'           => defined('DC_START_TIME') ? DC_START_TIME : $undefined,
            'DC_TPL_CACHE'            => defined('DC_TPL_CACHE') ? DC_TPL_CACHE : $undefined,
            'DC_UPDATE_URL'           => defined('DC_UPDATE_URL') ? DC_UPDATE_URL : $undefined,
            'DC_UPDATE_VERSION'       => defined('DC_UPDATE_VERSION') ? DC_UPDATE_VERSION : $undefined,
            'DC_UPGRADE'              => defined('DC_UPGRADE') ? DC_UPGRADE : $undefined,
            'DC_VAR'                  => defined('DC_VAR') ? DC_VAR : $undefined,
            'DC_VENDOR_NAME'          => defined('DC_VENDOR_NAME') ? DC_VENDOR_NAME : $undefined,
            'DC_VERSION'              => defined('DC_VERSION') ? DC_VERSION : $undefined,
            'CLEARBRICKS_VERSION'     => defined('CLEARBRICKS_VERSION') ? CLEARBRICKS_VERSION : $undefined,
        ];

        if (App::plugins()->moduleExists('staticCache')) {
            $constants['DC_SC_CACHE_ENABLE']    = defined('DC_SC_CACHE_ENABLE') ? (DC_SC_CACHE_ENABLE ? 'true' : 'false') : $undefined;
            $constants['DC_SC_CACHE_DIR']       = defined('DC_SC_CACHE_DIR') ? DC_SC_CACHE_DIR : $undefined;
            $constants['DC_SC_CACHE_BLOGS_ON']  = defined('DC_SC_CACHE_BLOGS_ON') ? constant('DC_SC_CACHE_BLOGS_ON') : $undefined;
            $constants['DC_SC_CACHE_BLOGS_OFF'] = defined('DC_SC_CACHE_BLOGS_OFF') ? constant('DC_SC_CACHE_BLOGS_OFF') : $undefined;
            $constants['DC_SC_EXCLUDED_URL']    = defined('DC_SC_EXCLUDED_URL') ? DC_SC_EXCLUDED_URL : $undefined;
        }

        return [$undefined, $constants];
    }
}
