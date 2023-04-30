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

use dcCore;
use dcNsProcess;
use dcPage;
use dcStaticCacheControl;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Submit;

class Manage extends dcNsProcess
{
    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        // Manageable only by super-admin
        static::$init = defined('DC_CONTEXT_ADMIN')
            && dcCore::app()->auth->isSuperAdmin()
            && My::phpCompliant();

        return static::$init;
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        $checklists = [
            __('System') => [
                __('Information')  => 'default',
                __('PHP info')     => 'phpinfo',
                __('DC Constants') => 'constants',
                __('Globals')      => 'globals',
                __('Folders')      => 'folders',
                __('Integrity')    => 'integrity',
                __('Autoloader')   => 'autoloader',
            ],

            __('Core') => [
                __('URL handlers')        => 'urlhandlers',
                __('Behaviours')          => 'behaviours',
                __('Admin URLs')          => 'adminurls',
                __('Types of permission') => 'permissions',
            ],

            __('Templates') => [
                __('Compiled templates') => 'templates',
                __('Template paths')     => 'tplpaths',
            ],

            __('Repositories') => [
                __('Plugins repository (cache)') => 'dcrepo-plugins-cache',
                __('Plugins repository')         => 'dcrepo-plugins',
                __('Themes repository (cache)')  => 'dcrepo-themes-cache',
                __('Themes repository')          => 'dcrepo-themes',
            ],

            __('Miscellaneous') => [
                __('Plugins')              => 'plugins',
                __('Editors and Syntaxes') => 'formaters',
                __('REST methods')         => 'rest',
                __('Versions')             => 'versions',
            ],

            __('Report') => [
                __('Full report') => 'report',
            ],
        ];

        if (dcCore::app()->plugins->moduleExists('staticCache') && defined('DC_SC_CACHE_ENABLE') && DC_SC_CACHE_ENABLE && defined('DC_SC_CACHE_DIR') && dcStaticCacheControl::cacheCurrentBlog()) {
            $checklists[__('3rd party')] = [
                __('Static cache') => 'sc',
            ];
        }

        dcCore::app()->admin->checklists = $checklists;

        $checklist = !empty($_POST['checklist']) ? $_POST['checklist'] : '';

        // Cope with form submit return
        $checklist = Helper::doCheckVersions($checklist);
        $checklist = Helper::doCheckTemplates($checklist);
        $checklist = Helper::doCheckStaticCache($checklist);
        $checklist = Helper::doReport($checklist);

        dcCore::app()->admin->checklist = $checklist;

        // Cope with form submit
        $checklist = Helper::doFormVersions($checklist);
        $checklist = Helper::doFormTemplates($checklist);
        $checklist = Helper::doFormStaticCache($checklist);

        dcCore::app()->admin->checklist = $checklist;

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        # Get interface setting
        $user_ui_colorsyntax       = dcCore::app()->auth->user_prefs->interface->colorsyntax;
        $user_ui_colorsyntax_theme = dcCore::app()->auth->user_prefs->interface->colorsyntax_theme;

        $head = dcPage::cssModuleLoad('sysInfo/css/sysinfo.css', 'screen', dcCore::app()->getVersion('sysInfo')) .
        dcPage::jsJson('sysinfo', [
            'colorsyntax'       => $user_ui_colorsyntax,
            'colorsyntax_theme' => $user_ui_colorsyntax_theme,
            'msg'               => [
                'confirm_del_tpl' => __('Are you sure you want to remove selected template cache files?'),
                'confirm_del_ver' => __('Are you sure you want to remove selected versions from database?'),
                'confirm_del_sc'  => __('Are you sure you want to remove selected static cache files?'),
                'tpl_not_found'   => __('Compiled template file not found or unreadable'),
                'sc_not_found'    => __('Static cache file not found or unreadable'),
            ],
        ]) .
        dcPage::jsModal() .
        dcPage::jsModuleLoad('sysInfo/js/sysinfo.js', dcCore::app()->getVersion('sysInfo'));
        if ($user_ui_colorsyntax) {
            $head .= dcPage::jsLoadCodeMirror($user_ui_colorsyntax_theme);
        }

        dcPage::openModule(__('System Information'), $head);

        echo dcPage::breadcrumb(
            [
                __('System')             => '',
                __('System Information') => '',
            ]
        ) .
        dcPage::notices();

        echo
        (new Form('frmchecklist'))
            ->action(dcCore::app()->admin->getPageURL())
            ->method('post')
            ->fields([
                (new Para())
                    ->separator(' ')
                    ->class('field')
                    ->items([
                        (new Label(__('Select a checklist:')))
                            ->for('checklist'),
                        (new Select('checklist'))
                            ->items(dcCore::app()->admin->checklists)
                            ->default(dcCore::app()->admin->checklist),
                        (new Submit(['frmsubmit']))
                            ->value(__('Check')),
                        dcCore::app()->formNonce(false),
                    ]),
            ])
            ->render();

        // Display required information
        switch (dcCore::app()->admin->checklist) {
            case 'autoloader':
                // Affichage des informations relatives à l'autoloader
                echo Helper::autoloader();

                break;

            case 'globals':
                // Affichage de la liste des variables globales
                echo Helper::globals();

                break;

            case 'permissions':
                // Affichage de la liste des types de permission enregistrés
                echo Helper::permissions();

                break;

            case 'rest':
                // Affichage de la liste des méthodes REST
                echo Helper::restMethods();

                break;

            case 'plugins':
                // Affichage de la liste des plugins (et de leurs propriétés)
                echo Helper::plugins();

                break;

            case 'formaters':
                // Affichage de la liste des éditeurs et des syntaxes par éditeur
                echo Helper::formaters();

                break;

            case 'constants':
                // Affichage des constantes remarquables de Dotclear
                echo Helper::dcConstants();

                break;

            case 'folders':
                // Affichage des dossiers remarquables de Dotclear
                echo Helper::folders();

                break;

            case 'integrity':
                // Affichage du contrôle d'intégrité
                echo Helper::integrity();

                break;

            case 'behaviours':
                // Récupération des behaviours enregistrées
                echo Helper::behaviours();

                break;

            case 'urlhandlers':
                // Récupération des types d'URL enregistrées
                echo Helper::URLHandlers();

                break;

            case 'adminurls':
                // Récupération de la liste des URLs d'admin enregistrées
                echo Helper::adminURLs();

                break;

            case 'phpinfo':
                // Get PHP Infos
                echo Helper::phpInfo();

                break;

            case 'templates':
                // Get list of compiled template's files
                echo Helper::templates();

                break;

            case 'tplpaths':
                // Get list of template's paths
                echo Helper::tplPaths();

                break;

            case 'sc':
                // Get list of existing cache files
                echo Helper::staticCache();

                break;

            case 'dcrepo-plugins':
            case 'dcrepo-plugins-cache':
                // Get list of available plugins
                echo Helper::repoPlugins(dcCore::app()->admin->checklist === 'dcrepo-plugins-cache');

                break;

            case 'dcrepo-themes':
            case 'dcrepo-themes-cache':
                // Get list of available themes
                echo Helper::repoThemes(dcCore::app()->admin->checklist === 'dcrepo-themes-cache');

                break;

            case 'versions':
                // Get list of module's versions
                echo Helper::versions();

                break;

            case 'report':
                echo Helper::report();

                break;

            default:
                // Display PHP version and DB version
                echo Helper::quoteVersions();

                break;
        }

        dcPage::closeModule();
    }
}
