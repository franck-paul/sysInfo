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
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Plugin\sysInfo\CoreHelper;

class Integrity
{
    /**
     * Check Dotclear digest integrity
     */
    public static function render(): string
    {
        $digests_file = implode(DIRECTORY_SEPARATOR, [App::config()->dotclearRoot(), 'inc', 'digests']);

        $rows = function () use ($digests_file) {
            if (is_readable($digests_file)) {
                $opts     = FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES;
                $contents = file($digests_file, $opts);
                $count    = 0;

                if ($contents !== false) {
                    foreach ($contents as $digest) {
                        if (!preg_match('#^([\da-f]{32})\s+(.+?)$#', $digest, $m)) {
                            continue;
                        }

                        $md5_in_digest = $m[1];
                        $filename      = App::config()->dotclearRoot() . '/' . $m[2];

                        $md5_standard        = '';
                        $md5_experimental    = '';
                        $status_standard     = '';
                        $status_experimental = '';

                        if (!is_readable($filename)) {
                            $md5_standard = __('Not readable');
                        } else {
                            // Direct
                            $md5_standard = md5_file($filename);

                            if ($md5_standard !== $md5_in_digest) {
                                // Remove EOL
                                $filecontent = (string) file_get_contents($filename);
                                $filecontent = str_replace("\r\n", "\n", $filecontent);
                                $filecontent = str_replace("\r", "\n", $filecontent);

                                $md5_standard    = md5($filecontent);
                                $status_standard = $md5_standard === $md5_in_digest ? '' : 'version-disabled';
                            }

                            // Experimental
                            // Remove EOL
                            $filecontent = (string) file_get_contents($filename);
                            $filecontent = preg_replace('/(*BSR_ANYCRLF)\R/', '\n', $filecontent);

                            if ($filecontent) {
                                $md5_experimental    = md5($filecontent);
                                $status_experimental = $md5_experimental === $md5_in_digest ? '' : 'version-disabled';
                            }
                        }

                        if ($status_standard !== '') {
                            ++$count;

                            yield (new Tr())
                                ->cols([
                                    (new Td())
                                        ->class('maximal')
                                        ->text(CoreHelper::simplifyFilename($filename, true)),
                                    (new Td())
                                        ->class('nowrap')
                                        ->text($md5_in_digest),
                                    (new Td())
                                        ->text($md5_standard)
                                        ->class(['nowrap', $status_standard]),
                                    (new Td())
                                        ->text($md5_experimental)
                                        ->class(['nowrap', $status_experimental]),
                                ]);
                        }
                    }

                    if ($count === 0) {
                        yield (new Tr())
                            ->cols([
                                (new Td())
                                    ->colspan(4)
                                    ->text(__('Everything is fine.')),
                            ]);
                    }
                } else {
                    yield (new Tr())
                        ->cols([
                            (new Td())
                                ->colspan(4)
                                ->text(__('Unable to read digests file.')),
                        ]);
                }
            } else {
                yield (new Tr())
                    ->cols([
                        (new Td())
                            ->colspan(4)
                            ->text(__('Unable to read digests file.')),
                    ]);
            }
        };

        return (new Table('integrity'))
            ->class('sysinfo')
            ->caption(new Caption(__('Dotclear digest integrity')))
            ->thead((new Thead())
                ->rows([
                    (new Tr())
                        ->cols([
                            (new Th())
                                ->scope('col')
                                ->text(__('File')),
                            (new Th())
                                ->scope('col')
                                ->text(__('digest')),
                            (new Th())
                                ->scope('col')
                                ->text(__('md5')),
                            (new Th())
                                ->scope('col')
                                ->text(__('md5 (experimental)')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([
                    ... $rows(),
                ]))
        ->render();
    }
}
