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
use Dotclear\Plugin\sysInfo\CoreHelper;

class Undigest
{
    /**
     * Check Dotclear un-digest (find PHP files not in digest)
     *
     * @return     string
     */
    public static function render(): string
    {
        $released       = [];
        $list_primary   = [];
        $list_secondary = [];
        $unattended     = [];
        $root           = App::config()->dotclearRoot();

        $folders = [
            'admin',
            'inc',
            'locales',
            'src',
        ];
        // Add distributed plugins
        foreach (explode(',', (string) App::Config()->distributedPlugins()) as $theme) {
            $folders[] = implode(DIRECTORY_SEPARATOR, ['plugins', $theme]);
        }
        // Add distributed themes
        foreach (explode(',', (string) App::Config()->distributedThemes()) as $theme) {
            $folders[] = implode(DIRECTORY_SEPARATOR, ['themes', $theme]);
        }

        $ignore_folders = [
            'vendor',
        ];

        // Primary extensions to find
        $ext_primary = [
            'php',
            'tpl',
        ];
        // Sub-extensions to ignore
        $ignore_ext_primary = [
            '.lang.php',
        ];

        // Secondary extensions to find
        $ext_secondary = [
            'css',
            'dat',
            'gif',
            'htaccess',
            'html',
            'ico',
            'in',
            'jpg',
            'js',
            'json',
            'md',
            'pdf',
            'png',
            'po',
            'pot',
            'scss',
            'svg',
            'txt',
            'woff2',
            'xml',
            'xsl',
        ];

        // Suffixes to find for each extensions
        $ext_suffixes = [
            '-OLD',
        ];

        $str = '<table id="undigest" class="sysinfo"><caption>' . __('Unexpected or additional files') . '</caption>' .
            '<thead>' .
            '<tr><th scope="col">' . __('File') . ' (' . implode(', ', $ext_primary) . ')' . '</th></tr>' .
            '</thead>' .
            '<tbody>';

        // Get list of files in digest
        $digests_file = implode(DIRECTORY_SEPARATOR, [App::config()->dotclearRoot(), 'inc', 'digests']);
        if (is_readable($digests_file)) {
            $contents = file($digests_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($contents !== false) {
                foreach ($contents as $digest) {
                    if (!preg_match('#^([\da-f]{32})\s+(.+?)$#', $digest, $m)) {
                        continue;
                    }
                    $released[] = Path::real(implode(DIRECTORY_SEPARATOR, [$root,$m[2]]));
                }
                if (count($released)) {
                    foreach ($folders as $folder) {
                        $list_primary = self::scanDir(
                            implode(DIRECTORY_SEPARATOR, [$root, $folder]),
                            $list_primary,
                            $ext_primary,
                            $ignore_folders,
                            $ignore_ext_primary,
                            $ext_suffixes
                        );
                        $list_secondary = self::scanDir(
                            implode(DIRECTORY_SEPARATOR, [$root, $folder]),
                            $list_secondary,
                            $ext_secondary,
                            $ignore_folders
                        );
                    }
                    if (count($list_primary)) {
                        foreach ($list_primary as $filename) {
                            if (!in_array($filename, $released)) {
                                $unattended[] = $filename;
                            }
                        }
                        if (count($unattended)) {
                            foreach ($unattended as $filename) {
                                $str .= '<tr><td>' . CoreHelper::simplifyFilename($filename) . '</td></tr>';
                            }
                        } else {
                            $str .= '<tr><td>' . __('Nothing unexpected or additional found.') . '</td></tr>';
                        }
                    }

                    // Second part
                    $unattended = [];
                    $str .= '<tr><th scope="col">' . __('File') . ' (' . implode(', ', $ext_secondary) . ')' . '</th></tr>';
                    if (count($list_secondary)) {
                        foreach ($list_secondary as $filename) {
                            if (!in_array($filename, $released)) {
                                $unattended[] = $filename;
                            }
                        }
                        if (count($unattended)) {
                            foreach ($unattended as $filename) {
                                $str .= '<tr><td>' . CoreHelper::simplifyFilename($filename) . '</td></tr>';
                            }
                        } else {
                            $str .= '<tr><td>' . __('Nothing unexpected or additional found.') . '</td></tr>';
                        }
                    }
                }
            } else {
                $str .= '<tr><td>' . __('Unable to read digests file.') . '</td></tr>';
            }
        } else {
            $str .= '<tr><td>' . __('Unable to read digests file.') . '</td></tr>';
        }

        return $str . '</tbody></table>';
    }

    /**
     * Scan recursively a directory and found only some files with specific extensions.
     *
     * @param   string              $path           The directory path to scan
     * @param   array<int,string>   $stack          The paths stack
     * @param   array<int,string>   $ext            The extensions to find
     * @param   array<int,string>   $ignore         The folders to ignore
     * @param   array<int,string>   $ignore_ext     The extensions to ignore
     * @param   array<int,string>   $suffixes       The suffixes to also find
     *
     * @return  array<int,string>   The paths stack
     */
    private static function scanDir(string $path, array $stack = [], array $ext = [], array $ignore = [], array $ignore_ext = [], array $suffixes = []): array
    {
        $path = Path::real($path);
        if ($path === false || !is_dir($path) || !is_readable($path)) {
            return [];
        }
        $files = Files::scandir($path);

        foreach ($files as $file) {
            // Ignore all hidden items (starting with a dot)
            if (str_starts_with($file, '.')) {
                continue;
            }
            if (in_array($file, $ignore)) {
                continue;
            }
            if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                $stack = self::scanDir($path . DIRECTORY_SEPARATOR . $file, $stack, $ext, $ignore, $ignore_ext, $suffixes);
            } else {
                $pathname = implode(DIRECTORY_SEPARATOR, [$path, $file]);
                $info     = pathinfo($pathname, PATHINFO_EXTENSION);

                if (in_array($info, $ext)) {
                    // Check if not in extensions to ignore list
                    $keep = true;
                    foreach ($ignore_ext as $needle) {
                        if (str_ends_with($file, $needle)) {
                            $keep = false;

                            break;
                        }
                    }
                    if ($keep) {
                        $stack[] = $pathname;
                    }
                } else {
                    // Check for suffixes
                    $keep = false;
                    foreach ($suffixes as $suffix) {
                        if (str_ends_with($file, $suffix)) {
                            $keep = true;

                            break;
                        }
                    }
                    if ($keep) {
                        $stack[] = $pathname;
                    }
                }
            }
        }

        return $stack;
    }
}
