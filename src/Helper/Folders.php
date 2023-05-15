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
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Template\Template;
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
            'root'   => DC_ROOT,
            'config' => DC_RC_PATH,
            'cache'  => [
                DC_TPL_CACHE,
                DC_TPL_CACHE . DIRECTORY_SEPARATOR . 'cbfeed',
                DC_TPL_CACHE . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER,
                DC_TPL_CACHE . DIRECTORY_SEPARATOR . 'dcrepo',
                DC_TPL_CACHE . DIRECTORY_SEPARATOR . 'versions',
            ],
            'digest'  => DC_DIGESTS,
            'l10n'    => DC_L10N_ROOT,
            'plugins' => explode(PATH_SEPARATOR, DC_PLUGINS_ROOT),
            'public'  => dcCore::app()->blog->public_path,
            'themes'  => dcCore::app()->blog->themes_path,
            'var'     => DC_VAR,
        ];

        if (defined('DC_SC_CACHE_DIR')) {
            $folders += ['static' => DC_SC_CACHE_DIR];
        }

        $str = '<table id="urls" class="sysinfo"><caption>' . __('Dotclear folders and files') . '</caption>' .
            '<thead><tr><th scope="col" class="nowrap">' . __('Name') . '</th>' .
            '<th scope="col">' . __('Path') . '</th>' .
            '<th scope="col" class="maximal">' . __('Status') . '</th></tr></thead>' .
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
                    '<img src="images/check-on.png" alt="" /> ' . __('Writable') :
                    '<img src="images/check-wrn.png" alt="" /> ' . __('Readonly');
                } else {
                    $status = '<img src="images/check-off.png" alt="" /> ' . __('Unknown');
                }
                if ($err !== '') {
                    $status .= '<div style="display: none;"><p>' . $err . '</p></div>';
                }

                if (substr($folder, 0, strlen(DC_ROOT)) === DC_ROOT) {
                    $folder = substr_replace($folder, '<code>DC_ROOT</code> ', 0, strlen(DC_ROOT));
                }

                $str .= '<tr>' .
                '<td class="nowrap">' . $name . '</td>' .
                '<td class="maximal">' . $folder . '</td>' .
                '<td class="nowrap">' . $status . '</td>' .
                '</tr>';

                $name = '';     // Avoid repeating it if multiple lines
            }
        }

        $str .= '</tbody>' .
            '</table>';

        return $str;
    }
}
