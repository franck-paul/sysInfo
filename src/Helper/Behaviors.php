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
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Set;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Plugin\sysInfo\CoreHelper;

class Behaviors
{
    /**
     * Return list of registered behaviours
     */
    public static function render(): string
    {
        // Affichage de la liste des behaviours inscrits
        $behaviorsList = App::behavior()->getBehaviors();
        App::lexical()->lexicalKeySort($behaviorsList, App::lexical()::ADMIN_LOCALE);

        $behaviourLines = function () use ($behaviorsList) {
            foreach ($behaviorsList as $behaviorName => $behaviorCallback) {
                if (is_array($behaviorCallback)) {
                    $first = true;
                    foreach ($behaviorCallback as $callback) {
                        yield (new Tr())
                            ->cols([
                                (new Td())
                                    ->class('nowrap')
                                    ->text($first ? $behaviorName : ''),
                                (new Td())
                                    ->class('maximal')
                                    ->items([
                                        (new Text('code', CoreHelper::callableName($callback))),
                                    ]),
                            ]);
                        $first = false;
                    }
                } else {
                    yield (new Tr())
                        ->cols([
                            (new Td())
                                ->class('nowrap')
                                ->text($behaviorName),
                            (new Td())
                                ->class('maximal')
                                ->items([
                                    (new Text('code', $behaviorCallback . '()')),
                                ]),
                        ]);
                }
            }
        };

        return (new Set())
            ->items([
                (new Para())
                    ->items([
                        (new Link('sysinfo-preview'))
                            ->href(App::blog()->url() . App::url()->getURLFor('sysinfo') . '/behaviours')
                            ->text(__('Display public behaviours')),
                    ]),
                (new Table('behaviours'))
                    ->class('sysinfo')
                    ->caption(new Caption(__('Behaviours list') . ' (' . sprintf('%d', count($behaviorsList)) . ')'))
                    ->thead((new Thead())
                        ->rows([
                            (new Tr())
                                ->cols([
                                    (new Th())
                                        ->scope('col')
                                        ->class('nowrap')
                                        ->text(__('Behavior')),
                                    (new Th())
                                        ->scope('col')
                                        ->class('maximal')
                                        ->text(__('Callback')),
                                ]),
                        ]))
                    ->tbody((new Tbody())
                        ->rows([
                            ... $behaviourLines(),
                        ])),
            ])
        ->render();
    }
}
