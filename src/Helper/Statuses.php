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
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Html;
use Dotclear\Schema\Status\Blog;
use Dotclear\Schema\Status\Comment;
use Dotclear\Schema\Status\Post;
use Dotclear\Schema\Status\User;

class Statuses
{
    /**
     * Return list of statuses
     */
    public static function render(): string
    {
        return
        self::getTable(App::status()->blog(), 'Blog')->render() .
        self::getTable(App::status()->user(), 'User')->render() .
        self::getTable(App::status()->post(), 'Post')->render() .
        self::getTable(App::status()->comment(), 'Comment')->render();
    }

    protected static function getTable(Blog|User|Post|Comment $statuses, string $name): Table
    {
        $lines = function (Blog|User|Post|Comment $statuses) {
            foreach ($statuses->dump() as $status) {
                $icon      = $status->icon();
                $icon_dark = $status->iconDark();
                if ($icon_dark !== '') {
                    // Two icons, one for each mode (light and dark)
                    $icons = [
                        (new Img($icon))
                            ->alt(Html::escapeHTML(__($status->name())))
                            ->class(['mark', 'mark-' . $status->id(), 'light-only']),
                        (new Img($icon_dark))
                            ->alt(Html::escapeHTML(__($status->name())))
                            ->class(['mark', 'mark-' . $status->id(), 'dark-only']),
                    ];
                } else {
                    $icons = [
                        (new Img($icon))
                            ->alt(Html::escapeHTML(__($status->name())))
                            ->class(['mark', 'mark-' . $status->id()]),
                    ];
                }

                $restricted = $status->level() <= $statuses->threshold() ? 'status-restricted' : '';

                yield (new Tr())
                    ->class($restricted)
                    ->items([
                        (new Td())
                            ->text($status->id()),
                        (new Td())
                            ->items($icons),
                        (new Td())
                            ->class('right')
                            ->text((new Text('code', (string) $status->level()))->render()),
                        (new Td())
                            ->class('maximal')
                            ->text($status->name()),
                        (new Td())
                            ->items([
                                (new Img('images/' . ($status->hidden() ? 'check-on.svg' : 'check-off.svg')))
                                    ->class(['mark', 'mark-' . ($status->hidden() ? 'check-on' : 'check-off')])
                                    ->alt($status->hidden() ? 'true' : 'false'),
                            ]),
                    ]);
            }
        };

        return (new Table('statuses_' . mb_strtolower($name)))
            ->class('sysinfo')
            ->caption(new Caption(__($name) . ' - ' . sprintf(__('threshold = %d'), $statuses->threshold())))
            ->thead((new Thead())
                ->rows([
                    (new Tr())
                        ->cols([
                            (new Th())
                                ->scope('col')
                                ->text(__('ID')),
                            (new Th())
                                ->scope('col')
                                ->text(__('Icon')),
                            (new Th())
                                ->scope('col')
                                ->text(__('Value')),
                            (new Th())
                                ->scope('col')
                                ->class('maximal')
                                ->text(__('Name')),
                            (new Th())
                                ->scope('col')
                                ->text(__('Hidden')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([
                    ...$lines($statuses),
                ]));
    }
}
