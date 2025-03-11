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
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;

class PostTypes
{
    /**
     * Return list of entry types
     */
    public static function render(): string
    {
        $lines = function () {
            $types = App::postTypes()->dump();
            foreach (array_keys($types) as $type) {
                yield (new Tr())
                    ->items([
                        (new Td())
                            ->text($type),
                        (new Td())
                            ->items([
                                App::postTypes()->image($type),
                            ]),
                        (new Td())
                            ->text(__(App::postTypes()->get($type)->get('label'))),
                        (new Td())
                            ->items([
                                (new Text('code', App::postTypes()->get($type)->get('admin_url'))),
                            ]),
                        (new Td())
                            ->items([
                                (new Text('code', App::postTypes()->get($type)->get('list_admin_url'))),
                            ]),
                        (new Td())
                            ->items([
                                (new Text('code', App::postTypes()->get($type)->get('public_url'))),
                            ]),
                    ]);
            }
        };

        return (new Table('posttypes'))
            ->class('sysinfo')
            ->caption(new Caption(__('Entry types')))
            ->thead((new Thead())
                ->rows([
                    (new Th())
                        ->scope('col')
                        ->text(__('Type')),
                    (new Th())
                        ->scope('col')
                        ->text(__('Icon')),
                    (new Th())
                        ->scope('col')
                        ->text(__('Name')),
                    (new Th())
                        ->scope('col')
                        ->text(__('Admin URL')),
                    (new Th())
                        ->scope('col')
                        ->text(__('List admin URL')),
                    (new Th())
                        ->scope('col')
                        ->text(__('Public URL')),
                ]))
            ->tbody((new Tbody())
                ->rows([
                    ... $lines(),
                ]))
        ->render();
    }
}
