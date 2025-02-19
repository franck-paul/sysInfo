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

class AdminUrls
{
    /**
     * Return list of admin registered URLs
     */
    public static function render(): string
    {
        // Récupération de la liste des URLs d'admin enregistrées
        $urls = App::backend()->url()->dumpUrls();
        $urls = $urls->getArrayCopy();
        App::lexical()->lexicalKeySort($urls, App::lexical()::ADMIN_LOCALE);

        $lines = function () use ($urls) {
            foreach ($urls as $name => $url) {
                yield (new Tr())
                    ->cols([
                        (new Td())
                            ->class('nowrap')
                            ->text($name),
                        (new Td())
                            ->items([
                                new Text('code', $url['url']),
                            ]),
                        (new Td())
                            ->class('maximal')
                            ->items([
                                new Text('code', http_build_query($url['qs'])),
                            ]),
                    ]);
            }
        };

        return (new Table('urls'))
            ->class('sysinfo')
            ->caption(new Caption(__('Admin registered URLs') . ' (' . sprintf('%d', count($urls)) . ')'))
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
                                ->text(__('URL')),
                            (new Th())
                                ->scope('col')
                                ->class('maximal')
                                ->text(__('Query string')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([
                    ... $lines(),
                ]))
        ->render();
    }
}
