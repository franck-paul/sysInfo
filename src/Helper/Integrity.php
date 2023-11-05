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

class Integrity
{
    /**
     * Check Dotclear digest integrity
     *
     * @return     string
     */
    public static function render(): string
    {
        $str = '<table id="urls" class="sysinfo"><caption>' . __('Dotclear digest integrity') . '</caption>' .
            '<thead><tr>' .
            '<th scope="col">' . __('File') . '</th>' .
            '<th scope="col">' . __('digest') . '</th>' .
            '<th scope="col">' . __('md5') . '</th>' .
            '<th scope="col">' . __('md5 (experimental)') . '</th>' .
            '</tr></thead>' .
            '<tbody>';

        $digests_file = implode(DIRECTORY_SEPARATOR, [App::config()->dotclearRoot(), 'inc', 'digests']);
        if (is_readable($digests_file)) {
            $opts     = FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES;
            $contents = file($digests_file, $opts);
            $count    = 0;

            if ($contents !== false) {
                foreach ($contents as $digest) {
                    if (!preg_match('#^([\da-f]{32})\s+(.+?)$#', $digest, $m)) {
                        continue;
                    }

                    $md5      = $m[1];
                    $filename = App::config()->dotclearRoot() . '/' . $m[2];

                    $md5_std    = '';
                    $md5_exp    = '';
                    $std_status = '';
                    $exp_status = '';

                    if (!is_readable($filename)) {
                        $md5_std = __('Not readable');
                    } else {
                        // Direct
                        $md5_std = md5_file($filename);

                        if ($md5_std !== $md5) {
                            // Remove EOL
                            $filecontent = (string) file_get_contents($filename);
                            $filecontent = str_replace("\r\n", "\n", $filecontent);
                            $filecontent = str_replace("\r", "\n", $filecontent);

                            $md5_std    = md5($filecontent);
                            $std_status = $md5_std === $md5 ? '' : ' class="version-disabled"';
                        }

                        // Experimental
                        // Remove EOL
                        $filecontent = (string) file_get_contents($filename);
                        $filecontent = preg_replace('/(*BSR_ANYCRLF)\R/', '\n', $filecontent);

                        if ($filecontent) {
                            $md5_exp    = md5($filecontent);
                            $exp_status = $md5_exp === $md5 ? '' : ' class="version-disabled"';
                        }
                    }

                    if ($std_status !== '') {
                        ++$count;
                        $str .= '<tr><td class="maximal">' . CoreHelper::simplifyFilename($filename, true) . '</td>' .
                        '<td>' . $md5 . '</td>' .
                        '<td' . $std_status . '>' . $md5_std . '</td>' .
                        '<td' . $exp_status . '>' . $md5_exp . '</td>' .
                        '</tr>';
                    }
                }

                if ($count === 0) {
                    $str .= '<tr><td>' . __('Everything is fine.') . '</td></tr>';
                }
            } else {
                $str .= '<tr><td>' . __('Unable to read digests file.') . '</td></tr>';
            }
        } else {
            $str .= '<tr><td>' . __('Unable to read digests file.') . '</td></tr>';
        }

        return $str . '</tbody></table>';
    }
}
