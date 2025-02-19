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

use Dotclear\Helper\Html\Form\Set;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Plugin\sysInfo\CoreHelper;

class PhpInfo
{
    /**
     * Return PHP info
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

        $values = function ($section) {
            foreach ($section as $key => $val) {
                if (is_array($val)) {
                    yield (new Tr())
                        ->cols([
                            (new Td())
                                ->class('nowrap')
                                ->text((string) $key),
                            (new Td())
                                ->text((string) $val[0]),
                            (new Td())
                                ->text((string) $val[1]),
                        ]);
                } elseif (is_string($key)) {
                    yield (new Tr())
                        ->cols([
                            (new Td())
                                ->class('nowrap')
                                ->text($key),
                            (new Td())
                                ->colspan(2)
                                ->text(CoreHelper::simplifyFilename($val)),
                        ]);
                } else {
                    yield (new Tr())
                        ->cols([
                            (new Td())
                                ->colspan(3)
                                ->text(CoreHelper::simplifyFilename($val)),
                        ]);
                }
            }
        };

        $sections = function () use ($phpinfo, $values) {
            foreach ($phpinfo as $name => $section) {
                yield (new Set())
                    ->items([
                        (new Text('h3', $name)),
                        (new Table('phpinfo'))
                            ->class(['sysinfo'])
                            ->tbody((new Tbody())
                                ->rows([
                                    ... $values($section),
                                ])),
                    ]);
            }
        };

        return (new Set())
            ->items([
                ... $sections(),
            ])
        ->render();
    }
}
