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
use Dotclear\Helper\Html\Form\Img;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Stack\Status;

class Statuses
{
    /**
     * Return list of statuses
     */
    public static function render(): string
    {
        $lines = [];

        $statuses = App::status()->blog()->dump(true);
        $type     = 'App::status()->blog()';
        foreach ($statuses as $status) {
            $lines[] = self::getRow($status, $type);
            if ($type !== '') {
                $type = '';
            }
        }

        $statuses = App::status()->user()->dump(true);
        $type     = 'App::status()->user()';
        foreach ($statuses as $status) {
            $lines[] = self::getRow($status, $type);
            if ($type !== '') {
                $type = '';
            }
        }

        $statuses = App::status()->post()->dump(true);
        $type     = 'App::status()->post()';
        foreach ($statuses as $status) {
            $lines[] = self::getRow($status, $type);
            if ($type !== '') {
                $type = '';
            }
        }

        $statuses = App::status()->comment()->dump(true);
        $type     = 'App::status()->comment()';
        foreach ($statuses as $status) {
            $lines[] = self::getRow($status, $type);
            if ($type !== '') {
                $type = '';
            }
        }

        // Affichage de la liste des status
        return (new Table('statuses'))
            ->class('sysinfo')
            ->caption(new Caption(__('Statuses')))
            ->thead((new Thead())
                ->rows([
                    (new Tr())
                        ->cols([
                            (new Th())
                                ->scope('col')
                                ->class('nowrap')
                                ->text(__('Type')),
                            (new Th())
                                ->scope('col')
                                ->text(__('ID')),
                            (new Th())
                                ->scope('col')
                                ->text(__('Value')),
                            (new Th())
                                ->scope('col')
                                ->class('maximal')
                                ->text(__('Name')),
                            (new Th())
                                ->scope('col')
                                ->text(__('Hidden')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows($lines))
        ->render();
    }

    protected static function getRow(Status $status, string $type = ''): Tr
    {
        return (new Tr())
            ->items([
                (new Td())
                    ->class('nowrap')
                    ->text($type),
                (new Td())
                    ->text($status->id()),
                (new Td())
                    ->class('right')
                    ->text((new Text('code', (string) $status->level()))->render()),
                (new Td())
                    ->class('maximal')
                    ->text($status->name()),
                (new Td())
                    ->items([
                        (new Img('images/' . ($status->hidden() ? 'check-on.svg' : 'check-off.svg')))
                            ->class(['mark', 'mark-' . ($status->hidden() ? 'check-on' : 'check-off')])
                            ->alt($status->hidden() ? 'true' : 'false'),
                    ]),
            ]);
    }
}
