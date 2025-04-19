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
use Dotclear\Helper\Html\Form\None;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Set;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;

class Thumbnails
{
    /**
     * Return list of known exceptions
     */
    public static function render(): string
    {
        $excluded_codes = ['sq', 't', 's', 'm', 'o'];

        // Récupération de la liste des tailles connues
        $list = App::media()->getThumbSizes();

        $thumbnails = function () use ($list, $excluded_codes) {
            foreach ($list as $name => $info) {
                $status = in_array($name, $excluded_codes) ?
                    (new Img('images/check-on.svg'))->class(['mark', 'mark-check-on']) :
                    (new None());
                yield (new Tr())
                    ->cols([
                        (new Td())
                            ->class('nowrap')
                            ->text($name),
                        (new Td())
                            ->items([
                                (new Text('code', (string) $info[0])),
                            ]),
                        (new Td())
                            ->items([
                                (new Text(null, (string) $info[1])),
                            ]),
                        (new Td())
                            ->items([
                                (new Text(null, (string) $info[2])),
                            ]),
                        (new Td())
                            ->items([
                                (new Text(null, (string) ($info[3] ?? '???'))),
                            ]),
                        (new Td())
                            ->items([
                                $status,
                            ]),
                    ]);
            }
        };

        return (new Set())
            ->items([
                (new Table('thumbnails'))
                    ->class('sysinfo')
                    ->caption(new Caption(__('Media thumbnails sizes') . ' (' . sprintf('%d', count($list)) . ')'))
                    ->thead((new Thead())
                        ->rows([
                            (new Tr())
                                ->cols([
                                    (new Th())
                                        ->scope('col')
                                        ->class('nowrap')
                                        ->text(__('Code')),
                                    (new Th())
                                        ->scope('col')
                                        ->text(__('Size')),
                                    (new Th())
                                        ->scope('col')
                                        ->text(__('Mode')),
                                    (new Th())
                                        ->scope('col')
                                        ->text(__('Translated label')),
                                    (new Th())
                                        ->scope('col')
                                        ->text(__('Label')),
                                    (new Th())
                                        ->scope('col')
                                        ->text(__('System')),
                                ]),
                        ]))
                    ->tbody((new Tbody())
                        ->rows([
                            ... $thumbnails(),
                        ])),
                (new Note())
                    ->text(sprintf(__('Thumbnails prefix: <code>%s</code>'), App::media()->getThumbnailPrefix())),
                (new Note())
                    ->text(sprintf(__('Thumbnails file pattern: <code>%s</code> (path, filename, thumbnail code)'), App::media()->getThumbnailFilePattern())),
            ])
        ->render();
    }
}
