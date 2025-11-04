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
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Plugin\sysInfo\CoreHelper;
use Dotclear\Plugin\sysInfo\My;
use Exception;

class Undigest
{
    /**
     * Check Dotclear un-digest (find PHP files not in digest)
     */
    public static function render(): string
    {
        $released       = [];
        $list_primary   = [];
        $list_secondary = [];
        $unattended     = [];
        $root           = App::config()->dotclearRoot();

        $folders = [
            'admin',
            'inc',
            'locales',
            'src',
        ];
        // Add distributed plugins
        foreach (explode(',', (string) App::config()->distributedPlugins()) as $theme) {
            $folders[] = implode(DIRECTORY_SEPARATOR, ['plugins', $theme]);
        }
        // Add distributed themes
        foreach (explode(',', (string) App::config()->distributedThemes()) as $theme) {
            $folders[] = implode(DIRECTORY_SEPARATOR, ['themes', $theme]);
        }

        // Folders to ignore during scanDir
        $ignore = [
        ];

        // Primary extensions to find
        $ext_primary = [
            'php',
            'tpl',
        ];
        // Sub-extensions to ignore
        $ignore_ext_primary = [
            '.lang.php',
        ];

        // Secondary extensions to find
        $ext_secondary = [
            'css',
            'dat',
            'gif',
            'htaccess',
            'html',
            'ico',
            'in',
            'jpg',
            'js',
            'json',
            'md',
            'mjs',
            'pdf',
            'png',
            'po',
            'pot',
            'scss',
            'svg',
            'txt',
            'woff2',
            'xml',
            'xsl',
        ];

        // Suffixes to find for each extensions
        $ext_suffixes = [
            '-OLD',
        ];

        // Files to ignore after scanDir (primary or secondary)
        $ignore_files = [
            Path::real(implode(DIRECTORY_SEPARATOR, [$root, 'admin', 'js', 'jquery', 'jquery-ui.md'])), // Git ignored
            Path::real(implode(DIRECTORY_SEPARATOR, [$root, 'inc', 'config.php'])),
            Path::real(implode(DIRECTORY_SEPARATOR, [$root, 'inc', 'oauth2.php'])),
            Path::real(implode(DIRECTORY_SEPARATOR, [$root, 'inc', 'core', '_fake_l10n.php'])),
        ];

        // Folders to ignore after scanDir (primary or secondary)
        $ignore_folders = [
            Path::real(implode(DIRECTORY_SEPARATOR, [$root, 'admin', 'style', 'scss'])),    // Removed in Makefile
            Path::real(implode(DIRECTORY_SEPARATOR, [$root, 'themes', 'berlin', 'scss'])),  // Removed in Makefile
        ];

        // Add optional locales to ignored folders
        $locales_folders = glob((string) Path::real(implode(DIRECTORY_SEPARATOR, [$root, 'locales', '*']), false), GLOB_ONLYDIR);
        if ($locales_folders) {
            $keep_locales_folders = [
                Path::real(implode(DIRECTORY_SEPARATOR, [$root, 'locales', 'en'])), // Keep in Makefile
                Path::real(implode(DIRECTORY_SEPARATOR, [$root, 'locales', 'fr'])), // Keep in Makefile
            ];
            foreach ($locales_folders as $locales_folder) {
                if (!in_array($locales_folder, $keep_locales_folders)) {
                    $ignore_folders[] = $locales_folder;
                }
            }
        }

        $keep_file = function (string $filename) use ($ignore_files, $ignore_folders): bool {
            if (in_array($filename, $ignore_files)) {
                return false;
            }
            foreach ($ignore_folders as $folder) {
                if ($folder && str_starts_with($filename, $folder)) {
                    return false;
                }
            }

            return true;
        };

        $rows = [];

        // Get list of files in digest
        $digests_file = implode(DIRECTORY_SEPARATOR, [App::config()->dotclearRoot(), 'inc', 'digests']);
        if (is_readable($digests_file)) {
            $contents = file($digests_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($contents !== false) {
                foreach ($contents as $digest) {
                    if (!preg_match('#^([\da-f]{32})\s+(.+)$#', $digest, $m)) {
                        continue;
                    }
                    $released[] = Path::real(implode(DIRECTORY_SEPARATOR, [$root,$m[2]]));
                }
                if ($released !== []) {
                    foreach ($folders as $folder) {
                        $list_primary = self::scanDir(
                            implode(DIRECTORY_SEPARATOR, [$root, $folder]),
                            $list_primary,
                            $ext_primary,
                            $ignore,
                            $ignore_ext_primary,
                            $ext_suffixes
                        );
                        $list_secondary = self::scanDir(
                            implode(DIRECTORY_SEPARATOR, [$root, $folder]),
                            $list_secondary,
                            $ext_secondary,
                            $ignore
                        );
                    }
                    if ($list_primary !== []) {
                        foreach ($list_primary as $filename) {
                            if (!in_array($filename, $released) && $keep_file($filename)) {
                                $unattended[] = $filename;
                            }
                        }
                        if ($unattended !== []) {
                            foreach ($unattended as $filename) {
                                $rows[] = (new Tr())
                                    ->cols([
                                        (new Td())
                                            ->class('nowrap')
                                            ->items([
                                                (new Checkbox(['ud[]'], false))
                                                    ->value($filename)
                                                    ->label(new Label(CoreHelper::simplifyFilename($filename), Label::IL_FT)),
                                            ]),
                                    ]);
                            }
                        } else {
                            $rows[] = (new Tr())
                                ->cols([
                                    (new Td())
                                        ->text(__('Nothing unexpected or additional found.')),
                                ]);
                        }
                    }

                    // Second part
                    $unattended = [];
                    $rows[]     = (new Tr())
                        ->cols([
                            (new Th())
                                ->scope('col')
                                ->text(__('File') . ' (' . implode(', ', $ext_secondary) . ')'),
                        ]);
                    if ($list_secondary !== []) {
                        foreach ($list_secondary as $filename) {
                            if (!in_array($filename, $released) && $keep_file($filename)) {
                                $unattended[] = $filename;
                            }
                        }
                        if ($unattended !== []) {
                            foreach ($unattended as $filename) {
                                $rows[] = (new Tr())
                                    ->cols([
                                        (new Td())
                                            ->class('nowrap')
                                            ->items([
                                                (new Checkbox(['ud[]'], false))
                                                    ->value($filename)
                                                    ->label(new Label(CoreHelper::simplifyFilename($filename), Label::IL_FT)),
                                            ]),
                                    ]);
                            }
                        } else {
                            $rows[] = (new Tr())
                                ->cols([
                                    (new Td())
                                        ->text(__('Nothing unexpected or additional found.')),
                                ]);
                        }
                    }
                }
            } else {
                $rows[] = (new Tr())
                    ->cols([
                        (new Td())
                            ->text(__('Unable to read digests file.')),
                    ]);
            }
        } else {
            $rows[] = (new Tr())
                ->cols([
                    (new Td())
                        ->text(__('Unable to read digests file.')),
                ]);
        }

        return (new Form('udform'))
            ->method('post')
            ->action(App::backend()->getPageURL())
            ->fields([
                (new Table('undigest'))
                    ->class('sysinfo')
                    ->caption(new Caption(__('Unexpected or additional files')))
                    ->thead((new Thead())
                        ->rows([
                            (new Tr())
                                ->cols([
                                    (new Th())
                                        ->scope('col')
                                        ->text(__('File') . ' (' . implode(', ', $ext_primary) . ')'),
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
                                (new Submit('deludaction', __('Delete selected unexpected files')))
                                    ->class('delete'),
                            ]),
                    ]),
            ])
        ->render();
    }

    /**
     * Scan recursively a directory and found only some files with specific extensions.
     *
     * @param   string              $path           The directory path to scan
     * @param   array<int,string>   $stack          The paths stack
     * @param   array<int,string>   $ext            The extensions to find
     * @param   array<int,string>   $ignore         The folders to ignore
     * @param   array<int,string>   $ignore_ext     The extensions to ignore
     * @param   array<int,string>   $suffixes       The suffixes to also find
     *
     * @return  array<int,string>   The paths stack
     */
    private static function scanDir(string $path, array $stack = [], array $ext = [], array $ignore = [], array $ignore_ext = [], array $suffixes = []): array
    {
        $path = Path::real($path);
        if ($path === false || !is_dir($path) || !is_readable($path)) {
            return [];
        }
        $files = Files::scandir($path);

        foreach ($files as $file) {
            // Ignore all hidden items (starting with a dot)
            if (str_starts_with($file, '.')) {
                continue;
            }
            if (in_array($file, $ignore)) {
                continue;
            }
            if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                $stack = self::scanDir($path . DIRECTORY_SEPARATOR . $file, $stack, $ext, $ignore, $ignore_ext, $suffixes);
            } else {
                $pathname = implode(DIRECTORY_SEPARATOR, [$path, $file]);
                $info     = pathinfo($pathname, PATHINFO_EXTENSION);

                if (in_array($info, $ext)) {
                    // Check if not in extensions to ignore list
                    $keep = true;
                    foreach ($ignore_ext as $needle) {
                        if (str_ends_with($file, $needle)) {
                            $keep = false;

                            break;
                        }
                    }
                    if ($keep) {
                        $stack[] = $pathname;
                    }
                } else {
                    // Check for suffixes
                    $keep = false;
                    foreach ($suffixes as $suffix) {
                        if (str_ends_with($file, $suffix)) {
                            $keep = true;

                            break;
                        }
                    }
                    if ($keep) {
                        $stack[] = $pathname;
                    }
                }
            }
        }

        return $stack;
    }

    /**
     * Cope with undigest form action.
     *
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception
     */
    public static function process(string $checklist): string
    {
        $nextlist = $checklist;
        if (!empty($_POST['deludaction'])) {
            // Cope with static cache file deletion
            try {
                if (empty($_POST['ud'])) {
                    throw new Exception(__('No unexpected file selected'));
                }

                foreach ($_POST['ud'] as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            } catch (Exception $e) {
                $nextlist = 'undigest';
                App::error()->add($e->getMessage());
            }

            if (!App::error()->flag()) {
                App::backend()->notices()->addSuccessNotice(__('Selected unexpected files have been deleted.'));
                My::redirect([
                    'ud' => 1,
                ]);
            }
        }

        return $nextlist;
    }

    public static function check(string $checklist): string
    {
        return empty($_GET['ud']) ? $checklist : 'undigest';
    }
}
