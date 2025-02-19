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

namespace Dotclear\Plugin\sysInfo;

use Dotclear\App;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Html\Template\Template;
use Dotclear\Helper\Network\Http;

class BackendRest
{
    /**
     * Gets the compiled template. (JSON)
     *
     * @param      array<string, string>    $get     The get
     *
     * @return     array<string, mixed>
     */
    public static function getCompiledTemplate(array $get): array
    {
        // Return compiled template file content
        $file    = empty($get['file']) ? '' : $get['file'];
        $content = '';
        $payload = [
            'ret' => false,
        ];

        if ($file != '') {
            // Load content of compiled template file (if exist and if is readable)
            $subpath  = sprintf('%s' . DIRECTORY_SEPARATOR . '%s', substr($file, 0, 2), substr($file, 2, 2));
            $fullpath = Path::real(App::config()->cacheRoot()) . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER . DIRECTORY_SEPARATOR . $subpath . DIRECTORY_SEPARATOR . $file;
            if (file_exists($fullpath) && is_readable($fullpath)) {
                $content = (string) file_get_contents($fullpath);

                // Escape file content (in order to avoid further parsing error)
                // JSON encode to preserve UTF-8 encoding
                // Base 64 encoding to preserve line breaks
                $payload = [
                    'ret'  => true,
                    'html' => base64_encode(json_encode(Html::escapeHTML($content), JSON_THROW_ON_ERROR)),
                ];
            }
        }

        return $payload;
    }

    /**
     * Gets the static cache dir. (JSON)
     *
     * @param      array<string, string>    $get     The get
     *
     * @return     array<string, mixed>
     */
    public static function getStaticCacheDir(array $get): array
    {
        // Return list of folders in a given cache folder
        $root    = empty($get['root']) ? '' : $get['root'];
        $pattern = implode(DIRECTORY_SEPARATOR, array_fill(0, 5, '%s'));
        $payload = [
            'ret' => false,
        ];

        if (defined('DC_SC_CACHE_DIR') && $root != '') {
            $blog_host = App::blog()->host();
            if (!str_ends_with((string) $blog_host, '/')) {
                $blog_host .= '/';
            }

            $cache_dir = Path::real(DC_SC_CACHE_DIR, false);
            $cache_key = md5(Http::getHostFromURL($blog_host));
            $key_parts = str_split($cache_key, 2);
            $cache_dir = sprintf($pattern, $cache_dir, $key_parts[0], $key_parts[1], $key_parts[2], $cache_key);
            if (is_dir($cache_dir) && is_readable($cache_dir)) {
                $files = Files::scandir($cache_dir . DIRECTORY_SEPARATOR . $root);
                $lines = [];
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && $file !== 'mtime') {
                        $cache_fullpath = $cache_dir . DIRECTORY_SEPARATOR . $root . DIRECTORY_SEPARATOR . $file;
                        if (is_dir($cache_fullpath)) {
                            $lines[] = (new Tr())
                                ->cols([
                                    (new Td())      // 1st level
                                        ->class('nowrap')
                                        ->text($root),
                                    (new td())      // 2nd level
                                        ->class('nowrap')
                                        ->items([
                                            (new Link())
                                                ->class('sc_subdir')
                                                ->href('#')
                                                ->text($file),
                                        ]),
                                    (new Td())      // 3rd level
                                        ->class('nowrap')
                                        ->text(__('…')),
                                    (new Td())      // cache file
                                        ->class(['nowrap', 'maximal']),
                                ])
                            ->render();
                        }
                    }
                }

                $payload = [
                    'ret'  => true,
                    'html' => implode("\n", $lines),
                ];
            }
        }

        return $payload;
    }

    /**
     * Gets the static cache list. (JSON)
     *
     * @param      array<string, string>    $get     The get
     *
     * @return     array<string, mixed>
     */
    public static function getStaticCacheList(array $get): array
    {
        // Return list of folders and files in a given folder
        $root    = empty($get['root']) ? '' : $get['root'];
        $ret     = false;
        $lines   = [];
        $pattern = implode(DIRECTORY_SEPARATOR, array_fill(0, 5, '%s'));

        if (defined('DC_SC_CACHE_DIR') && $root != '') {
            $blog_host = App::blog()->host();
            if (!str_ends_with((string) $blog_host, '/')) {
                $blog_host .= '/';
            }

            $cache_dir = Path::real(DC_SC_CACHE_DIR, false);
            $cache_key = md5(Http::getHostFromURL($blog_host));
            if ($cache_dir !== false && is_dir($cache_dir) && is_readable($cache_dir)) {
                $key_parts = str_split($cache_key, 2);
                $cache_dir = sprintf($pattern, $cache_dir, $key_parts[0], $key_parts[1], $key_parts[2], $cache_key);

                $dirs = [$cache_dir . DIRECTORY_SEPARATOR . $root];
                do {
                    $dir   = array_shift($dirs);
                    $files = Files::scandir($dir);
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..' && $file !== 'mtime') {
                            $cache_fullpath = $dir . DIRECTORY_SEPARATOR . $file;
                            if (is_file($cache_fullpath)) {
                                $key_parts = str_split($file, 2);

                                // Compose preview link
                                $url = (new Link())
                                    ->class('sc_compiled')
                                    ->href('#')
                                    ->data(['file' => $cache_fullpath])
                                    ->text($file)
                                ->render();

                                $lines[] = (new Tr())
                                    ->cols([
                                        (new Td())      // 1st level
                                            ->class('nowrap')
                                            ->text($key_parts[0]),
                                        (new Td())      // 2nd level
                                            ->class('nowrap')
                                            ->text($key_parts[1]),
                                        (new Td())      // 3rd level
                                            ->class('nowrap')
                                            ->text($key_parts[2]),
                                        (new Td())      // cache file
                                            ->class(['nowrap', 'maximal'])
                                            ->items([
                                                (new Checkbox(['sc[]'], false))
                                                    ->value($cache_fullpath)
                                                    ->label(new Label($url, Label::IL_FT)),
                                            ]),
                                    ])
                                ->render();
                            } else {
                                $dirs[] = $dir . DIRECTORY_SEPARATOR . $file;
                            }
                        }
                    }
                } while (count($dirs));

                if ($lines === []) {
                    // No more dirs and files → send an empty raw
                    $key_parts = explode(DIRECTORY_SEPARATOR, $root);
                    $lines[]   = (new Tr())
                        ->cols([
                            (new Td())      // 1st level
                                ->class('nowrap')
                                ->text($key_parts[0]),
                            (new Td())      // 2nd level
                                ->class('nowrap')
                                ->text($key_parts[1]),
                            (new Td())      // 3rd level (empty)
                                ->class('nowrap')
                                ->text(__('(empty)')),
                            (new Td())      // cache file (empty)
                                ->class(['nowrap', 'maximal']),
                        ])
                    ->render();
                }

                $ret = true;
            }
        }

        return [
            'ret'  => $ret,
            'html' => implode("\n", $lines),
        ];
    }

    /**
     * Gets the static cache name. (JSON)
     *
     * @param      array<string, string>    $get     The get
     *
     * @return     array<string, mixed>
     */
    public static function getStaticCacheName(array $get): array
    {
        // Return static cache filename from a given URL
        $url     = empty($get['url']) ? '' : $get['url'];
        $ret     = false;
        $content = '';

        // Extract REQUEST_URI from URL if possible
        $blog_host = App::blog()->host();
        if (str_starts_with($url, (string) $blog_host)) {
            $url = substr($url, strlen((string) $blog_host));
        }

        if ($url != '') {
            $content = md5($url);
            $ret     = true;
        }

        return [
            'ret'  => $ret,
            'html' => $content,
        ];
    }

    /**
     * Gets the static cache file. (JSON)
     *
     * @param      array<string, string>    $get     The get
     *
     * @return     array<string, mixed>
     */
    public static function getStaticCacheFile(array $get): array
    {
        // Return compiled static cache file content
        $file    = empty($get['file']) ? '' : $get['file'];
        $ret     = false;
        $content = '';

        if ($file != '' && file_exists($file) && is_readable($file)) {
            $content = (string) file_get_contents($file);
            $ret     = true;
        }

        // Escape file content (in order to avoid further parsing error)
        // JSON encode to preserve UTF-8 encoding
        // Base 64 encoding to preserve line breaks
        return [
            'ret'  => $ret,
            'html' => base64_encode(json_encode(Html::escapeHTML($content), JSON_THROW_ON_ERROR)),
        ];
    }
}
