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

class Permissions
{
    /**
     * Return list of registered permissions
     */
    public static function render(): string
    {
        $permissions = App::auth()->getPermissionsTypes();

        $rows = function () use ($permissions) {
            foreach ($permissions as $key => $value) {
                yield (new Tr())
                    ->cols([
                        (new Td())
                            ->class('nowrap')
                            ->text((string) $key),
                        (new Td())
                            ->class('maximal')
                            ->text(__((string) $value)),
                    ]);
            }
        };

        return (new Table('permissions'))
            ->class('sysinfo')
            ->caption(new Caption(__('Types of permission') . ' (' . sprintf('%d', count($permissions)) . ')'))
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
                                ->class('maximal')
                                ->text(__('Label')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([
                    ... $rows(),
                ]))
        ->render();
    }
}
