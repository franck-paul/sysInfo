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

class UrlHandlers
{
    /**
     * Return list of registered URLs
     */
    public static function render(): string
    {
        // Récupération des types d'URL enregistrées
        $url_handlers = App::url()->getTypes();

        /**
         * Tables des URLs non gérées par le menu
         * Ex: ['xmlrpc','preview','trackback','feed','spamfeed','hamfeed','pagespreview','tag_feed']
         *
         * @var        array<int, string>
         */
        $url_excluded = [];

        $url_handlers_rows = function () use ($url_handlers, $url_excluded) {
            // Default handler (home)
            yield (new Tr())
                ->cols([
                    (new Td())
                        ->text('home'),
                    (new Td()),
                    (new Td())
                        ->items([
                            (new Text('code', '^$')),
                        ]),
                    (new Td())
                        ->items([
                            (new Text('code', '(default)')),
                        ]),
                ]);
            // Other URLs handler
            foreach ($url_handlers as $url_handler_name => $url_handler_parameters) {
                if (!in_array($url_handler_name, $url_excluded)) {
                    yield (new Tr())
                        ->cols([
                            (new Td())
                                ->text($url_handler_name),
                            (new Td())
                                ->text($url_handler_parameters['url']),
                            (new Td())
                                ->items([
                                    (new Text('code', $url_handler_parameters['representation'])),
                                ]),
                            (new Td())
                                ->items([
                                    (new Text('code', CoreHelper::callableName($url_handler_parameters['handler']))),
                                ]),
                        ]);
                }
            }
        };

        return (new Table('urls'))
            ->class('sysinfo')
            ->caption(new Caption(__('List of known URLs') . ' (' . sprintf('%d', count($url_handlers)) . ')'))
            ->thead((new Thead())
                ->rows([
                    (new Th())
                        ->scope('col')
                        ->text(__('Type')),
                    (new Th())
                        ->scope('col')
                        ->text(__('base URL')),
                    (new Th())
                        ->scope('col')
                        ->text(__('Regular expression')),
                    (new Th())
                        ->scope('col')
                        ->text(__('Callback')),
                ]))
            ->tbody((new Tbody())
                ->rows($url_handlers_rows()))
        ->render();
    }
}
