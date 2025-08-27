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
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Details;
use Dotclear\Helper\Html\Form\Li;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Set;
use Dotclear\Helper\Html\Form\Strong;
use Dotclear\Helper\Html\Form\Summary;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Form\Ul;
use Dotclear\Helper\Network\Http;
use Dotclear\Module\ModuleDefine;
use Dotclear\Module\StoreParser;
use Dotclear\Module\StoreReader;
use Dotclear\Module\Themes;

class Repo
{
    /**
     * Return list of available modules
     *
     * @param      bool    $use_cache  The use cache
     * @param      string  $url        The url
     * @param      string  $title      The title
     * @param      string  $label      The label
     */
    private static function renderModules(bool $use_cache, string $url, string $title, string $label): Set
    {
        [$parser, $in_cache] = self::parseRepo($use_cache, $url);

        $defines = $parser ? $parser->getDefines() : [];
        $data    = [];
        foreach ($defines as $define) {
            $data[$define->getId()] = $define;
        }

        App::lexical()->lexicalKeySort($data, App::lexical()::ADMIN_LOCALE);
        $count = $parser ? ' (' . sprintf('%d', count($data)) . ')' : '';

        $lines = function ($data) {
            foreach ($data as $id => $define) {
                yield self::renderModule($id, $define);
            }
        };

        return (new Set())
            ->items([
                (new Text('h3', $title . __(' from: ') . ($in_cache ? __('cache') : $url) . $count)),
                $parser ?
                (new Set())
                    ->items([
                        (new Details('expand-all'))
                            ->summary(new Summary($label)),
                        ... $lines($data),
                    ]) :
                (new Note())
                    ->text(__('Repository is unreachable')),
            ]);
    }

    /**
     * Return list of available modules (from alternate repositories)
     *
     * @param      array<int|string, mixed>     $modules    The modules
     * @param      bool                         $use_cache  The use cache
     * @param      string                       $title      The title
     */
    private static function renderAltModules(array $modules, bool $use_cache, string $title): Table
    {
        $rows = [];
        foreach ($modules as $module) {
            if ($module->get('repository') != '' && App::config()->allowRepositories()) {
                $url = str_ends_with((string) $module->get('repository'), '/dcstore.xml') ? $module->get('repository') : Http::concatURL($module->get('repository'), 'dcstore.xml');

                [$parser, $in_cache] = self::parseRepo($use_cache, $url);

                $defines   = $parser ? $parser->getDefines() : [];
                $raw_datas = [];
                foreach ($defines as $define) {
                    $raw_datas[$define->getId()] = $define;
                }

                App::lexical()->lexicalKeySort($raw_datas, App::lexical()::ADMIN_LOCALE);
                $count = $parser && count($raw_datas) > 1 ? ' (' . sprintf('%d', count($raw_datas)) . ')' : '';

                $label = $url . ' ' . ($in_cache ? __('in cache') : '') . $count;

                if (!$parser) {
                    $details = (new Note())
                        ->text(__('Repository is unreachable'));
                } else {
                    $list = [];
                    foreach ($raw_datas as $id => $define) {
                        $list[] = self::renderModule($id, $define);
                    }

                    if (count($raw_datas) > 1) {
                        $details = (new Details())
                            ->summary(new Summary(__('Repository content')))
                            ->items($list);
                    } else {
                        $details = (new Set())
                            ->items($list);
                    }
                }

                $rows[] = (new Tr())
                    ->cols([
                        (new Td())
                            ->items([
                                (new Para())
                                    ->items([
                                        (new Strong($label)),
                                    ]),
                                $details,
                            ]),
                    ]);
            }
        }

        return (new Table())
            ->caption(new Caption($title))
            ->thead((new Thead())
                ->rows([
                    (new Tr())
                        ->cols([
                            (new Th())
                                ->text(__('Repositories')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows($rows));
    }

    /**
     * Render content for a single module
     *
     * @param      string          $id      The identifier
     * @param      ModuleDefine    $define  The define
     */
    private static function renderModule(string $id, ModuleDefine $define): Details
    {
        $infos = $define->dump();

        $lines = function ($infos) {
            foreach ($infos as $key => $value) {
                if (in_array($key, ['file', 'details', 'support', 'sshot'])) {
                    $val = $value ? sprintf('<a href="%1$s">%1$s</a>', $value) : $value;
                } else {
                    $val = is_array($value) ? var_export($value, true) : $value;
                }

                yield (new Li())
                    ->text($key . ' = ' . $val);
            }
        };

        return (new Details())
            ->summary(new Summary($id))
            ->items([
                (new Ul())
                    ->items([... $lines($infos)]),
            ]);
    }

    /**
     * Parse a repository
     *
     * @param      bool    $use_cache  The use cache
     * @param      string  $url        The url
     *
     * @return     list{0:false|StoreParser, 1:bool}
     */
    private static function parseRepo(bool $use_cache, string $url): array
    {
        $cache_path = Path::real(App::config()->cacheRoot());
        $in_cache   = false;

        if ($use_cache) {
            // Get XML cache file for modules
            $ser_file = sprintf(
                '%s/%s/%s/%s/%s.ser',
                $cache_path,
                'dcrepo',
                substr(md5($url), 0, 2),
                substr(md5($url), 2, 2),
                md5($url)
            );
            if (file_exists($ser_file)) {
                $in_cache = true;
            }
        }

        $ret = StoreReader::quickParse($url, App::config()->cacheRoot(), !$in_cache);

        return [
            $ret,
            $in_cache,
        ];
    }

    /**
     * Return list of available plugins
     *
     * @param      bool    $use_cache  Use cache if available
     */
    public static function renderPlugins(bool $use_cache = false): string
    {
        return self::renderModules(
            $use_cache,
            App::blog()->settings()->system->store_plugin_url,
            __('Repository plugins list'),
            __('Plugin ID')
        )
        ->render();
    }

    /**
     * Return list of available themes
     *
     * @param      bool    $use_cache  Use cache if available
     */
    public static function renderThemes(bool $use_cache = false): string
    {
        return self::renderModules(
            $use_cache,
            App::blog()->settings()->system->store_theme_url,
            __('Repository themes list'),
            __('Theme ID')
        )
        ->render();
    }

    /**
     * Return list of available plugins from alternate repositories
     */
    public static function renderAltPlugins(): string
    {
        $plugins = App::plugins()->getDefines();
        uasort($plugins, static fn ($a, $b): int => strtolower((string) $a->getId()) <=> strtolower((string) $b->getId()));

        return self::renderAltModules(
            $plugins,
            true,
            __('Repository plugins list (alternate repositories)')
        )
        ->render();
    }

    /**
     * Return list of available themes from alternate repositories
     */
    public static function renderAltThemes(): string
    {
        if (!(App::themes() instanceof Themes)) {
            App::themes()->loadModules((string) App::blog()->themes_path, null);
        }

        $themes = App::themes()->getDefines();
        uasort($themes, static fn ($a, $b): int => strtolower((string) $a->getId()) <=> strtolower((string) $b->getId()));

        return self::renderAltModules(
            $themes,
            true,
            __('Repository themes list (alternate repositories)')
        )
        ->render();
    }
}
