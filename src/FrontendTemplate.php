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

namespace Dotclear\Plugin\sysInfo;

use Dotclear\App;
use Dotclear\Helper\Html\Form\Li;
use Dotclear\Helper\Html\Form\Set;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Ul;

class FrontendTemplate
{
    private static function phpCode(string $code, bool $echo = true): string
    {
        if ($echo) {
            // Use PHP short syntax with implicit echo
            return '<?= ' . trim($code) . ' ?>';
        }

        return implode("\n", ['<?php', trim($code), '?>']) . "\n";
    }

    public static function sysInfoPageTitle(): string
    {
        $tplset = App::themes()->moduleInfo(App::blog()->settings()->system->theme, 'tplset');
        if (empty($tplset)) {
            $tplset = App::config()->defaultTplset() . '-default';
        }

        return trim((string) (new Text('span', __('System Information')))
            ->class('dc-tpl-' . $tplset)
        ->render());
    }

    public static function sysInfoBehaviours(): string
    {
        return (new Set())
            ->items([
                (new Text('h3', self::phpCode(self::class . '::publicBehavioursTitle()'))),
                (new Text(null, self::phpCode(self::class . '::publicBehavioursList()'))),
            ])
        ->render();
    }

    public static function publicBehavioursTitle(): string
    {
        $bl = App::behavior()->getBehaviors();

        return __('Public behaviours list') . ' (' . sprintf('%d', count($bl)) . ')';
    }

    public static function publicBehavioursList(): string
    {
        $behaviorsList = function (array $behaviors) {
            foreach ($behaviors as $name => $callbacks) {
                yield (new Li())
                    ->items([
                        (new Text(null, (string) $name)),
                        (new Ul())
                            ->items([
                                ... array_map(
                                    fn ($callback) => (new Li())
                                        ->items([
                                            (new Text('code', CoreHelper::callableName($callback))),
                                        ]),
                                    $callbacks
                                ),
                            ]),
                    ]);
            }
        };

        return (new Ul())
            ->items([
                ... $behaviorsList(App::behavior()->getBehaviors()),
            ])
        ->render();
    }

    public static function sysInfoTemplatetags(): string
    {
        return (new Set())
            ->items([
                (new Text('h3', self::phpCode(self::class . '::publicTemplatetagsTitle()'))),
                (new Text(null, self::phpCode(self::class . '::publicTemplatetagsList()'))),
            ])
        ->render();
    }

    public static function publicTemplatetagsTitle(): string
    {
        return __('Template tags list');
    }

    public static function publicTemplatetagsList(): string
    {
        $tplblocks = array_values(App::frontend()->template()->getBlockslist());
        $tplvalues = array_values(App::frontend()->template()->getValueslist());

        sort($tplblocks, SORT_STRING);
        sort($tplvalues, SORT_STRING);

        $tagsList = function (array $list, bool $block) {
            foreach ($list as $tag) {
                $callback = $block ?
                    App::frontend()->template()->getBlockCallback($tag) :
                    App::frontend()->template()->getValueCallback($tag);
                yield (new Li())
                    ->separator(' - ')
                    ->items([
                        (new Text(null, $tag)),
                        (new Text('code', CoreHelper::callableName($callback))),
                    ]);
            }
        };

        return (new Ul())
            ->items([
                (new Li())
                    ->items([
                        (new Text(null, __('Blocks') . ' (' . count($tplblocks) . ')')),
                        (new Ul())
                            ->items([
                                ... $tagsList($tplblocks, true),
                            ]),
                    ]),
                (new Li())
                    ->items([
                        (new Text(null, __('Values') . ' (' . count($tplvalues) . ')')),
                        (new Ul())
                            ->items([
                                ... $tagsList($tplvalues, false),
                            ]),
                    ]),
            ])
        ->render();
    }
}
