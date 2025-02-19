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

use Autoloader;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Li;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Set;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Form\Ul;

class Autoload
{
    /**
     * Return autoloader infos
     */
    public static function render(): string
    {
        $loader     = Autoloader::me();
        $namespaces = array_keys($loader->getNamespaces());
        sort($namespaces);

        $lines = function () use ($namespaces) {
            foreach ($namespaces as $namespace) {
                yield (new Tr())
                    ->cols([
                        (new Td())
                            ->class('nowrap')
                            ->text($namespace),
                    ]);
            }
        };

        return (new Set())
            ->items([
                (new Note())
                    ->text(__('Properties:')),
                (new Ul())
                    ->items([
                        (new Li())
                            ->text(__('Root prefix:') . ' ' . ($loader->getRootPrefix() !== '' ? $loader->getRootPrefix() : __('Empty'))),
                        (new Li())
                            ->text(__('Root basedir:') . ' ' . ($loader->getRootBaseDir() !== '' ? $loader->getRootBaseDir() : __('Empty'))),
                    ]),
                (new Table('autoloads'))
                    ->class('sysinfo')
                    ->caption(new Caption(__('Namespaces') . ' (' . sprintf('%d', count($namespaces)) . ')'))
                    ->thead((new Thead())
                        ->rows([
                            (new Tr())
                                ->cols([
                                    (new Th())
                                        ->scope('col')
                                        ->class('nowrap')
                                        ->text(__('Name')),
                                ]),
                        ]))
                    ->tbody((new Tbody())
                        ->rows([
                            ... $lines(),
                        ])),
            ])
        ->render();
    }
}
