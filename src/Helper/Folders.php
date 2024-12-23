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
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Template\Template;
use Dotclear\Plugin\sysInfo\CoreHelper;
use Exception;

class Folders
{
    /**
     * Check generic Dotclear folders
     *
     * @return     string
     */
    public static function render(): string
    {
        // Check generic Dotclear folders
        $folders = [
            'root'   => App::config()->dotclearRoot(),
            'config' => App::config()->configPath(),
            'cache'  => [
                App::config()->cacheRoot(),
                App::config()->cacheRoot() . DIRECTORY_SEPARATOR . 'cbfeed',
                App::config()->cacheRoot() . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER,
                App::config()->cacheRoot() . DIRECTORY_SEPARATOR . 'dcrepo',
                App::config()->cacheRoot() . DIRECTORY_SEPARATOR . 'versions',
            ],
            'digest'  => App::config()->digestsRoot(),
            'l10n'    => App::config()->l10nRoot(),
            'plugins' => explode(PATH_SEPARATOR, (string) App::config()->pluginsRoot()),
            'public'  => App::blog()->publicPath(),
            'themes'  => App::blog()->themesPath(),
            'var'     => App::config()->varRoot(),
        ];

        if (defined('DC_SC_CACHE_DIR')) {
            $folders += ['static' => DC_SC_CACHE_DIR];
        }

        $str = '<table id="folders" class="sysinfo"><caption>' . __('Dotclear folders and files') . '</caption>' .
            '<thead><tr><th scope="col" class="nowrap">' . __('Name') . '</th>' .
            '<th scope="col" class="maximal">' . __('Path') . '</th>' .
            '<th scope="col" class="nowrap">' . __('Status') . '</th></tr></thead>' .
            '<tbody>';

        foreach ($folders as $name => $subfolder) {
            if (!is_array($subfolder)) {
                $subfolder = [$subfolder];
            }

            foreach ($subfolder as $folder) {
                $err = '';
                if ($path = Path::real($folder)) {
                    $writable = is_writable($path);
                    $touch    = true;
                    if ($writable && is_dir($path)) {
                        // Try to create a file, inherit dir perms and then delete it
                        $void = '';

                        try {
                            $void  = $path . (substr($path, -1) === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR) . 'tmp-' . str_shuffle(MD5(microtime()));
                            $touch = false;
                            Files::putContent($void, '');
                            if (file_exists($void)) {
                                Files::inheritChmod($void);
                                unlink($void);
                                $touch = true;
                            }
                        } catch (Exception $e) {
                            $err = $void . ' : ' . $e->getMessage();
                        }
                    }

                    $status = $writable && $touch ?
                    '<img class="mark mark-check-on" src="images/check-on.svg" alt=""> ' . __('Writable') :
                    '<img class="mark mark-check-wrn" src="images/check-wrn.svg" alt=""> ' . __('Readonly');
                } else {
                    $status = '<img class="mark mark-check-off" src="images/check-off.svg" alt=""> ' . __('Unknown');
                }

                if ($err !== '') {
                    $status .= '<div style="display: none;"><p>' . $err . '</p></div>';
                }

                if (str_starts_with((string) $folder, (string) App::config()->dotclearRoot())) {
                    $folder = substr_replace($folder, '<code>DC_ROOT</code> ', 0, strlen((string) App::config()->dotclearRoot()));
                }

                $str .= '<tr><td class="nowrap">' . $name . '</td>' .
                '<td class="maximal">' . CoreHelper::simplifyFilename($folder) . '</td>' .
                '<td class="nowrap">' . $status . '</td>' .
                '</tr>';

                $name = '';     // Avoid repeating it if multiple lines
            }
        }

        return $str . '</tbody></table>';
    }
}
