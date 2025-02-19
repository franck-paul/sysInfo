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
use Dotclear\Helper\Html\Form\Details;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Li;
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\None;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Set;
use Dotclear\Helper\Html\Form\Summary;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Ul;
use Dotclear\Helper\Html\Html;
use Exception;

class System
{
    /**
     * Return a quote and PHP and DB driver version
     *
     * @param   bool    $quote include quote
     */
    public static function render(bool $quote = true): string
    {
        // Display a quote and PHP and DB version
        $quotes = [
            __('Live long and prosper.'),
            __('To infinity and beyond.'),
            __('So long, and thanks for all the fish.'),
            __('Find a needle in a haystack.'),
            __('A clever person solves a problem. A wise person avoids it.'),
            __('I\'m sorry, Dave. I\'m afraid I can\'t do that.'),
            __('With great power there must also come great responsibility.'),
            __('It\'s great, we have to do it all over again!'),
            __('Have You Tried Turning It Off And On Again?'),
            __('It\'s great, we\'ll have to do it all again!'),
        ];
        $q = random_int(0, count($quotes) - 1);

        // Get cache info
        $caches = [];

        try {
            // Check OPCache
            if ((extension_loaded('opcache') || extension_loaded('Zend OPcache')) && function_exists('\opcache_get_status')) {
                if (ini_get('opcache.restrict_api') !== false && ini_get('opcache.restrict_api') !== '') {
                    // OPCache API is restricted via .htaccess (or web server config), PHP_INI_USER or PHP_INI_PERDIR
                } elseif (get_cfg_var('opcache.restrict_api') !== false && get_cfg_var('opcache.restrict_api') !== '') {
                    // OPCache API is restricted via PHP.ini
                } elseif (is_array(opcache_get_status())) {
                    $caches[] = 'OPCache';
                }
            }
            // Check APCu
            if (function_exists('\apcu_cache_info') && !in_array(\apcu_cache_info(), [[], false], true)) {
                $caches[] = 'APCu';
            }
            // Check Memcache
            if (class_exists(\Memcache::class)) {
                $memcache     = new \Memcache();
                $isMemcacheOn = @$memcache->connect('localhost', 11211, 1);
                if ($isMemcacheOn) {
                    $caches[] = 'Memcache';
                }
            }
        } catch (Exception) {
        }

        if ($caches === []) {
            $caches[] = __('None');
        }

        // Helpers
        $toStrong = fn ($text): string => (new Text('strong', $text))->render();
        $toCode   = fn ($text): string => (new Text('code', $text))->render();

        // Server info
        $software = (new None());
        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            $info     = explode(' ', (string) $_SERVER['SERVER_SOFTWARE']);
            $software = (new Li())
                ->text(__('Server software: ') . $toStrong($info[0]));
        }

        $getDbInfo = fn (): string => App::con()->syntax() === 'mysql' ? ' - ' . sprintf(__('%s server'), $toStrong(stristr(mysqli_get_server_info(App::con()->link()), 'mariadb') ? 'MariaDB' : 'MySQL')) : '';

        $server = (new Set())
            ->items([
                $quote ? (new Div(null, 'blockquote'))
                    ->class('sysinfo')
                    ->items([
                        (new Note())
                            ->text($quotes[$q]),
                    ]) :
                    (new None()),
                (new Details())
                    ->open(true)
                    ->summary(new Summary(__('System info')))
                    ->items([
                        (new Ul())
                            ->items([
                                $software,
                                (new Li())
                                    ->text(__('PHP Version: ') . $toStrong(phpversion())),
                                (new Li())
                                    ->separator(' ')
                                    ->items([
                                        (new Text(null, __('DB driver: ') . $toStrong(App::con()->driver()))),
                                        (new Text(null, __('version') . $toStrong(App::con()->version()))),
                                        (new Text(null, sprintf(__('using <strong>%s</strong> syntax'), App::con()->syntax()) . $getDbInfo())),
                                    ]),
                                (new Li())
                                    ->separator(' ')
                                    ->items([
                                        (new Text(null, __('Error reporting: ' . $toStrong((string) error_reporting())))),
                                        (new Text(null, '=')),
                                        (new Text(null, self::errorLevelToString(error_reporting(), ', '))),
                                    ]),
                                (new Li())
                                    ->text(__('PHP Cache: ') . implode(', ', array_map(fn ($cache): string => $toStrong($cache), $caches))),
                                (new Li())
                                    ->text(__('Temporary folder: ') . $toStrong(sys_get_temp_dir())),
                                (new Li())
                                    ->text('DIRECTORY_SEPARATOR: ' . $toStrong($toCode(DIRECTORY_SEPARATOR))),
                                (new Li())
                                    ->text('PATH_SEPARATOR: ' . $toStrong($toCode(PATH_SEPARATOR))),
                                session_id() ?
                                    (new Li())
                                        ->text('session_id(): ' . $toStrong($toCode(session_id()))) :
                                    (new None()),
                                (new Li('sys_battery'))
                                    ->text(__('Battery level: ') . $toStrong('')),
                            ]),
                    ]),
            ]);

        // Dotclear info
        $dotclear = (new Details())
            ->open(true)
            ->summary(new Summary(__('Dotclear info')))
            ->items([
                (new Ul())
                    ->items([
                        (new Li())
                            ->text(__('Dotclear version: ') . $toStrong(App::config()->dotclearVersion())),
                        (new Li())
                            ->text(__('Update channel: ') . $toStrong(App::config()->coreUpdateCanal())),
                    ]),
            ]);

        // Update info
        $versions = [];
        $path     = Path::real(App::config()->cacheRoot() . '/versions');
        if ($path && is_dir($path)) {
            $channels = ['stable', 'testing', 'unstable'];
            foreach ($channels as $channel) {
                $file = $path . '/dotclear-' . $channel;
                if (file_exists($file) && ($content = @unserialize((string) @file_get_contents($file))) && (is_array($content))) {
                    $versions[] = (new Li())
                        ->items([
                            (new Text(null, __('Channel: ') . $toStrong($channel) . ' (' . date(DATE_ATOM, (int) filemtime($file)) . ')')),
                            (new Ul())
                                ->items([
                                    (new Li())
                                        ->text(__('version: ') . $toStrong($content['version'])),
                                    (new Li())
                                        ->items([
                                            (new Text(null, __('href: '))),
                                            (new Link())
                                                ->href($content['href'])
                                                ->text(Html::escapeHtml($content['href'])),
                                        ]),
                                    (new Li())
                                        ->text(__('checksum: ') . $toCode($content['checksum'])),
                                    (new Li())
                                        ->items([
                                            (new Text(null, __('info: '))),
                                            (new Link())
                                                ->href($content['info'])
                                                ->text(Html::escapeHtml($content['info'])),
                                        ]),
                                    (new Li())
                                        ->text(__('PHP min: ') . $toStrong($content['php'])),
                                    isset($content['warning']) ?
                                        (new Li())
                                            ->text(__('Warning: ') . $toStrong($content['warning'] ? __('Yes') : __('No'))) :
                                        (new None()),
                                ]),
                        ]);
                }
            }
        }

        $update = (new None());
        if ($versions !== []) {
            $update = (new Details())
                ->summary(new Summary(__('Update info') . ' ' . __('(from versions cache)')))
                ->items([
                    (new Ul())
                        ->items($versions),
                ]);
        }

        // Release info
        $infos        = [];
        $release_file = App::config()->dotclearRoot() . DIRECTORY_SEPARATOR . 'release.json';
        if (file_exists($release_file)) {
            // Add a section with the content of release.json file
            $content = json_decode((string) file_get_contents($release_file), true);
            foreach ($content as $key => $value) {
                if (is_array($value)) {
                    $list = [];
                    foreach ($value as $subkey => $subvalue) {
                        $list[] = (new Li())
                            ->text($subkey . ' = ' . $toStrong($subvalue));
                    }

                    $infos[] = (new Li())
                        ->items([
                            (new Text(null, $key . ' = ')),
                            (new Ul())
                                ->items($list),
                        ]);
                } else {
                    $infos[] = (new Li())
                        ->text($key . ' = ' . $toStrong($value));
                }
            }
        }

        $release = (new None());
        if ($infos !== []) {
            $release = (new Details())
                ->summary(new Summary(__('Release info')))
                ->items([
                    (new Ul())
                        ->items($infos),
                ]);
        }

        return (new Set())
            ->items([
                $server,
                $dotclear,
                $update,
                $release,
            ])
        ->render();
    }

    /**
     * PHP error_reporting to string
     *
     * @param      int     $intval     The intval
     * @param      string  $separator  The separator
     */
    private static function errorLevelToString(int $intval, string $separator = ','): string
    {
        $errorlevels = [
            E_ALL               => 'E_ALL',
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
            E_DEPRECATED        => 'E_DEPRECATED',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_NOTICE            => 'E_NOTICE',
            E_PARSE             => 'E_PARSE',
            E_WARNING           => 'E_WARNING',
            E_ERROR             => 'E_ERROR',
        ];
        $result = [];
        foreach ($errorlevels as $number => $name) {
            if (($intval & $number) === $number) {
                $result[] = $name;
            }
        }

        return implode($separator, $result);
    }
}
