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
use Dotclear\Helper\Html\Form\Li;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Form\Ul;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\L10n;

class Locales
{
    /**
     * Return list of current loaded translations
     */
    public static function render(): string
    {
        $l10n = L10n::$locales;
        App::lexical()->lexicalKeySort($l10n, App::lexical()::ADMIN_LOCALE);

        $rows = function (array $locales) {
            foreach ($locales as $source => $translation) {
                if (is_array($translation)) {
                    $item = (new Ul())
                        ->items([
                            ... array_map(fn ($value) => (new Li())->text(Html::escapeHTML($value)), $translation),
                        ]);
                } else {
                    $item = (new Text(null, Html::escapeHTML($translation)));
                }
                yield (new Tr())
                    ->cols([
                        (new Td())
                            ->text(Html::escapeHTML((string) $source)),
                        (new Td())
                            ->class('maximal')
                            ->items([
                                $item,
                            ]),
                    ]);
            }
        };

        return (new Table('Locales'))
            ->class('sysinfo')
            ->caption(new Caption(__('Locales') . ' (' . sprintf('%d', count($l10n)) . ')'))
            ->thead((new Thead())
                ->rows([
                    (new Tr())
                        ->cols([
                            (new Th())
                                ->scope('col')
                                ->text(__('English (source)')),
                            (new Th())
                                ->scope('col')
                                ->class('maximal')
                                ->text(L10n::getLanguageName(App::auth()->getInfo('user_lang'))),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([... $rows($l10n)]))
        ->render();
    }
}
