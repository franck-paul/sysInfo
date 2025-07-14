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

use ArrayObject;
use Dotclear\App;
use Dotclear\Helper\Html\Form\Li;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Ul;
use Dotclear\Plugin\TemplateHelper\Code;

class FrontendTemplate
{
    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr   The attribute
     */
    public static function sysInfoPageTitle(array|ArrayObject $attr): string
    {
        $tplset = App::themes()->moduleInfo(App::blog()->settings()->system->theme, 'tplset');
        if (empty($tplset)) {
            $tplset = App::config()->defaultTplset() . '-default';
        }

        return Code::getPHPTemplateValueCode(
            FrontendTemplateCode::sysInfoPageTitle(...),
            [
                $tplset,
            ],
            attr: $attr,
        );
    }

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr   The attribute
     */
    public static function sysInfoBehaviours(array|ArrayObject $attr): string
    {
        return Code::getPHPTemplateValueCode(
            FrontendTemplateCode::sysInfoBehaviours(...),
            attr: $attr,
        );
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

    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr   The attribute
     */
    public static function sysInfoTemplatetags(array|ArrayObject $attr): string
    {
        return Code::getPHPTemplateValueCode(
            FrontendTemplateCode::sysInfoTemplatetags(...),
            attr: $attr,
        );
    }

    public static function publicTemplatetagsTitle(): string
    {
        return __('Template tags list');
    }

    public static function publicTemplatetagsList(): string
    {
        $tplblocks = array_values(App::frontend()->template()->getBlocksList());
        $tplvalues = array_values(App::frontend()->template()->getValuesList());

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
