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

use DateTimeImmutable;
use Dotclear\App;
use Dotclear\Core\Backend\Notices;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Form\Button;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Set;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Network\Http;
use Dotclear\Plugin\sysInfo\My;
use Exception;

class StaticCache
{
    /**
     * Return list of files in static cache
     */
    public static function render(): string
    {
        $blog_host = App::blog()->host();
        if (!str_ends_with((string) $blog_host, '/')) {
            $blog_host .= '/';
        }

        if (!defined('DC_SC_CACHE_DIR')) {
            return (new Note())
                ->text(__('Static cache directory does not exists'))
            ->render();
        }

        $cache_dir = (string) Path::real(DC_SC_CACHE_DIR, false);
        $cache_key = md5(Http::getHostFromURL($blog_host));
        $cache     = new \Dotclear\Plugin\staticCache\StaticCache(DC_SC_CACHE_DIR, $cache_key);
        $pattern   = implode(DIRECTORY_SEPARATOR, array_fill(0, 5, '%s'));

        if (!is_dir($cache_dir)) {
            return (new Note())
                ->text(__('Static cache directory does not exists'))
            ->render();
        }

        if (!is_readable($cache_dir)) {
            return (new Note())
                ->text(__('Static cache directory is not readable'))
            ->render();
        }

        $k          = str_split($cache_key, 2);
        $cache_root = $cache_dir;
        $cache_dir  = sprintf($pattern, $cache_dir, $k[0], $k[1], $k[2], $cache_key);
        $caption    = __('List of static cache files in') . ' ' . substr($cache_dir, strlen($cache_root));
        $mtime      = $cache->getMtime();
        if ($mtime !== false) {
            $caption .= ', ' . __('last update:') . ' ' . (new DateTimeImmutable())->setTimestamp((int) $cache->getMtime())->format('c');
        }

        // List of existing cache files
        $rows = [];

        try {
            $files = Files::scandir($cache_dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && $file !== 'mtime') {
                    $cache_fullpath = $cache_dir . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($cache_fullpath)) {
                        $rows[] = (new Tr())
                            ->cols([
                                (new Td())      // 1st level
                                    ->class('nowrap')
                                    ->items([
                                        (new Link())
                                            ->class('sc_dir')
                                            ->href('#')
                                            ->text($file),
                                    ]),
                                (new Td())      // 2nd level (loaded via getStaticCacheDir REST)
                                    ->class('nowrap')
                                    ->text(__('…')),
                                (new Td())      // 3rd level (loaded via getStaticCacheList REST)
                                    ->class('nowrap'),
                                (new Td())      // cache file (loaded via getStaticCacheList REST too)
                                    ->class(['nowrap', 'maximal']),
                            ]);
                    }
                }
            }
        } catch (Exception) {
            // Unable to read the static cache directory, ignore it
        }

        return (new Set())
            ->items([
                // Add a static cache URL convertor
                (new Fieldset())
                    ->legend(new Legend(__('URL converter')))
                    ->fields([
                        (new Para())
                            ->separator(' ')
                            ->items([
                                (new Input('sccalc_url'))
                                    ->size(50)
                                    ->maxlength(255)
                                    ->value(Html::escapeHTML(App::blog()->url()))
                                    ->label((new Label(__('URL:'), Label::IL_TF))->class('classic')),
                                (new Button('getscaction', __(' → '))),
                                (new Text('output'))
                                    ->id('sccalc_res'),
                                (new Link('sccalc_preview'))
                                    ->href('#')
                                    ->data(['dir' => $cache_dir]),
                            ]),
                    ]),
                // List of existing cache files
                (new Form('scform'))
                    ->method('post')
                    ->action(App::backend()->getPageURL())
                    ->fields([
                        (new Table('staticcache'))
                            ->class('sysinfo')
                            ->caption(new Caption($caption))
                            ->thead((new Thead())
                                ->rows([
                                    (new Tr())
                                        ->cols([
                                            (new Th())
                                                ->scope('col')
                                                ->class('nowrap')
                                                ->colspan(3)
                                                ->text(__('Cache subpath')),
                                            (new Th())
                                                ->scope('col')
                                                ->class(['nowrap', 'maximal'])
                                                ->text(__('Cache file')),
                                        ]),
                                ]))
                            ->tbody((new Tbody())
                                ->rows($rows)),
                        (new Div())
                            ->class('two-cols')
                            ->items([
                                (new Para())
                                    ->class(['col', 'checkboxes-helpers']),
                                (new Para())
                                    ->class(['col', 'right', 'form-buttons'])
                                    ->items([
                                        ... My::hiddenFields(),
                                        (new Submit('delscaction', __('Delete selected cache files')))
                                            ->class('delete'),
                                    ]),
                            ]),
                    ]),
            ])
        ->render();
    }

    /**
     * Cope with static cache form action.
     *
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception
     */
    public static function process(string $checklist): string
    {
        $nextlist = $checklist;
        if (!empty($_POST['delscaction'])) {
            // Cope with static cache file deletion
            try {
                if (empty($_POST['sc'])) {
                    throw new Exception(__('No cache file selected'));
                }

                foreach ($_POST['sc'] as $cache_file) {
                    if (file_exists($cache_file)) {
                        unlink($cache_file);
                    }
                }
            } catch (Exception $e) {
                $nextlist = 'sc';
                App::error()->add($e->getMessage());
            }

            if (!App::error()->flag()) {
                Notices::addSuccessNotice(__('Selected cache files have been deleted.'));
                My::redirect([
                    'sc' => 1,
                ]);
            }
        }

        return $nextlist;
    }

    public static function check(string $checklist): string
    {
        return empty($_GET['sc']) ? $checklist : 'sc';
    }
}
