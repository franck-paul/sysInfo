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
use Dotclear\Helper\Html\Form\Strong;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Form\Ul;
use Dotclear\Plugin\sysInfo\CoreHelper;

class Globals
{
    /**
     * Return list of global variables
     */
    public static function render(): string
    {
        $max_length = 1024 * 4;     // 4Kb max

        $variables = array_map(fn (int|string $v): string => (string) $v, array_keys($GLOBALS));
        App::lexical()->lexicalSort($variables, App::lexical()::ADMIN_LOCALE);

        $deprecated = [
            '__autoload'     => '2.23',
            '__l10n'         => '2.24',
            '__l10n_files'   => '2.24',
            '__parent_theme' => '2.23',
            '__resources'    => '2.23',
            '__smilies'      => '2.23',
            '__theme'        => '2.23',
            '__widgets'      => '2.23',

            '_ctx'          => '2.23',
            '_lang'         => '2.23',
            '_menu'         => '2.23',
            '_page_number'  => '2.23',
            '_search'       => '2.23',
            '_search_count' => '2.23',

            'core'      => '2.23',
            'mod_files' => '2.23',
            'mod_ts'    => '2.23',
            'p_url'     => '2.23',
        ];

        $globals = function ($non_deprecated = true) use ($variables, $deprecated, $max_length) {
            foreach ($variables as $variable) {
                if ($non_deprecated && !in_array($variable, array_keys($deprecated))) {
                    if (is_array($GLOBALS[$variable])) {
                        $values = $GLOBALS[$variable];
                        App::lexical()->lexicalKeySort($values, App::lexical()::ADMIN_LOCALE);

                        $lines = function ($values) {
                            foreach ($values as $key => $value) {
                                yield (new Li())
                                    ->separator(' ')
                                    ->items([
                                        (new Strong($key)),
                                        (new Text(null, '=')),
                                        (new Text('code', CoreHelper::simplifyFilename(print_r($value, true)))),
                                        (new Text(null, '(' . gettype($value) . ')')),
                                    ]);
                            }
                        };

                        $content = (new Ul())
                            ->items([
                                ... $lines($values),
                            ]);
                    } else {
                        $value = CoreHelper::simplifyFilename(print_r($GLOBALS[$variable], true));
                        if (mb_strlen($value) > $max_length) {
                            $value = mb_substr($value, 0, $max_length) . ' …';
                        }

                        $content = (new Text(null, $value . '(' . gettype($value) . ')'));
                    }

                    yield (new Tr())
                        ->cols([
                            (new Td())
                                ->class('nowrap')
                                ->text($variable),
                            (new td())
                                ->items([
                                    $content,
                                ]),
                        ]);
                } elseif (!$non_deprecated && in_array($variable, array_keys($deprecated))) {
                    yield (new Tr())
                        ->cols([
                            (new Td())
                                ->class('nowrap')
                                ->text($variable),
                            (new td())
                                ->class(['maximal', 'deprecated'])
                                ->text(sprintf(__('*** deprecated since %s ***'), $deprecated[$variable])),
                        ]);
                }
            }
        };

        return (new Table('globals'))
            ->class(['sysinfo'])
            ->caption(new Caption(__('Global variables') . ' (' . sprintf('%d', count($variables)) . ')'))
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
                                ->class('maximal')
                                ->text(__('Content')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([
                    ... $globals(),         // First loop for non deprecated variables
                    ... $globals(false),    // Second loop for deprecated variables
                ]))
        ->render();
    }
}
