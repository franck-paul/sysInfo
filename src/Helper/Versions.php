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
use Dotclear\Database\Statement\DeleteStatement;
use Dotclear\Database\Statement\UpdateStatement;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Strong;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Module\ModuleDefine;
use Dotclear\Plugin\sysInfo\My;
use Exception;

class Versions
{
    /**
     * Return list of registered versions (core, plugins, themes, …)
     */
    public static function render(): string
    {
        $versions    = App::version()->getVersions();
        $distributed = explode(',', (string) App::config()->distributedPlugins());
        $paths       = explode(PATH_SEPARATOR, (string) App::config()->pluginsRoot());
        $obsoletes   = [
            'blowupConfig',
            'daInstaller',
            'formatting-markdown',
            'magnific-popup',
            'dcrevisions',
            'contactme',
        ];

        // Some plugins may have registered their version with a different case reprensetation of their name
        // which is not a very good idea, but we need to cope with legacy code ;-)
        // Ex: dcRevisions store it as 'dcrevisions'
        // So we will check by ignoring case
        $plugins  = array_map(static fn (int|string $name): string => mb_strtolower((string) $name), array_keys(App::plugins()->getDefines(['state' => ModuleDefine::STATE_ENABLED], true)));
        $disabled = array_map(static fn (int|string $name): string => mb_strtolower((string) $name), array_keys(App::plugins()->getDefines(['state' => '!' . ModuleDefine::STATE_ENABLED], true)));

        $rows = [];
        App::lexical()->lexicalKeySort($versions, App::lexical()::ADMIN_LOCALE);
        foreach ($versions as $module => $version) {
            $status = [];
            $class  = [];
            $name   = $module;
            $strong = false;

            $checkbox = (new Checkbox(['ver[]'], false))
                ->value($module);

            $input = (new Input(['m[' . $module . ']']))
                ->value($version);

            if ($module === 'core') {
                $class[]  = 'version-core';
                $name     = '<strong>' . $module . '</strong>';
                $strong   = true;
                $status[] = __('Core');
                $checkbox->disabled(true);  // Do not delete core version
                $input->disabled(true);     // Do not modify core version
            } else {
                if (in_array($module, $distributed)) {
                    $class[]  = 'version-distrib';
                    $status[] = __('Distributed');
                    $checkbox->disabled(true);  // Do not delete distributed module version
                    $input->disabled(true);     // Do not modify distributed module version
                }

                if (!in_array(mb_strtolower((string) $module), $plugins)) {
                    // Not in activated plugins list
                    if (in_array(mb_strtolower((string) $module), $disabled)) {
                        // In disabled plugins list
                        $exists = true;
                    } else {
                        $exists = false;
                        // Look if the module exists in one of specified plugins paths
                        foreach ($paths as $path) {
                            if (is_dir($path . DIRECTORY_SEPARATOR . $module)) {
                                $exists = true;

                                break;
                            }
                        }
                    }

                    if ($exists) {
                        $class[]  = 'version-disabled';
                        $status[] = __('Disabled');
                    } elseif (in_array($module, $obsoletes, true)) {
                        $class[]  = 'version-obsolete';
                        $status[] = __('Obsolete');
                    } else {
                        $class[]  = 'version-unknown';
                        $status[] = __('Not found but may exist');
                    }
                }
            }
            $rows[] = (new Tr())
                ->class($class)
                ->cols([
                    (new Td())
                        ->items([
                            $checkbox->label(new Label($strong ? (new Strong($name))->render() : $name, Label::IL_FT)),
                        ]),
                    (new Td())
                        ->class('nowrap')
                        ->items([
                            $input,
                        ]),
                    (new Td())
                        ->class('nowrap')
                        ->text(implode(', ', $status)),
                ]);
        }

        return (new Form('verform'))
            ->method('post')
            ->action(App::backend()->getPageURL())
            ->fields([
                (new Table('versions'))
                    ->class('sysinfo')
                    ->caption(new Caption(__('List of versions registered in the database') . ' (' . sprintf('%d', count($versions)) . ')'))
                    ->thead((new Thead())
                        ->rows([
                            (new Tr())
                                ->cols([
                                    (new Th())
                                        ->scope('col')
                                        ->text(__('Module')),
                                    (new Th())
                                        ->scope('col')
                                        ->class('nowrap')
                                        ->text(__('Version')),
                                    (new Th())
                                        ->scope('col')
                                        ->class('nowrap')
                                        ->text(__('Status')),
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
                                (new Submit('updveraction', __('Update versions'))),
                                (new Submit('delveraction', __('Delete selected versions')))
                                    ->class('delete'),
                            ]),
                    ]),
            ])
        ->render();
    }

    /**
     * Cope with form versions action.
     *
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception
     */
    public static function process(string $checklist): string
    {
        $nextlist = $checklist;
        if (!empty($_POST['delveraction'])) {
            // Cope with versions deletion
            try {
                if (empty($_POST['ver'])) {
                    throw new Exception(__('No version selected'));
                }

                $list = [];
                foreach ($_POST['ver'] as $v) {
                    $list[] = $v;
                }

                $sql = new DeleteStatement();
                $sql
                    ->from(App::db()->con()->prefix() . App::version()::VERSION_TABLE_NAME)
                    ->where('module' . $sql->in($list));
                $sql->delete();
            } catch (Exception $e) {
                $checklist = 'versions';
                App::error()->add($e->getMessage());
            }

            if (!App::error()->flag()) {
                Notices::addSuccessNotice(__('Selected versions have been deleted.'));
                My::redirect([
                    'ver' => 1,
                ]);
            }
        }

        if (!empty($_POST['updveraction'])) {
            // Cope with versions update
            try {
                $sql = new UpdateStatement();
                $sql
                    ->ref(App::db()->con()->prefix() . App::version()::VERSION_TABLE_NAME);
                foreach ($_POST['m'] as $module => $version) {
                    $sql
                        ->set('version = ' . $sql->quote($version), true)   // Reset value
                        ->where('module = ' . $sql->quote($module), true)   // Reset condition
                        ->update();
                }
            } catch (Exception $e) {
                $nextlist = 'versions';
                App::error()->add($e->getMessage());
            }

            if (!App::error()->flag()) {
                Notices::addSuccessNotice(__('Versions have been updated.'));
                My::redirect([
                    'ver' => 1,
                ]);
            }
        }

        return $nextlist;
    }

    public static function check(string $checklist): string
    {
        return empty($_GET['ver']) ? $checklist : 'versions';
    }
}
