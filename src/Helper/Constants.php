<?php

/**
 * @brief sysInfo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\sysInfo\Helper;

use Dotclear\App;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Img;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Plugin\sysInfo\CoreHelper;

class Constants
{
    /**
     * Return list of Dotclear constants
     */
    public static function render(): string
    {
        [$undefined, $constants] = self::getConstants();
        App::lexical()->lexicalKeySort($constants, App::lexical()::ADMIN_LOCALE);

        // Affichage des constantes remarquables de Dotclear

        $lines = function () use ($constants, $undefined) {
            foreach ($constants as $key => $value) {
                if ($value != $undefined) {
                    $value = CoreHelper::simplifyFilename($value);
                }

                yield (new Tr())
                    ->cols([
                        (new Td())
                            ->class('nowrap')
                            ->separator(' ')
                            ->items([
                                (new Img('images/' . ($value != $undefined ? 'check-on.svg' : 'check-off.svg')))
                                    ->class(['mark', 'mark-' . ($value != $undefined ? 'check-on' : 'check-off')]),
                                (new Text('code', $key)),
                            ]),
                        (new Td())
                            ->class('maximal')
                            ->text($value),
                    ]);
            }
        };

        return (new Table('constants'))
            ->class('sysinfo')
            ->caption(new Caption(__('Dotclear constants') . ' (' . sprintf('%d', count($constants)) . ')'))
            ->thead((new Thead())
                ->rows([
                    (new Tr())
                        ->cols([
                            (new Th())
                                ->scope('col')
                                ->class('nowrap')
                                ->text(__('Constant')),
                            (new Th())
                                ->scope('col')
                                ->class('maximal')
                                ->text(__('Value')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([
                    ... $lines(),
                ]))
        ->render();
    }

    /**
     * Get current list of Dotclear constants and their values
     *
     * @return     array{0: string, array<string, string>}  array[0] = undefined value, array[1] = list of constants
     */
    private static function getConstants(): array
    {
        $undefined = '<!-- undefined -->';

        $populate_string = function (string $name, string $display = '') use ($undefined): string {
            if (defined($name) && is_string(constant($name))) {
                return $display !== '' ? $display : trim(var_export(constant($name), true), '\'');
            }

            return $undefined;
        };

        $populate_numeric = function (string $name, string $unit = '') use ($undefined): string {
            if (defined($name) && is_numeric(constant($name))) {
                return var_export(constant($name), true) . ($unit !== '' ? ' ' . $unit : '');
            }

            return $undefined;
        };

        $populate_bool = function (string $name) use ($undefined): string {
            if (defined($name) && is_bool(constant($name))) {
                return var_export(constant($name), true);
            }

            return $undefined;
        };

        $constants = [
            'DC_ADMIN_CONTEXT'         => $populate_bool('DC_ADMIN_CONTEXT'),
            'DC_ADMIN_MAILFROM'        => $populate_string('DC_ADMIN_MAILFROM'),
            'DC_ADMIN_SSL'             => $populate_bool('DC_ADMIN_SSL'),
            'DC_ADMIN_URL'             => $populate_string('DC_ADMIN_URL'),
            'DC_AKISMET_SUPER'         => $populate_bool('DC_AKISMET_SUPER'),
            'DC_ALLOW_MULTI_MODULES'   => $populate_bool('DC_ALLOW_MULTI_MODULES'),
            'DC_ALLOW_REPOSITORIES'    => $populate_bool('DC_ALLOW_REPOSITORIES'),
            'DC_ANTISPAM_CONF_SUPER'   => $populate_bool('DC_ANTISPAM_CONF_SUPER'),
            'DC_AUTH_PASSWORD_ONLY'    => $populate_bool('DC_AUTH_PASSWORD_ONLY'),
            'DC_AUTH_SESS_ID'          => $populate_string('DC_AUTH_SESS_ID'),
            'DC_AUTH_SESS_UID'         => $populate_string('DC_AUTH_SESS_UID'),
            'DC_BACKUP_PATH'           => $populate_string('DC_BACKUP_PATH'),
            'DC_BLOG_ID'               => $populate_string('DC_BLOG_ID'),
            'DC_CONTEXT_ADMIN'         => $populate_bool('DC_CONTEXT_ADMIN'),
            'DC_CONTEXT_MODULE'        => $populate_bool('DC_CONTEXT_MODULE'),
            'DC_CRYPT_ALGO'            => $populate_string('DC_CRYPT_ALGO'),
            'DC_CSP_LOGFILE'           => $populate_string('DC_CSP_LOGFILE'),
            'DC_STORE_NOT_UPDATE'      => $populate_bool('DC_STORE_NOT_UPDATE'),
            'DC_DBDRIVER'              => $populate_string('DC_DBDRIVER'),
            'DC_DBHOST'                => $populate_string('DC_DBHOST'),
            'DC_DBNAME'                => $populate_string('DC_DBNAME'),
            'DC_DBPASSWORD'            => $populate_string('DC_DBPASSWORD', '********* ' . __('(see inc/config.php)')),
            'DC_DBPREFIX'              => $populate_string('DC_DBPREFIX'),
            'DC_DBUSER'                => $populate_string('DC_DBUSER'),
            'DC_DEBUG'                 => $populate_bool('DC_DEBUG'),
            'DC_DEFAULT_JQUERY'        => $populate_string('DC_DEFAULT_JQUERY'),
            'DC_DEFAULT_THEME'         => $populate_string('DC_DEFAULT_THEME'),
            'DC_DEFAULT_TPLSET'        => $populate_string('DC_DEFAULT_TPLSET'),
            'DC_DEV'                   => $populate_bool('DC_DEV'),
            'DC_DIGESTS'               => $populate_string('DC_DIGESTS'),
            'DC_DISTRIB_PLUGINS'       => $populate_string('DC_DISTRIB_PLUGINS'),
            'DC_DISTRIB_THEMES'        => $populate_string('DC_DISTRIB_THEMES'),
            'DC_DNSBL_SUPER'           => $populate_bool('DC_DNSBL_SUPER'),
            'DC_FAIRTRACKBACKS_FORCE'  => $populate_bool('DC_FAIRTRACKBACKS_FORCE'),
            'DC_FORCE_SCHEME_443'      => $populate_bool('DC_FORCE_SCHEME_443'),
            'DC_L10N_ROOT'             => $populate_string('DC_L10N_ROOT'),
            'DC_L10N_UPDATE_URL'       => $populate_string('DC_L10N_UPDATE_URL'),
            'DC_MASTER_KEY'            => $populate_string('DC_MASTER_KEY', '********* ' . __('(see inc/config.php)')),
            'DC_MAX_UPLOAD_SIZE'       => $populate_numeric('DC_MAX_UPLOAD_SIZE'),
            'DC_MEDIA_UPDATE_DB_LIMIT' => $populate_numeric('DC_MEDIA_UPDATE_DB_LIMIT'),
            'DC_MIGRATE'               => $populate_bool('DC_MIGRATE'),
            'DC_NEXT_REQUIRED_PHP'     => $populate_string('DC_NEXT_REQUIRED_PHP'),
            'DC_NOT_UPDATE'            => $populate_bool('DC_NOT_UPDATE'),
            'DC_PLUGINS_ROOT'          => $populate_string('DC_PLUGINS_ROOT'),
            'DC_QUERY_TIMEOUT'         => $populate_numeric('DC_QUERY_TIMEOUT', __('seconds')),
            'DC_RC_PATH'               => $populate_string('DC_RC_PATH'),
            'DC_REST_SERVICES'         => $populate_bool('DC_REST_SERVICES'),
            'DC_ROOT'                  => $populate_string('DC_ROOT'),
            'DC_SESSION_NAME'          => $populate_string('DC_SESSION_NAME'),
            'DC_SESSION_TTL'           => $populate_string('DC_SESSION_TTL'),
            'DC_SHOW_HIDDEN_DIRS'      => $populate_bool('DC_SHOW_HIDDEN_DIRS'),
            'DC_START_TIME'            => $populate_numeric('DC_START_TIME'),
            'DC_TPL_CACHE'             => $populate_string('DC_TPL_CACHE'),
            'DC_UPDATE_URL'            => $populate_string('DC_UPDATE_URL'),
            'DC_UPDATE_VERSION'        => $populate_string('DC_UPDATE_VERSION'),
            'DC_UPGRADE'               => $populate_string('DC_UPGRADE'),
            'DC_VAR'                   => $populate_string('DC_VAR'),
            'DC_VENDOR_NAME'           => $populate_string('DC_VENDOR_NAME'),
            'DC_VERSION'               => $populate_string('DC_VERSION'),
            'CLEARBRICKS_VERSION'      => $populate_string('CLEARBRICKS_VERSION'),
            'HTTP_PROXY_HOST'          => $populate_string('HTTP_PROXY_HOST'),
            'HTTP_PROXY_PORT'          => $populate_string('HTTP_PROXY_PORT'),
            'SOCKET_VERIFY_PEER'       => $populate_bool('SOCKET_VERIFY_PEER'),
        ];

        if (App::plugins()->moduleExists('staticCache')) {
            $constants['DC_SC_CACHE_ENABLE']    = $populate_bool('DC_SC_CACHE_ENABLE');
            $constants['DC_SC_CACHE_DIR']       = $populate_string('DC_SC_CACHE_DIR');
            $constants['DC_SC_CACHE_BLOGS_ON']  = $populate_string('DC_SC_CACHE_BLOGS_ON');
            $constants['DC_SC_CACHE_BLOGS_OFF'] = $populate_string('DC_SC_CACHE_BLOGS_OFF');
            $constants['DC_SC_EXCLUDED_URL']    = $populate_string('DC_SC_EXCLUDED_URL');
        }

        return [$undefined, $constants];
    }
}
