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
                if ($value !== $undefined) {
                    $value = CoreHelper::simplifyFilename($value);
                }

                yield (new Tr())
                    ->cols([
                        (new Td())
                            ->class('nowrap')
                            ->separator(' ')
                            ->items([
                                (new Img('images/' . ($value !== $undefined ? 'check-on.svg' : 'check-off.svg')))
                                    ->class(['mark', 'mark-' . ($value !== $undefined ? 'check-on' : 'check-off')]),
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

        $populateString = function (string $name, string $display = '') use ($undefined): string {
            if (defined($name) && is_string(constant($name))) {
                return $display !== '' ? $display : trim(var_export(constant($name), true), '\'');
            }

            return $undefined;
        };

        $populateNumeric = function (string $name, string $unit = '') use ($undefined): string {
            if (defined($name) && is_numeric(constant($name))) {
                return var_export(constant($name), true) . ($unit !== '' ? ' ' . $unit : '');
            }

            return $undefined;
        };

        $populateBool = function (string $name) use ($undefined): string {
            if (defined($name) && is_bool(constant($name))) {
                return var_export(constant($name), true);
            }

            return $undefined;
        };

        $constants = [
            'DC_ADMIN_CONTEXT'         => $populateBool('DC_ADMIN_CONTEXT'),
            'DC_ADMIN_MAILFROM'        => $populateString('DC_ADMIN_MAILFROM'),
            'DC_ADMIN_SSL'             => $populateBool('DC_ADMIN_SSL'),
            'DC_ADMIN_URL'             => $populateString('DC_ADMIN_URL'),
            'DC_AKISMET_SUPER'         => $populateBool('DC_AKISMET_SUPER'),
            'DC_ALLOW_MULTI_MODULES'   => $populateBool('DC_ALLOW_MULTI_MODULES'),
            'DC_ALLOW_REPOSITORIES'    => $populateBool('DC_ALLOW_REPOSITORIES'),
            'DC_ANTISPAM_CONF_SUPER'   => $populateBool('DC_ANTISPAM_CONF_SUPER'),
            'DC_AUTH_PASSWORD_ONLY'    => $populateBool('DC_AUTH_PASSWORD_ONLY'),
            'DC_AUTH_SESS_ID'          => $populateString('DC_AUTH_SESS_ID'),
            'DC_AUTH_SESS_UID'         => $populateString('DC_AUTH_SESS_UID'),
            'DC_BACKUP_PATH'           => $populateString('DC_BACKUP_PATH'),
            'DC_BLOG_ID'               => $populateString('DC_BLOG_ID'),
            'DC_CONTEXT_ADMIN'         => $populateBool('DC_CONTEXT_ADMIN'),
            'DC_CONTEXT_MODULE'        => $populateBool('DC_CONTEXT_MODULE'),
            'DC_CRYPT_ALGO'            => $populateString('DC_CRYPT_ALGO'),
            'DC_CSP_LOGFILE'           => $populateString('DC_CSP_LOGFILE'),
            'DC_STORE_NOT_UPDATE'      => $populateBool('DC_STORE_NOT_UPDATE'),
            'DC_DBDRIVER'              => $populateString('DC_DBDRIVER'),
            'DC_DBHOST'                => $populateString('DC_DBHOST'),
            'DC_DBNAME'                => $populateString('DC_DBNAME'),
            'DC_DBPASSWORD'            => $populateString('DC_DBPASSWORD', '********* ' . __('(see inc/config.php)')),
            'DC_DBPREFIX'              => $populateString('DC_DBPREFIX'),
            'DC_DBUSER'                => $populateString('DC_DBUSER'),
            'DC_DEBUG'                 => $populateBool('DC_DEBUG'),
            'DC_DEFAULT_JQUERY'        => $populateString('DC_DEFAULT_JQUERY'),
            'DC_DEFAULT_THEME'         => $populateString('DC_DEFAULT_THEME'),
            'DC_DEFAULT_TPLSET'        => $populateString('DC_DEFAULT_TPLSET'),
            'DC_DEV'                   => $populateBool('DC_DEV'),
            'DC_DIGESTS'               => $populateString('DC_DIGESTS'),
            'DC_DISTRIB_PLUGINS'       => $populateString('DC_DISTRIB_PLUGINS'),
            'DC_DISTRIB_THEMES'        => $populateString('DC_DISTRIB_THEMES'),
            'DC_DNSBL_SUPER'           => $populateBool('DC_DNSBL_SUPER'),
            'DC_FAIRTRACKBACKS_FORCE'  => $populateBool('DC_FAIRTRACKBACKS_FORCE'),
            'DC_FORCE_SCHEME_443'      => $populateBool('DC_FORCE_SCHEME_443'),
            'DC_L10N_ROOT'             => $populateString('DC_L10N_ROOT'),
            'DC_L10N_UPDATE_URL'       => $populateString('DC_L10N_UPDATE_URL'),
            'DC_MASTER_KEY'            => $populateString('DC_MASTER_KEY', '********* ' . __('(see inc/config.php)')),
            'DC_MAX_UPLOAD_SIZE'       => $populateNumeric('DC_MAX_UPLOAD_SIZE'),
            'DC_MEDIA_UPDATE_DB_LIMIT' => $populateNumeric('DC_MEDIA_UPDATE_DB_LIMIT'),
            'DC_MIGRATE'               => $populateBool('DC_MIGRATE'),
            'DC_MODERN'                => $populateBool('DC_MODERN'),
            'DC_NEXT_REQUIRED_PHP'     => $populateString('DC_NEXT_REQUIRED_PHP'),
            'DC_NOT_UPDATE'            => $populateBool('DC_NOT_UPDATE'),
            'DC_PLUGINS_ROOT'          => $populateString('DC_PLUGINS_ROOT'),
            'DC_QUERY_TIMEOUT'         => $populateNumeric('DC_QUERY_TIMEOUT', __('seconds')),
            'DC_RC_PATH'               => $populateString('DC_RC_PATH'),
            'DC_REST_SERVICES'         => $populateBool('DC_REST_SERVICES'),
            'DC_ROOT'                  => $populateString('DC_ROOT'),
            'DC_SESSION_NAME'          => $populateString('DC_SESSION_NAME'),
            'DC_SESSION_TTL'           => $populateString('DC_SESSION_TTL'),
            'DC_SHOW_HIDDEN_DIRS'      => $populateBool('DC_SHOW_HIDDEN_DIRS'),
            'DC_START_TIME'            => $populateNumeric('DC_START_TIME'),
            'DC_TPL_CACHE'             => $populateString('DC_TPL_CACHE'),
            'DC_UPDATE_URL'            => $populateString('DC_UPDATE_URL'),
            'DC_UPDATE_VERSION'        => $populateString('DC_UPDATE_VERSION'),
            'DC_UPGRADE'               => $populateString('DC_UPGRADE'),
            'DC_VAR'                   => $populateString('DC_VAR'),
            'DC_VENDOR_NAME'           => $populateString('DC_VENDOR_NAME'),
            'DC_VERSION'               => $populateString('DC_VERSION'),
            'CLEARBRICKS_VERSION'      => $populateString('CLEARBRICKS_VERSION'),
            'HTTP_PROXY_HOST'          => $populateString('HTTP_PROXY_HOST'),
            'HTTP_PROXY_PORT'          => $populateString('HTTP_PROXY_PORT'),
            'SOCKET_VERIFY_PEER'       => $populateBool('SOCKET_VERIFY_PEER'),
        ];

        if (App::plugins()->moduleExists('staticCache')) {
            $constants['DC_SC_CACHE_ENABLE']    = $populateBool('DC_SC_CACHE_ENABLE');
            $constants['DC_SC_CACHE_DIR']       = $populateString('DC_SC_CACHE_DIR');
            $constants['DC_SC_CACHE_BLOGS_ON']  = $populateString('DC_SC_CACHE_BLOGS_ON');
            $constants['DC_SC_CACHE_BLOGS_OFF'] = $populateString('DC_SC_CACHE_BLOGS_OFF');
            $constants['DC_SC_EXCLUDED_URL']    = $populateString('DC_SC_EXCLUDED_URL');
        }

        return [$undefined, $constants];
    }
}
