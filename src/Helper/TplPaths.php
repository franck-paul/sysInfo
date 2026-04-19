<?php

/**
 * @brief sysInfo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\sysInfo\Helper;

use Dotclear\App;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Set;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Plugin\sysInfo\CoreHelper;

class TplPaths
{
    /**
     * Return list of template paths
     */
    public static function render(): string
    {
        CoreHelper::publicPrepend();
        $paths = App::frontend()->template()->getPath();

        $document_root = is_string($document_root = $_SERVER['DOCUMENT_ROOT'] ?? '') ? $document_root : '';

        $rows = function () use ($paths, $document_root) {
            foreach ($paths as $path) {
                $sub_path = Path::real($path, false);
                if ($sub_path !== false) {
                    if (str_starts_with($sub_path, $document_root)) {
                        $sub_path = substr($sub_path, strlen($document_root));
                        if (str_starts_with($sub_path, '/')) {
                            $sub_path = substr($sub_path, 1);
                        }
                    } elseif (str_starts_with($sub_path, (string) App::config()->dotclearRoot())) {
                        $sub_path = substr($sub_path, strlen((string) App::config()->dotclearRoot()));
                        if (str_starts_with($sub_path, '/')) {
                            $sub_path = substr($sub_path, 1);
                        }
                    }

                    yield (new Tr())
                        ->cols([
                            (new Td())
                                ->text(CoreHelper::simplifyFilename($sub_path)),
                        ]);
                }
            }
        };

        return (new Set())
            ->items([
                (new Para())
                    ->items([
                        (new Link('sysinfo-preview'))
                            ->href(App::blog()->url() . App::url()->getURLFor('sysinfo') . '/templatetags')
                            ->text(__('Display template tags')),
                    ]),
                (new Table('tplpaths'))
                    ->class('sysinfo')
                    ->caption(new Caption(__('List of template paths') . ' (' . sprintf('%d', count($paths)) . ')'))
                    ->thead((new Thead())
                        ->rows([
                            (new Tr())
                                ->cols([
                                    (new Th())
                                        ->scope('col')
                                        ->text(__('Path')),
                                ]),
                        ]))
                    ->tbody((new Tbody())
                        ->rows($rows())),
            ])
        ->render();
    }
}
