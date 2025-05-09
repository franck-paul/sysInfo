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
use Dotclear\Plugin\sysInfo\CoreHelper;

class Rest
{
    /**
     * Return list of REST methods
     */
    public static function render(): string
    {
        /**
         * @var        \Dotclear\Helper\RestServer
         */
        $rest    = App::rest();
        $methods = $rest->functions;

        App::lexical()->lexicalKeySort($methods, App::lexical()::ADMIN_LOCALE);

        $rows = function ($methods) {
            foreach ($methods as $method => $callback) {
                yield (new Tr())
                    ->cols([
                        (new Td())
                            ->class('nowrap')
                            ->text($method),
                        (new Td())
                            ->class('maximal')
                            ->items([
                                (new Text('code', CoreHelper::callableName($callback))),
                            ]),
                    ]);
            }
        };

        return (new Table('restmethods'))
            ->class('sysinfo')
            ->caption(new Caption(__('REST methods') . ' (' . sprintf('%d', count($methods)) . ')'))
            ->thead((new Thead())
                ->rows([
                    (new Tr())
                        ->cols([
                            (new Th())
                                ->scope('col')
                                ->class('nowrap')
                                ->text(__('Method')),
                            (new Th())
                                ->scope('col')
                                ->class('maximal')
                                ->text(__('Callback')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([... $rows($methods)]))
        ->render();
    }
}
