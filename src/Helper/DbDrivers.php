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

class DbDrivers
{
    /**
     * Return list of statuses
     */
    public static function render(): string
    {
        $drivers = App::db()->combo();
        //$drivers = array_keys(App::db()->combo());

        $lines = function () use ($drivers) {
            foreach ($drivers as $driver => $id) {
                yield (new Tr())
                    ->items([
                        (new Td())
                            ->text($id),
                        (new Td())
                            ->text($driver),
                    ]);
            }
        };

        return (new Table('dbdrivers'))
            ->class('sysinfo')
            ->caption(new Caption(__('DB drivers') . ' (' . sprintf('%d', count($drivers)) . ')'))
            ->thead((new Thead())
                ->rows([
                    (new Tr())
                        ->cols([
                            (new Th())
                                ->scope('col')
                                ->text(__('Driver')),
                            (new Th())
                                ->scope('col')
                                ->text(__('Driver name')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([
                    ...$lines(),
                ]))
        ->render();
    }
}
