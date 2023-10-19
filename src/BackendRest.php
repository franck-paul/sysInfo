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
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Html\Template\Template;
use Dotclear\Helper\Html\XmlTag;
use Dotclear\Helper\Network\Http;

class BackendRest
{
    /**
     * Gets the compiled template.
     *
     * @param      mixed                    $unused  The unused
     * @param      array<string, string>    $get     The get
     *
     * @return     XmlTag
     */
    public static function getCompiledTemplate(mixed $unused, array $get): XmlTag
    {
        // Return compiled template file content
        $file    = !empty($get['file']) ? $get['file'] : '';
        $rsp     = new XmlTag('sysinfo');
        $ret     = false;
        $content = '';

        if ($file != '') {
            // Load content of compiled template file (if exist and if is readable)
            $subpath  = sprintf('%s' . DIRECTORY_SEPARATOR . '%s', substr($file, 0, 2), substr($file, 2, 2));
            $fullpath = Path::real(DC_TPL_CACHE) . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER . DIRECTORY_SEPARATOR . $subpath . DIRECTORY_SEPARATOR . $file;
            if (file_exists($fullpath) && is_readable($fullpath)) {
                $content = (string) file_get_contents($fullpath);
                $ret     = true;
            }
        }

        $rsp->ret = $ret;
        // Escape file content (in order to avoid further parsing error)
        // JSON encode to preserve UTF-8 encoding
        // Base 64 encoding to preserve line breaks
        $rsp->msg = base64_encode(json_encode(Html::escapeHTML($content), JSON_THROW_ON_ERROR));

        return $rsp;
    }

    /**
     * Gets the static cache dir.
     *
     * @param      mixed                    $unused  The unused
     * @param      array<string, string>    $get     The get
     *
     * @return     XmlTag
     */
    public static function getStaticCacheDir(mixed $unused, array $get): XmlTag
    {
        // Return list of folders in a given cache folder
        $root    = !empty($get['root']) ? $get['root'] : '';
        $rsp     = new XmlTag('sysinfo');
        $ret     = false;
        $content = '';
        $pattern = implode(DIRECTORY_SEPARATOR, array_fill(0, 5, '%s'));

        if ($root != '') {
            $blog_host = App::blog()->host();
            if (substr($blog_host, -1) != '/') {
                $blog_host .= '/';
            }
            $cache_dir = Path::real(DC_SC_CACHE_DIR, false);
            $cache_key = md5(Http::getHostFromURL($blog_host));

            $k         = str_split($cache_key, 2);
            $cache_dir = sprintf($pattern, $cache_dir, $k[0], $k[1], $k[2], $cache_key);

            if (is_dir($cache_dir) && is_readable($cache_dir)) {
                $files = Files::scandir($cache_dir . DIRECTORY_SEPARATOR . $root);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && $file !== 'mtime') {
                        $cache_fullpath = $cache_dir . DIRECTORY_SEPARATOR . $root . DIRECTORY_SEPARATOR . $file;
                        if (is_dir($cache_fullpath)) {
                            $content .= '<tr>' .
                            '<td class="nowrap">' . $root . '</td>' . // 1st level
                            '<td class="nowrap">' .
                            '<a class="sc_subdir" href="#">' . $file . '</a>' .
                            '</td>' .                                     // 2nd level
                            '<td class="nowrap">' . __('…') . '</td>' . // 3rd level
                            '<td class="nowrap maximal"></td>' .          // cache file
                            '</tr>' . "\n";
                        }
                    }
                }
                $ret = true;
            }
        }

        $rsp->ret = $ret;
        $rsp->msg = $content;

        return $rsp;
    }

    /**
     * Gets the static cache list.
     *
     * @param      mixed                    $unused  The unused
     * @param      array<string, string>    $get     The get
     *
     * @return     XmlTag
     */
    public static function getStaticCacheList(mixed $unused, array $get): XmlTag
    {
        // Return list of folders and files in a given folder
        $root    = !empty($get['root']) ? $get['root'] : '';
        $rsp     = new XmlTag('sysinfo');
        $ret     = false;
        $content = '';
        $pattern = implode(DIRECTORY_SEPARATOR, array_fill(0, 5, '%s'));

        if ($root != '') {
            $blog_host = App::blog()->host();
            if (substr($blog_host, -1) != '/') {
                $blog_host .= '/';
            }
            $cache_dir = Path::real(DC_SC_CACHE_DIR, false);
            $cache_key = md5(Http::getHostFromURL($blog_host));

            if ($cache_dir !== false && is_dir($cache_dir) && is_readable($cache_dir)) {
                $k         = str_split($cache_key, 2);
                $cache_dir = sprintf($pattern, $cache_dir, $k[0], $k[1], $k[2], $cache_key);

                $dirs = [$cache_dir . DIRECTORY_SEPARATOR . $root];
                do {
                    $dir   = array_shift($dirs);
                    $files = Files::scandir($dir);
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..' && $file !== 'mtime') {
                            $cache_fullpath = $dir . DIRECTORY_SEPARATOR . $file;
                            if (is_file($cache_fullpath)) {
                                $k = str_split($file, 2);
                                $content .= '<tr>' .
                                '<td class="nowrap">' . $k[0] . '</td>' . // 1st level
                                '<td class="nowrap">' . $k[1] . '</td>' . // 2nd level
                                '<td class="nowrap">' . $k[2] . '</td>' . // 3rd level
                                '<td class="nowrap maximal">' .
                                (new Checkbox(['sc[]'], false))->value($cache_fullpath)->render() . ' ' .
                                '<label class="classic">' .
                                '<a class="sc_compiled" href="#" data-file="' . $cache_fullpath . '">' . $file . '</a>' .
                                '</label>' .
                                '</td>' . // cache file
                                '</tr>' . "\n";
                            } else {
                                $dirs[] = $dir . DIRECTORY_SEPARATOR . $file;
                            }
                        }
                    }
                } while (count($dirs));
                if ($content == '') {
                    // No more dirs and files → send an empty raw
                    $k = explode(DIRECTORY_SEPARATOR, $root);
                    $content .= '<tr>' .
                    '<td class="nowrap">' . $k[0] . '</td>' .         // 1st level
                    '<td class="nowrap">' . $k[1] . '</td>' .         // 2nd level
                    '<td class="nowrap">' . __('(empty)') . '</td>' . // 3rd level (empty)
                    '<td class="nowrap maximal"></td>' .              // cache file (empty)
                    '</tr>' . "\n";
                }
                $ret = true;
            }
        }

        $rsp->ret = $ret;
        $rsp->msg = $content;

        return $rsp;
    }

    /**
     * Gets the static cache name.
     *
     * @param      mixed                    $unused  The unused
     * @param      array<string, string>    $get     The get
     *
     * @return     XmlTag
     */
    public static function getStaticCacheName(mixed $unused, array $get): XmlTag
    {
        // Return static cache filename from a given URL
        $url     = !empty($get['url']) ? $get['url'] : '';
        $rsp     = new XmlTag('sysinfo');
        $ret     = false;
        $content = '';

        // Extract REQUEST_URI from URL if possible
        $blog_host = App::blog()->host();
        if (substr($url, 0, strlen($blog_host)) == $blog_host) {
            $url = substr($url, strlen($blog_host));
        }

        if ($url != '') {
            $content = md5($url);
            $ret     = true;
        }

        $rsp->ret = $ret;
        $rsp->msg = $content;

        return $rsp;
    }

    /**
     * Gets the static cache file.
     *
     * @param      mixed                    $unused  The unused
     * @param      array<string, string>    $get     The get
     *
     * @return     XmlTag
     */
    public static function getStaticCacheFile(mixed $unused, array $get): XmlTag
    {
        // Return compiled static cache file content
        $file    = !empty($get['file']) ? $get['file'] : '';
        $rsp     = new XmlTag('sysinfo');
        $ret     = false;
        $content = '';

        if ($file != '' && file_exists($file) && is_readable($file)) {
            $content = (string) file_get_contents($file);
            $ret     = true;
        }

        $rsp->ret = $ret;
        // Escape file content (in order to avoid further parsing error)
        // JSON encode to preserve UTF-8 encoding
        // Base 64 encoding to preserve line breaks
        $rsp->msg = base64_encode(json_encode(Html::escapeHTML($content), JSON_THROW_ON_ERROR));

        return $rsp;
    }
}
