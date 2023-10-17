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

use dcCore;
use dcModuleDefine;
use dcUtils;
use Dotclear\Core\Backend\Notices;
use Dotclear\Database\Statement\DeleteStatement;
use Dotclear\Database\Statement\UpdateStatement;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Plugin\sysInfo\My;
use Exception;

class Versions
{
    /**
     * Return list of registered versions (core, plugins, themes, …)
     *
     * @return     string
     */
    public static function render(): string
    {
        $versions    = dcCore::app()->getVersions();
        $distributed = explode(',', DC_DISTRIB_PLUGINS);
        $paths       = explode(PATH_SEPARATOR, DC_PLUGINS_ROOT);
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
        $plugins  = array_map(fn ($name): string => mb_strtolower((string) $name), array_values(array_keys(dcCore::app()->plugins->getDefines(['state' => dcModuleDefine::STATE_ENABLED], true))));
        $disabled = array_map(fn ($name): string => mb_strtolower((string) $name), array_values(array_keys(dcCore::app()->plugins->getDefines(['state' => '!' . dcModuleDefine::STATE_ENABLED], true))));

        $str = '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post" id="verform">' .
            '<table id="chk-table-result" class="sysinfo">' .
            '<caption>' . __('List of versions registered in the database') . ' (' . sprintf('%d', count($versions)) . ')' . '</caption>' .   // @phpstan-ignore-line
            '<thead>' .
            '<tr>' .
            '<th scope="col" class="">' . __('Module') . '</th>' .
            '<th scope="col" class="nowrap">' . __('Version') . '</th>' .
            '<th scope="col" class="nowrap">' . __('Status') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>';

        dcUtils::lexicalKeySort($versions);
        foreach ($versions as $module => $version) {
            $status   = [];
            $class    = [];
            $name     = $module;
            $checkbox = (new Checkbox(['ver[]'], false))->value($module);
            $input    = (new Input(['m[' . $module . ']']))->value($version);

            if ($module === 'core') {
                $class[]  = 'version-core';
                $name     = '<strong>' . $module . '</strong>';
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
                if (!in_array(mb_strtolower($module), $plugins)) {
                    // Not in activated plugins list
                    if (in_array(mb_strtolower($module), $disabled)) {
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
            $str .= '<tr class="' . implode(' ', $class) . '">' .
                '<td class="">' . $checkbox->render() . ' ' . $name . '</td>' .
                '<td class="nowrap">' . $input->render() . '</td>' .
                '<td class="nowrap">' . implode(', ', $status) . '</td>' .
                '</tr>';
        }
        $str .= '</tbody></table>' .
            '<div class="two-cols">' .
            '<p class="col checkboxes-helpers"></p>' .
            '<p class="col right">' .
            My::parsedHiddenFields() .
            (new Submit('updveraction', __('Update versions')))->render() . ' ' .
            (new Submit('delveraction', __('Delete selected versions')))->class('delete')->render() .
            '</p>' .
            '</div>' .
            '</form>';

        return $str;
    }

    /**
     * Cope with form versions action.
     *
     * @param      string     $checklist  The checklist
     *
     * @throws     Exception
     *
     * @return  string
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
                    ->from(dcCore::app()->prefix . dcCore::VERSION_TABLE_NAME)
                    ->where('module' . $sql->in($list));
                $sql->delete();
            } catch (Exception $e) {
                $checklist = 'versions';
                dcCore::app()->error->add($e->getMessage());
            }
            if (!dcCore::app()->error->flag()) {
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
                    ->ref(dcCore::app()->prefix . dcCore::VERSION_TABLE_NAME);
                foreach ($_POST['m'] as $module => $version) {
                    $sql
                        ->set('version = ' . $sql->quote($version), true)   // Reset value
                        ->where('module = ' . $sql->quote($module), true)   // Reset condition
                        ->update();
                }
            } catch (Exception $e) {
                $nextlist = 'versions';
                dcCore::app()->error->add($e->getMessage());
            }
            if (!dcCore::app()->error->flag()) {
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
        return !empty($_GET['ver']) ? 'versions' : $checklist;
    }
}
