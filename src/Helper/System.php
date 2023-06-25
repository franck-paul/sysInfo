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
use Dotclear\Helper\File\Path;

class System
{
    /**
     * Return a quote and PHP and DB driver version
     *
     * @param   bool    $quote include quote
     *
     * @return     string
     */
    public static function render(bool $quote = true): string
    {
        // Display a quote and PHP and DB version
        $quotes = [
            __('Live long and prosper.'),
            __('To infinity and beyond.'),
            __('So long, and thanks for all the fish.'),
            __('Find a needle in a haystack.'),
            __('A clever person solves a problem. A wise person avoids it.'),
            __('I\'m sorry, Dave. I\'m afraid I can\'t do that.'),
            __('With great power there must also come great responsibility.'),
            __('It\'s great, we have to do it all over again!'),
            __('Have You Tried Turning It Off And On Again?'),
        ];
        $q = random_int(0, count($quotes) - 1);

        // Get cache info
        $caches = [];
        if (function_exists('\opcache_get_status') && is_array(\opcache_get_status())) {
            $caches[] = 'OPCache';
        }
        if (function_exists('\apcu_cache_info') && is_array(\apcu_cache_info())) {
            $caches[] = 'APC';
        }
        if (class_exists('\Memcache')) {
            $memcache     = new \Memcache();
            $isMemcacheOn = @$memcache->connect('localhost', 11211, 1);
            if ($isMemcacheOn) {
                $caches[] = 'Memcache';
            }
        }
        if (count($caches) === 0) {
            $caches[] = __('None');
        }

        $server = '';
        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            $info   = explode(' ', $_SERVER['SERVER_SOFTWARE']);
            $server = '<li>' . __('Server software: ') . ' <strong>' . $info[0] . '</strong></li>';
        }

        // Server info
        $server = ($quote ? '<blockquote class="sysinfo"><p>' . $quotes[$q] . '</p></blockquote>' : '') .
            '<details open><summary>' . __('System info') . '</summary>' .
            '<ul>' .
            $server .
            '<li>' . __('PHP Version: ') . ' <strong>' . phpversion() . '</strong></li>' .
            '<li>' .
                __('DB driver: ') . ' <strong>' . dcCore::app()->con->driver() . '</strong> ' .
                __('version') . ' <strong>' . dcCore::app()->con->version() . '</strong> ' .
                sprintf(__('using <strong>%s</strong> syntax'), dcCore::app()->con->syntax()) . '</li>' .
            '<li>' . __('Error reporting: ') . ' <strong>' . error_reporting() . '</strong>' . ' = ' . self::errorLevelToString(error_reporting(), ', ') . '</li>' .
            '<li>' . __('PHP Cache: ') . ' <strong>' . implode('</strong>, <strong>', $caches) . '</strong></li>' .
            '<li>' . __('Temporary folder: ') . ' <strong>' . sys_get_temp_dir() . '</strong></li>' .
            '<li>' . 'DIRECTORY_SEPARATOR :' . ' <strong><code>' . DIRECTORY_SEPARATOR . '</code></strong></li>' .
            '<li>' . 'PATH_SEPARATOR :' . ' <strong><code>' . PATH_SEPARATOR . '</code></strong></li>' .
            '</ul>' .
            '</details>';

        // Dotclear info
        $dotclear = '<details open><summary>' . __('Dotclear info') . '</summary>' .
            '<ul>' .
            '<li>' . __('Dotclear version: ') . '<strong>' . DC_VERSION . '</strong></li>' .
            '<li>' . __('Update channel: ') . '<strong>' . DC_UPDATE_VERSION . '</strong></li>' .
            '</ul>' .
            '</details>';

        // Update info

        $versions = '';
        $path     = Path::real(DC_TPL_CACHE . '/versions');
        if ($path && is_dir($path)) {
            $channels = ['stable', 'testing', 'unstable'];
            foreach ($channels as $channel) {
                $file = $path . '/dotclear-' . $channel;
                if (file_exists($file)) {
                    if ($content = @unserialize(@file_get_contents($file))) {
                        if (is_array($content)) {
                            $versions .= '<li>' . __('Channel: ') . '<strong>' . $channel . '</strong>' .
                                ' (' . date(DATE_ATOM, filemtime($file)) . ')' .
                                '<ul>' .
                                '<li>' . __('version: ') . '<strong>' . $content['version'] . '</strong></li>' .
                                '<li>' . __('href: ') . '<a href="' . $content['href'] . '">' . $content['href'] . '</a></li>' .
                                '<li>' . __('checksum: ') . ' <code>' . $content['checksum'] . '</code></li>' .
                                '<li>' . __('info: ') . '<a href="' . $content['info'] . '">' . $content['info'] . '</a></li>' .
                                '<li>' . __('PHP min: ') . '<strong>' . $content['php'] . '</strong></li>' .
                                (isset($content['warning']) ?
                                    '<li>' . __('Warning: ') . '<strong>' . ($content['warning'] ? __('Yes') : __('No')) . '</strong></li>' :
                                    '') .
                                '</ul>' .
                                '</li>';
                        }
                    }
                }
            }
        }
        if ($versions !== '') {
            $versions = '<details open><summary>' . __('Update info') . ' ' . __('(from versions cache)') . '</summary><ul>' . $versions . '</ul></details>';
        }

        $release      = '';
        $release_file = DC_ROOT . DIRECTORY_SEPARATOR . 'release.json';
        if (file_exists($release_file)) {
            // Add a section with the content of release.json file
            $content = json_decode(file_get_contents($release_file), true);
            foreach ($content as $key => $value) {
                if (is_array($value)) {
                    $release .= '<li>' . $key . ' = <ul>';
                    foreach ($value as $subkey => $subvalue) {
                        $release .= '<li>' . $subkey . ' = <strong>' . $subvalue . '</strong></li>';
                    }
                    $release .= '</ul></li>';
                } else {
                    $release .= '<li>' . $key . ' = <strong>' . $value . '</strong></li>';
                }
            }
            if ($release) {
                $release = '<details open><summary>' . __('Release info') . '</summary><ul>' . $release . '</ul></details>';
            }
        }

        return $server . $dotclear . $versions . $release;
    }

    /**
     * PHP error_reporting to string
     *
     * @param      int     $intval     The intval
     * @param      string  $separator  The separator
     *
     * @return     string
     */
    private static function errorLevelToString(int $intval, string $separator = ','): string
    {
        $errorlevels = [
            E_ALL               => 'E_ALL',
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
            E_DEPRECATED        => 'E_DEPRECATED',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_STRICT            => 'E_STRICT',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_NOTICE            => 'E_NOTICE',
            E_PARSE             => 'E_PARSE',
            E_WARNING           => 'E_WARNING',
            E_ERROR             => 'E_ERROR',
        ];
        $result = [];
        foreach ($errorlevels as $number => $name) {
            if (($intval & $number) === $number) {
                $result[] = $name;
            }
        }

        return implode($separator, $result);
    }
}
