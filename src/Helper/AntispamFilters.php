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

use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Plugin\antispam\Antispam;

class AntispamFilters
{
    /**
     * Return list of antispam filters
     */
    public static function render(): string
    {
        // Get antispam filters
        Antispam::initFilters();
        $fs = Antispam::$filters->getFilters();

        $lines = function () use ($fs) {
            foreach ($fs as $f) {
                yield (new Tr())
                    ->cols([
                        (new Td())
                            ->class('nowrap')
                            ->text($f->id),
                        (new Td())
                            ->class('nowrap')
                            ->text($f->name),
                        (new Td())
                            ->text($f->hasGUI() ? __('yes') : __('no')),
                        (new Td())
                            ->class('maximal')
                            ->items([
                                (new Text('code', (string) $f->guiURL())),
                            ]),
                    ]);
            }
        };

        return (new Table('antispams'))
            ->class('sysinfo')
            ->caption(new Caption(__('Antispam filters') . ' (' . sprintf('%d', count($fs)) . ')'))
            ->thead((new Thead())
                ->rows([
                    (new Tr())
                        ->cols([
                            (new Th())
                                ->scope('col')
                                ->class('nowrap')
                                ->text(__('ID')),
                            (new Th())
                                ->scope('col')
                                ->class('nowrap')
                                ->text(__('Name')),
                            (new Th())
                                ->scope('col')
                                ->text(__('GUI')),
                            (new Th())
                                ->scope('col')
                                ->class('maximal')
                                ->text(__('URL')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([
                    ... $lines(),
                ]))
        ->render();
    }
}
