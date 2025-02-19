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

class Exceptions
{
    /**
     * Return list of known exceptions
     */
    public static function render(): string
    {
        // Récupération de la liste des exceptions connues
        $list = [];
        foreach (\Dotclear\Exception\ExceptionEnum::cases() as $enum) {
            $list[$enum->name] = [
                'value' => $enum->value,
                'code'  => $enum->code(),
                'label' => $enum->label(),
            ];
        }

        App::lexical()->lexicalKeySort($list, App::lexical()::ADMIN_LOCALE);

        $exceptions = function () use ($list) {
            foreach ($list as $name => $info) {
                yield (new Tr())
                    ->cols([
                        (new Td())
                            ->class('nowrap')
                            ->text($name),
                        (new Td())
                            ->items([
                                (new Text('code', (string) $info['value'])),
                            ]),
                        (new Td())
                            ->items([
                                (new Text('code', (string) $info['code'])),
                            ]),
                        (new Td())
                            ->items([
                                (new Text('code', (string) $info['label'])),
                            ]),
                    ]);
            }
        };

        return (new Table('exceptions'))
            ->class('sysinfo')
            ->caption(new Caption(__('Registered Exceptions') . ' (' . sprintf('%d', count($list)) . ')'))
            ->thead((new Thead())
                ->rows([
                    (new Tr())
                        ->cols([
                            (new Th())
                                ->scope('col')
                                ->class('nowrap')
                                ->text(__('Name')),
                            (new Th())
                                ->scope('col')
                                ->text(__('Value')),
                            (new Th())
                                ->scope('col')
                                ->text(__('Code')),
                            (new Th())
                                ->scope('col')
                                ->text(__('Label')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([
                    ... $exceptions(),
                ]))
        ->render();
    }
}
