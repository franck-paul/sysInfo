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

use Dotclear\Plugin\sysInfo\CoreHelper;

class PhpInfo
{
    /**
     * Return PHP info
     *
     * @return     string
     */
    public static function render(): string
    {
        ob_start();
        phpinfo(INFO_GENERAL + INFO_CONFIGURATION + INFO_MODULES + INFO_ENVIRONMENT + INFO_VARIABLES);
        $phpinfo = ['phpinfo' => []];
        if (preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', (string) ob_get_clean(), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $keys = array_keys($phpinfo);
                if (strlen($match[1] ?? '') !== 0) {
                    $phpinfo[$match[1]] = [];
                } elseif (isset($match[3])) {
                    @$phpinfo[end($keys)][$match[2] ?? ''] = isset($match[4]) ? [$match[3], $match[4]] : $match[3];
                } else {
                    @$phpinfo[end($keys)][] = $match[2] ?? '';
                }
            }
        }

        $str = '';
        foreach ($phpinfo as $name => $section) {
            $str .= "<h3>{$name}</h3>\n<table class=\"sysinfo\">\n";
            foreach ($section as $key => $val) {
                if (is_array($val)) {
                    $str .= "<tr><td>{$key}</td><td>$val[0]</td><td>$val[1]</td></tr>\n";
                } elseif (is_string($key)) {
                    $str .= sprintf('<tr><td>%s</td><td>', $key) . CoreHelper::simplifyFilename($val) . "</td></tr>\n";
                } else {
                    $str .= '<tr><td>' . CoreHelper::simplifyFilename($val) . "</td></tr>\n";
                }
            }

            $str .= "</table>\n";
        }

        return $str;
    }
}
