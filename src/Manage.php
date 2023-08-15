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
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Plugin\sysInfo\Helper\AdminUrls;
use Dotclear\Plugin\sysInfo\Helper\Autoload;
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

class Manage extends Process
{
    private static array $checklists = [];
    private static string $checklist = '';

    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        self::$checklists = [
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
                __('Plugins repository (cache)')                  => 'dcrepo-plugins-cache',
                __('Plugins repository')                          => 'dcrepo-plugins',
                __('Plugins repository (alternate repositories)') => 'dcrepo-plugins-alt',
                __('Themes repository (cache)')                   => 'dcrepo-themes-cache',
                __('Themes repository')                           => 'dcrepo-themes',
                __('Themes repository (alternate repositories)')  => 'dcrepo-themes-alt',
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
            self::$checklists[__('3rd party')] = [
                __('Static cache') => 'sc',
            ];
        }

        self::$checklist = !empty($_POST['checklist']) ? $_POST['checklist'] : '';

        // Cope with form submit return
        self::$checklist = Versions::check(self::$checklist);
        self::$checklist = Templates::check(self::$checklist);
        self::$checklist = StaticCache::check(self::$checklist);

        self::$checklist = CoreHelper::downloadReport(self::$checklist);

        // Cope with form submit
        self::$checklist = Versions::process(self::$checklist);
        self::$checklist = Templates::process(self::$checklist);
        self::$checklist = StaticCache::process(self::$checklist);

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        # Get interface setting
        $user_ui_colorsyntax       = dcCore::app()->auth->user_prefs->interface->colorsyntax;
        $user_ui_colorsyntax_theme = dcCore::app()->auth->user_prefs->interface->colorsyntax_theme;

        $head = My::cssLoad('sysinfo.css') .
        Page::jsJson('sysinfo', [
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
        Page::jsModal() .
        My::jsLoad('sysinfo.js');

        if ($user_ui_colorsyntax) {
            $head .= Page::jsLoadCodeMirror($user_ui_colorsyntax_theme);
        }

        Page::openModule(__('System Information'), $head);

        echo Page::breadcrumb(
            [
                __('System')             => '',
                __('System Information') => '',
            ]
        ) .
        Notices::getNotices();

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
                            ->items(self::$checklists)
                            ->default(self::$checklist),
                        (new Submit(['frmsubmit']))
                            ->value(__('Check')),
                        dcCore::app()->formNonce(false),
                    ]),
            ])
            ->render();

        // Display required information
        switch (self::$checklist) {
            case 'autoloader':
                // Affichage des informations relatives à l'autoloader
                echo Autoload::render();

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
                echo Repo::renderPlugins(self::$checklist === 'dcrepo-plugins-cache');

                break;

            case 'dcrepo-plugins-alt':
                // Get list of available plugins (alternate repositories)
                echo Repo::renderAltPlugins();

                break;

            case 'dcrepo-themes':
            case 'dcrepo-themes-cache':
                // Get list of available themes
                echo Repo::renderThemes(self::$checklist === 'dcrepo-themes-cache');

                break;

            case 'dcrepo-themes-alt':
                // Get list of available themes (alternate repositories)
                echo Repo::renderAltThemes();

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

        Page::closeModule();
    }
}
