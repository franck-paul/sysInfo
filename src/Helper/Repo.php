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

use dcCore;
use dcModuleDefine;
use dcThemes;
use dcUtils;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Network\Http;
use Dotclear\Module\StoreParser;
use Dotclear\Module\StoreReader;

class Repo
{
    /**
     * Return list of available modules
     *
     * @param      bool    $use_cache  The use cache
     * @param      string  $url        The url
     * @param      string  $title      The title
     * @param      string  $label      The label
     *
     * @return     string
     */
    private static function renderModules(bool $use_cache, string $url, string $title, string $label): string
    {
        $in_cache = false;
        $parser   = self::parseRepo($use_cache, $url, $in_cache);

        $defines   = !$parser ? [] : $parser->getDefines();     // @phpstan-ignore-line
        $raw_datas = [];
        foreach ($defines as $define) {
            $raw_datas[$define->getId()] = $define;
        }
        dcUtils::lexicalKeySort($raw_datas, dcUtils::ADMIN_LOCALE);
        $count = $parser ? ' (' . sprintf('%d', count($raw_datas)) . ')' : '';

        $str = '<h3>' . $title . __(' from: ') . ($in_cache ? __('cache') : $url) . $count . '</h3>';
        if (!$parser) {     // @phpstan-ignore-line
            $str .= '<p>' . __('Repository is unreachable') . '</p>';
        } else {
            $str .= '<details id="expand-all"><summary>' . $label . '</summary></details>';

            foreach ($raw_datas as $id => $define) {
                $str .= self::renderModule($id, $define);
            }
        }

        return $str;
    }

    /**
     * Return list of available modules (from alternate repositories)
     *
     * @param      array<int|string, mixed>     $modules    The modules
     * @param      bool                         $use_cache  The use cache
     * @param      string                       $title      The title
     *
     * @return     string
     */
    private static function renderAltModules(array $modules, bool $use_cache, string $title): string
    {
        $lines = '<table><caption>' . $title . '</caption><thead><tr><th>' . __('Repositories') . '</th></tr></thead><tbody>';
        foreach ($modules as $module) {
            if ($module->get('repository') != '' && DC_ALLOW_REPOSITORIES) {
                $url      = substr($module->get('repository'), -12, 12) == '/dcstore.xml' ? $module->get('repository') : Http::concatURL($module->get('repository'), 'dcstore.xml');
                $in_cache = false;
                $parser   = self::parseRepo($use_cache, $url, $in_cache);

                $defines   = !$parser ? [] : $parser->getDefines();
                $raw_datas = [];
                foreach ($defines as $define) {
                    $raw_datas[$define->getId()] = $define;
                }
                dcUtils::lexicalKeySort($raw_datas, dcUtils::ADMIN_LOCALE);
                $count = $parser && count($raw_datas) > 1 ? ' (' . sprintf('%d', count($raw_datas)) . ')' : '';

                $str   = '';
                $label = $url . ' ' . ($in_cache ? __('in cache') : '') . $count;
                $str .= '<tr><td>' . '<p><strong>' . $label . '</strong></p>';
                if (!$parser) {     // @phpstan-ignore-line
                    $str .= '<p>' . __('Repository is unreachable') . '</p>';
                } else {
                    if (count($raw_datas) > 1) {
                        $str .= '<details><summary>' . __('Repository content') . '</summary>';
                    }
                    foreach ($raw_datas as $id => $define) {
                        $str .= self::renderModule($id, $define);
                    }
                    if (count($raw_datas) > 1) {
                        $str .= '</details>';
                    }
                }
                $str .= '</td></tr>';

                $lines .= $str;
            }
        }
        $lines .= '</tbody></table>';

        return $lines;
    }

    /**
     * Render content for a single module
     *
     * @param      string          $id      The identifier
     * @param      dcModuleDefine  $define  The define
     *
     * @return     string
     */
    private static function renderModule(string $id, dcModuleDefine $define): string
    {
        $infos = $define->dump();
        $str   = '<details><summary>' . $id . '</summary>';
        $str .= '<ul>';
        foreach ($infos as $key => $value) {
            if (in_array($key, ['file', 'details', 'support', 'sshot'])) {
                $val = $value ? sprintf('<a href="%1$s">%1$s</a>', $value) : $value;
            } else {
                $val = is_array($value) ? var_export($value, true) : $value;
            }
            $str .= '<li>' . $key . ' = ' . $val . '</li>';
        }
        $str .= '</ul>';
        $str .= '</details>';

        return $str;
    }

    /**
     * Parse a repository
     *
     * @param      bool    $use_cache  The use cache
     * @param      string  $url        The url
     *
     * @return     false
     */
    private static function parseRepo(bool $use_cache, string $url, bool &$in_cache): bool|StoreParser
    {
        $cache_path = Path::real(DC_TPL_CACHE);
        $in_cache   = false;

        if ($use_cache) {
            // Get XML cache file for modules
            $ser_file = sprintf(
                '%s/%s/%s/%s/%s.ser',
                $cache_path,
                'dcrepo',
                substr(md5($url), 0, 2),
                substr(md5($url), 2, 2),
                md5($url)
            );
            if (file_exists($ser_file)) {
                $in_cache = true;
            }
        }

        return StoreReader::quickParse($url, DC_TPL_CACHE, !$in_cache);
    }

    /**
     * Return list of available plugins
     *
     * @param      bool    $use_cache  Use cache if available
     *
     * @return     string
     */
    public static function renderPlugins(bool $use_cache = false): string
    {
        return self::renderModules(
            $use_cache,
            dcCore::app()->blog->settings->system->store_plugin_url,
            __('Repository plugins list'),
            __('Plugin ID')
        );
    }

    /**
     * Return list of available themes
     *
     * @param      bool    $use_cache  Use cache if available
     *
     * @return     string
     */
    public static function renderThemes(bool $use_cache = false): string
    {
        return self::renderModules(
            $use_cache,
            dcCore::app()->blog->settings->system->store_theme_url,
            __('Repository themes list'),
            __('Theme ID')
        );
    }

    /**
     * Return list of available plugins from alternate repositories
     *
     * @return     string
     */
    public static function renderAltPlugins(): string
    {
        $plugins = dcCore::app()->plugins->getDefines();
        uasort($plugins, fn ($a, $b) => strtolower($a->getId()) <=> strtolower($b->getId()));

        return self::renderAltModules(
            $plugins,
            true,
            __('Repository plugins list (alternate repositories)')
        );
    }

    /**
     * Return list of available themes from alternate repositories
     *
     * @return     string
     */
    public static function renderAltThemes(): string
    {
        if (!(dcCore::app()->themes instanceof dcThemes)) {
            dcCore::app()->themes = new dcThemes();
            dcCore::app()->themes->loadModules((string) dcCore::app()->blog?->themes_path, null);
        }
        $themes = dcCore::app()->themes->getDefines();
        uasort($themes, fn ($a, $b) => strtolower($a->getId()) <=> strtolower($b->getId()));

        return self::renderAltModules(
            $themes,
            true,
            __('Repository themes list (alternate repositories)')
        );
    }
}
