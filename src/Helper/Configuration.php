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

class Configuration
{
    /**
     * Return list of Dotclear Config values
     */
    public static function render(): string
    {
        [$release, $config] = self::getConfig();

        // Affichage des valeurs de release de Dotclear
        $str = '<table id="dotclear-release" class="sysinfo"><caption>' . __('Dotclear release') . ' (' . sprintf('%d', count($release)) . ')' . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Key') . '</th>' .
            '<th scope="col" class="maximal">' . __('Value') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        App::lexical()->lexicalKeySort($release, App::lexical()::ADMIN_LOCALE);
        foreach ($release as $c => $v) {
            $str .= '<tr><td class="nowrap"><code>' . $c . '</code></td>';
            $str .= '<td class="maximal">' . (is_string($v) ? CoreHelper::simplifyFilename($v) : $v) . '</td></tr>';
        }

        $str .= '</tbody></table>';

        // Affichage des valeurs de configuration de Dotclear
        $str .= '<table id="dotclear-config" class="sysinfo"><caption>' . __('Dotclear configuration') . ' (' . sprintf('%d', count($config)) . ')' . '</caption>' .
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="nowrap">' . __('Key') . '</th>' .
            '<th scope="col">' . __('Type') . '</th>' .
            '<th scope="col" class="maximal">' . __('Value') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';
        App::lexical()->lexicalKeySort($config, App::lexical()::ADMIN_LOCALE);
        foreach ($config as $c => $v) {
            $str .= '<tr>';
            $str .= '<td class="nowrap"><code>' . $c . '</code></td>';
            $str .= '<td>' . gettype($v) . '</td>';
            $str .= '<td class="maximal">' . (is_string($v) ? CoreHelper::simplifyFilename($v) : var_export($v, true)) . '</td>';
            $str .= '</tr>';
        }

        return $str . '</tbody></table>';
    }

    /**
     * Get current list of Dotclear Config items and their values
     *
     * @return     array{array<string, mixed>, array<string, mixed>}  array[0] = list of release values, array[1] = list of config values
     */
    private static function getConfig(): array
    {
        $release = [
            'release_version'      => App::config()->release('release_version'),
            'release_name'         => App::config()->release('release_name'),
            'l10n_update_url'      => App::config()->release('l10n_update_url'),
            'distributed_plugins'  => App::config()->release('distributed_plugins'),
            'distributed_themes'   => App::config()->release('distributed_themes'),
            'default_theme'        => App::config()->release('default_theme'),
            'default_tplset'       => App::config()->release('default_tplset'),
            'default_jquery'       => App::config()->release('default_jquery'),
            'dotclear_migrate'     => App::config()->release('dotclear_migrate'),
            'php_min'              => App::config()->release('php_min'),
            'mysql_min'            => App::config()->release('mysql_min'),
            'pgsql_min'            => App::config()->release('pgsql_min'),
            'next_php'             => App::config()->release('next_php'),
            'release_update_url'   => App::config()->release('release_update_url'),
            'release_update_canal' => App::config()->release('release_update_canal'),
        ];

        $config = [
            'startTime'          => App::config()->startTime(),
            'cliMode'            => App::config()->cliMode(),
            'debugMode'          => App::config()->debugMode(),
            'devMode'            => App::config()->devMode(),
            'errorFile'          => App::config()->errorFile(),
            'blogId'             => App::config()->blogId(),
            'dotclearRoot'       => App::config()->dotclearRoot(),
            'dotclearVersion'    => App::config()->dotclearVersion(),
            'dotclearName'       => App::config()->dotclearName(),
            'hasConfig'          => App::config()->hasConfig(),
            'configPath'         => App::config()->configPath(),
            'digestsRoot'        => App::config()->digestsRoot(),
            'l10nRoot'           => App::config()->l10nRoot(),
            'l10nUpdateUrl'      => App::config()->l10nUpdateUrl(),
            'distributedPlugins' => App::config()->distributedPlugins(),
            'distributedThemes'  => App::config()->distributedThemes(),
            'defaultTheme'       => App::config()->defaultTheme(),
            'defaultTplset'      => App::config()->defaultTplset(),
            'defaultJQuery'      => App::config()->defaultJQuery(),
            'dotclearMigrate'    => App::config()->dotclearMigrate(),
            'minRequiredPhp'     => App::config()->minRequiredPhp(),
            'minRequiredMysql'   => App::config()->minRequiredMysql(),
            'minRequiredPgsql'   => App::config()->minRequiredPgsql(),
            'nextRequiredPhp'    => App::config()->nextRequiredPhp(),
            'vendorName'         => App::config()->vendorName(),
            'sessionTtl'         => App::config()->sessionTtl(),
            'sessionName'        => App::config()->sessionName(),
            'adminSsl'           => App::config()->adminSsl(),
            'adminMailfrom'      => App::config()->adminMailfrom(),
            'adminUrl'           => App::config()->adminUrl(),
            'dbDriver'           => App::config()->dbDriver(),
            'dbHost'             => App::config()->dbHost(),
            'dbUser'             => App::config()->dbUser(),
            'dbPassword'         => '********* ' . __('(see inc/config.php)'), // App::config()->dbPassword(),
            'dbName'             => App::config()->dbName(),
            'dbPrefix'           => App::config()->dbPrefix(),
            'dbPersist'          => App::config()->dbPersist(),
            'masterKey'          => '********* ' . __('(see inc/config.php)'), // App::config()->masterKey(),
            'cryptAlgo'          => App::config()->cryptAlgo(),
            'coreUpdateUrl'      => App::config()->coreUpdateUrl(),
            'coreUpdateCanal'    => App::config()->coreUpdateCanal(),
            'coreNotUpdate'      => App::config()->coreNotUpdate(),
            'allowMultiModules'  => App::config()->allowMultiModules(),
            'storeNotUpdate'     => App::config()->storeNotUpdate(),
            'allowRepositories'  => App::config()->allowRepositories(),
            'allowRestServices'  => App::config()->allowRestServices(),
            'cacheRoot'          => App::config()->cacheRoot(),
            'varRoot'            => App::config()->varRoot(),
            'backupRoot'         => App::config()->backupRoot(),
            'pluginsRoot'        => App::config()->pluginsRoot(),
            'coreUpgrade'        => App::config()->coreUpgrade(),
            'maxUploadSize'      => App::config()->maxUploadSize(),
            'queryTimeout'       => App::config()->queryTimeout(),
            'showHiddenDirs'     => App::config()->showHiddenDirs(),
            'httpScheme443'      => App::config()->httpScheme443(),
            'httpReverseProxy'   => App::config()->httpReverseProxy(),
            'checkAdsBlocker'    => App::config()->checkAdsBlocker(),
            'cspReportFile'      => App::config()->cspReportFile(),
        ];

        return [$release, $config];
    }
}
