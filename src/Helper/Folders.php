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
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Img;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Template\Template;
use Dotclear\Plugin\sysInfo\CoreHelper;
use Exception;

class Folders
{
    /**
     * Check generic Dotclear folders
     */
    public static function render(): string
    {
        // Check generic Dotclear folders
        $folders = [
            'root'   => App::config()->dotclearRoot(),
            'config' => App::config()->configPath(),
            'cache'  => [
                App::config()->cacheRoot(),
                App::config()->cacheRoot() . DIRECTORY_SEPARATOR . 'cbfeed',
                App::config()->cacheRoot() . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER,
                App::config()->cacheRoot() . DIRECTORY_SEPARATOR . 'dcrepo',
                App::config()->cacheRoot() . DIRECTORY_SEPARATOR . 'versions',
            ],
            'digest'  => App::config()->digestsRoot(),
            'l10n'    => App::config()->l10nRoot(),
            'plugins' => explode(PATH_SEPARATOR, (string) App::config()->pluginsRoot()),
            'public'  => App::blog()->publicPath(),
            'themes'  => App::blog()->themesPath(),
            'var'     => App::config()->varRoot(),
        ];

        if (defined('DC_SC_CACHE_DIR')) {
            $folders += ['static' => DC_SC_CACHE_DIR];
        }

        $lines = function () use ($folders) {
            foreach ($folders as $name => $subfolder) {
                if (!is_array($subfolder)) {
                    $subfolder = [$subfolder];
                }

                foreach ($subfolder as $folder) {
                    $err      = '';
                    $statuses = [];
                    if ($path = Path::real($folder)) {
                        $writable = is_writable($path);
                        $touch    = true;
                        if ($writable && is_dir($path)) {
                            // Try to create a file, inherit dir perms and then delete it
                            $void = '';

                            try {
                                $void  = $path . (substr($path, -1) === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR) . 'tmp-' . str_shuffle(MD5(microtime()));
                                $touch = false;
                                Files::putContent($void, '');
                                if (file_exists($void)) {
                                    Files::inheritChmod($void);
                                    unlink($void);
                                    $touch = true;
                                }
                            } catch (Exception $e) {
                                $err = $void . ' : ' . $e->getMessage();
                            }
                        }

                        if ($writable && $touch) {
                            $statuses[] = (new Img('images/check-on.svg'))
                                ->class(['mark', 'mark-check-on']);
                            $statuses[] = (new Text(null, __('Writable')));
                        } else {
                            $statuses[] = (new Img('images/check-wrn.svg'))
                                ->class(['mark', 'mark-check-wrn']);
                            $statuses[] = (new Text(null, __('Readonly')));
                        }
                    } else {
                        $statuses[] = (new Img('images/check-off.svg'))
                            ->class(['mark', 'mark-check-off']);
                        $statuses[] = (new Text(null, __('Unknown')));
                    }

                    if ($err !== '') {
                        $statuses[] = (new Div())
                            ->extra('style="display: none;"')
                            ->items([
                                (new Note())
                                    ->text($err),
                            ]);
                    }

                    if (str_starts_with((string) $folder, (string) App::config()->dotclearRoot())) {
                        $folder = substr_replace($folder, '<code>DC_ROOT</code> ', 0, strlen((string) App::config()->dotclearRoot()));
                    }

                    yield (new Tr())
                        ->cols([
                            (new Td())
                                ->class('nowrap')
                                ->text($name),
                            (new Td())
                                ->class('maximal')
                                ->text(CoreHelper::simplifyFilename($folder)),
                            (new Td())
                                ->class('nowrap')
                                ->separator(' ')
                                ->items($statuses),
                        ]);

                    $name = '';     // Avoid repeating it if multiple lines
                }
            }
        };

        return (new Table('folders'))
            ->class('sysinfo')
            ->caption(new Caption(__('Dotclear folders and files')))
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
                                ->text(__('Path')),
                            (new Th())
                                ->scope('col')
                                ->class('nowrap')
                                ->text(__('Status')),
                        ]),
                ]))
            ->tbody((new Tbody())
                ->rows([
                    ... $lines(),
                ]))
        ->render();
    }
}
