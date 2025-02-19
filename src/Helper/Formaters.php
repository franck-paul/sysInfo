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

class Formaters
{
    /**
     * Return list of formaters (syntaxes coped by installed editors)
     */
    public static function render(): string
    {
        // Affichage de la liste des éditeurs et des syntaxes par éditeur
        $formaters = App::formater()->getFormaters();

        $rows = function () use ($formaters) {
            foreach ($formaters as $editor => $syntaxes) {
                $first = true;
                foreach ($syntaxes as $syntax) {
                    yield (new Tr())
                        ->cols([
                            (new Td())
                                ->class('nowrap')
                                ->text($first ? $editor : ''),
                            (new Td())
                                ->text($syntax),
                            (new Td())
                                ->class('maximal')
                                ->text(App::formater()->getFormaterName($syntax)),
                        ]);
                    $first = false;
                }
            }
        };

        return (new Table('formaters'))
            ->class('sysinfo')
            ->caption(new Caption(__('Editors and their supported syntaxes')))
            ->thead((new Thead())
                ->rows([
                    (new Tr())
                        ->cols([
                            (new Th())
                                ->scope('col')
                                ->class('nowrap')
                                ->text(__('Editor')),
                            (new Th())
                                ->scope('col')
                                ->text(__('Code')),
                            (new Th())
                                ->scope('col')
                                ->class('maximal')
                                ->text(__('Syntax')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([... $rows()]))
        ->render();
    }
}
