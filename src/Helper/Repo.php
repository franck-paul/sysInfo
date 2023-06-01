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
use dcStoreReader;
use dcUtils;
use Dotclear\Helper\File\Path;

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
        $cache_path = Path::real(DC_TPL_CACHE);
        $xml_url    = $url;
        $in_cache   = false;

        if ($use_cache) {
            // Get XML cache file for modules
            $ser_file = sprintf(
                '%s/%s/%s/%s/%s.ser',
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
        $parser = dcStoreReader::quickParse($xml_url, DC_TPL_CACHE, !$in_cache);

        $defines   = !$parser ? [] : $parser->getDefines();     // @phpstan-ignore-line
        $raw_datas = [];
        foreach ($defines as $define) {
            $raw_datas[$define->getId()] = $define;
        }
        dcUtils::lexicalKeySort($raw_datas, dcUtils::ADMIN_LOCALE);
        $count = $parser ? ' (' . sprintf('%d', count($raw_datas)) . ')' : '';

        $str = '<h3>' . $title . __(' from: ') . ($in_cache ? __('cache') : $xml_url) . $count . '</h3>';
        if (!$parser) {     // @phpstan-ignore-line
            $str .= '<p>' . __('Repository is unreachable') . '</p>';
        } else {
            $str .= '<details id="expand-all"><summary>' . $label . '</summary></details>';
            $url_fmt = '<a href="%1$s">%1$s</a>';
            foreach ($raw_datas as $id => $define) {
                $infos = $define->dump();
                $str .= '<details><summary>' . $id . '</summary>';
                $str .= '<ul>';
                foreach ($infos as $key => $value) {
                    if (in_array($key, ['file', 'details', 'support', 'sshot'])) {
                        $val = $value ? sprintf($url_fmt, $value) : $value;
                    } else {
                        $val = is_array($value) ? var_export($value, true) : $value;
                    }
                    $str .= '<li>' . $key . ' = ' . $val . '</li>';
                }
                $str .= '</ul>';
                $str .= '</details>';
            }
        }

        return $str;
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
}
