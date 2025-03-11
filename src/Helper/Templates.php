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
use Dotclear\Core\Backend\Notices;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Img;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Template\Template;
use Dotclear\Plugin\sysInfo\CoreHelper;
use Dotclear\Plugin\sysInfo\My;
use Exception;

class Templates
{
    /**
     * Return list of compiled template's files
     */
    public static function render(): string
    {
        CoreHelper::publicPrepend();

        $document_root = (empty($_SERVER['DOCUMENT_ROOT']) ? '' : $_SERVER['DOCUMENT_ROOT']);
        $cache_path    = (string) Path::real(App::config()->cacheRoot());
        if (str_starts_with($cache_path, (string) $document_root)) {
            $cache_path = substr($cache_path, strlen((string) $document_root));
        } elseif (str_starts_with($cache_path, (string) App::config()->dotclearRoot())) {
            $cache_path = substr($cache_path, strlen((string) App::config()->dotclearRoot()));
        }

        $blog_host = App::blog()->host();
        if (!str_ends_with((string) $blog_host, '/')) {
            $blog_host .= '/';
        }

        $blog_url = App::blog()->url();
        if (str_starts_with((string) $blog_url, (string) $blog_host)) {
            $blog_url = substr((string) $blog_url, strlen((string) $blog_host));
        }

        $paths = App::frontend()->template()->getPath();

        $rows = [];

        // Template stack
        $stack = [];
        // Loop on template paths
        foreach ($paths as $path) {
            $sub_path = (string) Path::real($path, false);
            if (str_starts_with($sub_path, (string) $document_root)) {
                $sub_path = substr($sub_path, strlen((string) $document_root));
                if (str_starts_with($sub_path, '/')) {
                    $sub_path = substr($sub_path, 1);
                }
            } elseif (str_starts_with($sub_path, (string) App::config()->dotclearRoot())) {
                $sub_path = substr($sub_path, strlen((string) App::config()->dotclearRoot()));
                if (str_starts_with($sub_path, '/')) {
                    $sub_path = substr($sub_path, 1);
                }
            }

            $path_displayed = false;
            $md5_path       = $path;
            if (str_starts_with((string) Path::real($path), (string) App::config()->dotclearRoot())) {
                $md5_path = (string) Path::real($path);
            }

            $files = Files::scandir($path);
            foreach ($files as $file) {
                if (preg_match('/^(.*)\.(html|xml|xsl)$/', $file, $matches) && !in_array($file, $stack)) {
                    $stack[] = $file;

                    /**
                     * Check cache file
                     *
                     * @param      string  $path   The path
                     * @param      string  $file   The file
                     *
                     * @return     array{0: string, 1: string, 2: bool}
                     */
                    $check = function (string $path, string $file): array {
                        // Compute MD5 representation of the cache file
                        $cache_file = md5($path . DIRECTORY_SEPARATOR . $file) . '.php';
                        // Get sub path where the cache file should be stored
                        $cache_subpath = sprintf('%s/%s', substr($cache_file, 0, 2), substr($cache_file, 2, 2));
                        $file_exists   = file_exists(
                            implode(DIRECTORY_SEPARATOR, [
                                Path::real(App::config()->cacheRoot()),
                                Template::CACHE_FOLDER,
                                $cache_subpath,
                                $cache_file,
                            ])
                        );

                        return [
                            $cache_file,
                            $cache_subpath,
                            $file_exists,
                        ];
                    };

                    [$cache_file, $cache_subpath, $file_exists] = $check($md5_path, $file);
                    if (!$file_exists) {
                        // Try with real path
                        [$cache_file, $cache_subpath, $file_exists] = $check((string) Path::real($path), $file);
                    }

                    $title = CoreHelper::simplifyFilename($sub_path) . DIRECTORY_SEPARATOR . $file;

                    $url = $file_exists ?
                        (new Link())
                            ->class('tpl_compiled')
                            ->title($title)
                            ->href('#')
                            ->text($cache_file)
                        ->render() :
                        $cache_file;

                    $rows[] = (new Tr())
                        ->cols([
                            (new Td())
                                ->text($path_displayed ? '' : CoreHelper::simplifyFilename($sub_path)),
                            (new Td())
                                ->class('nowrap')
                                ->text($file),
                            (new Td())
                                ->class('nowrap')
                                ->separator(' ')
                                ->items([
                                    (new Img('images/' . ($file_exists ? 'check-on.svg' : 'check-off.svg')))
                                        ->class(['mark', 'mark-' . ($file_exists ? 'check-on' : 'check-off')]),
                                    (new Text(null, $cache_subpath)),
                                ]),
                            (new Td())
                                ->class('nowrap')
                                ->items([
                                    (new Checkbox(['tpl[]'], false))
                                        ->value($cache_file)
                                        ->class($file_exists ? 'tpl_compiled' : '')
                                        ->disabled(!$file_exists)
                                        ->label(new Label($url, Label::IL_FT)),
                                ]),
                        ]);

                    $path_displayed = true;
                }
            }
        }

        return (new Form('tplform'))
            ->method('post')
            ->action(App::backend()->getPageURL())
            ->fields([
                (new Table('templates'))
                    ->class('sysinfo')
                    ->caption(new Caption(__('List of compiled templates in cache') . ' ' . $cache_path . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER))
                    ->thead((new Thead())
                        ->rows([
                            (new Tr())
                                ->cols([
                                    (new Th())
                                        ->scope('col')
                                        ->text(__('Template path')),
                                    (new Th())
                                        ->scope('col')
                                        ->class('nowrap')
                                        ->text(__('Template file')),
                                    (new Th())
                                        ->scope('col')
                                        ->class('nowrap')
                                        ->text(__('Cache subpath')),
                                    (new Th())
                                        ->scope('col')
                                        ->class('nowrap')
                                        ->text(__('Cache file')),
                                ]),
                        ]))
                    ->tbody((new Tbody())
                        ->rows($rows)),
                (new Div())
                    ->class('two-cols')
                    ->items([
                        (new Para())
                            ->class(['col', 'checkboxes-helpers', 'form-buttons']),
                        (new Para())
                            ->class(['col', 'right', 'form-buttons'])
                            ->items([
                                ... My::hiddenFields(),
                                (new Submit('deltplaction', __('Delete selected cache files')))
                                    ->class('delete'),
                            ]),
                    ]),
            ])
        ->render();
    }

    /**
     * Cope with form templates action.
     *
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception
     */
    public static function process(string $checklist): string
    {
        $nextlist = $checklist;
        if (!empty($_POST['deltplaction'])) {
            // Cope with cache file deletion
            try {
                if (empty($_POST['tpl'])) {
                    throw new Exception(__('No cache file selected'));
                }

                $root_cache = Path::real(App::config()->cacheRoot()) . DIRECTORY_SEPARATOR . Template::CACHE_FOLDER . DIRECTORY_SEPARATOR;
                foreach ($_POST['tpl'] as $v) {
                    $cache_file = $root_cache . sprintf('%s' . DIRECTORY_SEPARATOR . '%s', substr((string) $v, 0, 2), substr((string) $v, 2, 2)) . DIRECTORY_SEPARATOR . $v;
                    if (file_exists($cache_file)) {
                        unlink($cache_file);
                    }
                }
            } catch (Exception $e) {
                $nextlist = 'templates';
                App::error()->add($e->getMessage());
            }

            if (!App::error()->flag()) {
                Notices::addSuccessNotice(__('Selected cache files have been deleted.'));
                My::redirect([
                    'tpl' => 1,
                ]);
            }
        }

        return $nextlist;
    }

    public static function check(string $checklist): string
    {
        return empty($_GET['tpl']) ? $checklist : 'templates';
    }
}
