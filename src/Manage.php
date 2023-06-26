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
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Plugin\sysInfo\Helper\AdminUrls;
use Dotclear\Plugin\sysInfo\Helper\Autoloader;
use Dotclear\Plugin\sysInfo\Helper\Behaviors;
use Dotclear\Plugin\sysInfo\Helper\Constants;
use Dotclear\Plugin\sysInfo\Helper\Folders;
use Dotclear\Plugin\sysInfo\Helper\Formaters;
use Dotclear\Plugin\sysInfo\Helper\Globals;
use Dotclear\Plugin\sysInfo\Helper\Integrity;
use Dotclear\Plugin\sysInfo\Helper\Permissions;
use Dotclear\Plugin\sysInfo\Helper\PhpInfo;
use Dotclear\Plugin\sysInfo\Helper\Plugins;
use Dotclear\Plugin\sysInfo\Helper\Repo;
use Dotclear\Plugin\sysInfo\Helper\Rest;
use Dotclear\Plugin\sysInfo\Helper\StaticCache;
use Dotclear\Plugin\sysInfo\Helper\System;
use Dotclear\Plugin\sysInfo\Helper\Templates;
use Dotclear\Plugin\sysInfo\Helper\TplPaths;
use Dotclear\Plugin\sysInfo\Helper\UrlHandlers;
use Dotclear\Plugin\sysInfo\Helper\Versions;

class Manage extends dcNsProcess
{
    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        static::$init = My::checkContext(My::MANAGE);

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

        if (dcCore::app()->plugins->moduleExists('staticCache') && defined('DC_SC_CACHE_ENABLE') && DC_SC_CACHE_ENABLE && defined('DC_SC_CACHE_DIR')) {
            $checklists[__('3rd party')] = [
                __('Static cache') => 'sc',
            ];
        }

        dcCore::app()->admin->checklists = $checklists;

        $checklist = !empty($_POST['checklist']) ? $_POST['checklist'] : '';

        // Cope with form submit return
        $checklist = Versions::check($checklist);
        $checklist = Templates::check($checklist);
        $checklist = StaticCache::check($checklist);

        $checklist = CoreHelper::downloadReport($checklist);

        dcCore::app()->admin->checklist = $checklist;

        // Cope with form submit
        $checklist = Versions::process($checklist);
        $checklist = Templates::process($checklist);
        $checklist = StaticCache::process($checklist);

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

        $head = dcPage::cssModuleLoad(My::id() . '/css/sysinfo.css', 'screen', dcCore::app()->getVersion(My::id())) .
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
        dcPage::jsModuleLoad(My::id() . '/js/sysinfo.js', dcCore::app()->getVersion(My::id()));
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
                echo Autoloader::render();

                break;

            case 'globals':
                // Affichage de la liste des variables globales
                echo Globals::render();

                break;

            case 'permissions':
                // Affichage de la liste des types de permission enregistrés
                echo Permissions::render();

                break;

            case 'rest':
                // Affichage de la liste des méthodes REST
                echo Rest::render();

                break;

            case 'plugins':
                // Affichage de la liste des plugins (et de leurs propriétés)
                echo Plugins::render();

                break;

            case 'formaters':
                // Affichage de la liste des éditeurs et des syntaxes par éditeur
                echo Formaters::render();

                break;

            case 'constants':
                // Affichage des constantes remarquables de Dotclear
                echo Constants::render();

                break;

            case 'folders':
                // Affichage des dossiers remarquables de Dotclear
                echo Folders::render();

                break;

            case 'integrity':
                // Affichage du contrôle d'intégrité
                echo Integrity::render();

                break;

            case 'behaviours':
                // Récupération des behaviours enregistrées
                echo Behaviors::render();

                break;

            case 'urlhandlers':
                // Récupération des types d'URL enregistrées
                echo UrlHandlers::render();

                break;

            case 'adminurls':
                // Récupération de la liste des URLs d'admin enregistrées
                echo AdminUrls::render();

                break;

            case 'phpinfo':
                // Get PHP Infos
                echo PhpInfo::render();

                break;

            case 'templates':
                // Get list of compiled template's files
                echo Templates::render();

                break;

            case 'tplpaths':
                // Get list of template's paths
                echo TplPaths::render();

                break;

            case 'sc':
                // Get list of existing cache files
                echo StaticCache::render();

                break;

            case 'dcrepo-plugins':
            case 'dcrepo-plugins-cache':
                // Get list of available plugins
                echo Repo::renderPlugins(dcCore::app()->admin->checklist === 'dcrepo-plugins-cache');

                break;

            case 'dcrepo-themes':
            case 'dcrepo-themes-cache':
                // Get list of available themes
                echo Repo::renderThemes(dcCore::app()->admin->checklist === 'dcrepo-themes-cache');

                break;

            case 'versions':
                // Get list of module's versions
                echo Versions::render();

                break;

            case 'report':
                echo CoreHelper::renderReport();

                break;

            default:
                // Display PHP version and DB version
                echo System::render();

                break;
        }

        dcPage::closeModule();
    }
}
