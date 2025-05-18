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

use Autoloader;
use Dotclear\App;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Strong;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Network\Http;

class FrontendBehaviors
{
    public static function publicHeadContent(): string
    {
        $settings = My::settings();

        if ((bool) $settings->public_debug) {
            echo My::cssLoad('frontend.css');
        }

        return '';
    }
    public static function publicAfterDocument(): string
    {
        $settings = My::settings();

        if ((bool) $settings->public_debug) {
            echo static::debugInfo();
        }

        return '';
    }

    /**
     * Ensures that Xdebug stack trace is available based on Xdebug version.
     *
     * Idea taken from developer bishopb at https://github.com/rollbar/rollbar-php
     *
     *  xdebug configuration:
     *
     *  zend_extension = /.../xdebug.so
     *
     *  xdebug.auto_trace = On
     *  xdebug.trace_format = 0
     *  xdebug.trace_options = 1
     *  xdebug.show_mem_delta = On
     *  xdebug.profiler_enable = 0
     *  xdebug.profiler_enable_trigger = 1
     *  xdebug.profiler_output_dir = /tmp
     *  xdebug.profiler_append = 0
     *  xdebug.profiler_output_name = timestamp
     */
    private static function isXdebugStackAvailable(): bool
    {
        if (!function_exists('xdebug_get_function_stack')) {
            return false;
        }

        // check for Xdebug being installed to ensure origin of xdebug_get_function_stack()
        $version = phpversion('xdebug');
        if ($version === false) {
            return false;
        }

        // Xdebug 2 and prior
        if (version_compare($version, '3.0.0', '<')) {
            return true;
        }

        // Xdebug 3 and later, proper mode is required
        $xdebug = ini_get('xdebug.mode');

        return $xdebug === false ? false : str_contains($xdebug, 'develop');
    }

    /**
     * Get HTML code of debug information
     */
    protected static function debugInfo(): string
    {
        $items = [];

        $items[] = (new Para())
            ->items([
                (new Text(null, 'Memory: usage = ')),
                (new Strong(Files::size(memory_get_usage()))),
                (new Text(null, ' - peak = ')),
                (new Strong(Files::size(memory_get_peak_usage()))),
            ]);

        if (static::isXdebugStackAvailable()) {
            $items[] = (new Para())
                ->items([
                    (new Text(null, 'Elapsed time = ')),
                    (new Strong((string) xdebug_time_index())),
                    (new Text(null, ' seconds')),
                ]);

            $prof_file = xdebug_get_profiler_filename();
            if ($prof_file !== '') {
                $items[] = (new Para())
                    ->items([
                        (new Text(null, 'Profiler file : ' . xdebug_get_profiler_filename())),
                    ]);
            } else {
                $prof_url = Http::getSelfURI();
                $prof_url .= str_contains($prof_url, '?') ? '&' : '?';
                $prof_url .= 'XDEBUG_PROFILE';

                $items[] = (new Para())
                    ->items([
                        (new Link())
                            ->href(Html::escapeURL($prof_url))
                            ->text('Trigger profiler'),
                    ]);
            }
        } else {
            $start    = App::config()->startTime();
            $end      = microtime(true);
            $duration = (int) (($end - $start) * 1000); // in milliseconds

            $items[] = (new Para())
                ->items([
                    (new Text(null, 'Page construction time (without asynchronous/secondary HTTP requests) = ')),
                    (new Strong(sprintf('%d ms', $duration))),
                ]);
        }

        $exclude     = ['_COOKIE', '_ENV', '_FILES', '_GET', '_POST', '_REQUEST', '_SERVER', '_SESSION'];
        $global_vars = array_diff(array_keys($GLOBALS), $exclude);
        sort($global_vars);
        $vars = array_map(fn ($var): string => (new Strong($var))->render(), $global_vars);

        $items[] = (new Para())
            ->items([
                (new Text(null, 'Global vars (Dotclear only): ' . implode(', ', $vars))),
            ]);

        $items[] = (new Para())
            ->items([
                (new Text(null, 'Autoloader: requests = ')),
                (new Strong((string) Autoloader::me()->getRequestsCount())),
                (new Text(null, ' - loads = ')),
                (new Strong((string) Autoloader::me()->getLoadsCount())),
            ]);

        return (new Div())
            ->id('sysinfo_debug')
            ->items([
                (new Div())
                    ->items($items),
            ])
        ->render();
    }
}
