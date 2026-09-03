<?php

/**
 * @brief sysInfo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul contact@open-time.net
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
        $filters = Antispam::$filters->getFilters();

        $lines = function () use ($filters) {
            foreach ($filters as $filter) {
                yield (new Tr())
                    ->cols([
                        (new Td())
                            ->class('nowrap')
                            ->text($filter->id),
                        (new Td())
                            ->class('nowrap')
                            ->text($filter->name),
                        (new Td())
                            ->text($filter->hasGUI() ? __('yes') : __('no')),
                        (new Td())
                            ->class('maximal')
                            ->items([
                                (new Text('code', (string) $filter->guiURL())),
                            ]),
                    ]);
            }
        };

        return (new Table('antispams'))
            ->class('sysinfo')
            ->caption(new Caption(__('Antispam filters') . ' (' . sprintf('%d', count($filters)) . ')'))
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
