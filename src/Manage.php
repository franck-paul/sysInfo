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

use Dotclear\App;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Plugin\sysInfo\Helper\AdminUrls;
use Dotclear\Plugin\sysInfo\Helper\AntispamFilters;
use Dotclear\Plugin\sysInfo\Helper\Autoload;
use Dotclear\Plugin\sysInfo\Helper\Behaviors;
use Dotclear\Plugin\sysInfo\Helper\Configuration;
use Dotclear\Plugin\sysInfo\Helper\Constants;
use Dotclear\Plugin\sysInfo\Helper\Exceptions;
use Dotclear\Plugin\sysInfo\Helper\Folders;
use Dotclear\Plugin\sysInfo\Helper\Formaters;
use Dotclear\Plugin\sysInfo\Helper\Globals;
use Dotclear\Plugin\sysInfo\Helper\Integrity;
use Dotclear\Plugin\sysInfo\Helper\Locales;
use Dotclear\Plugin\sysInfo\Helper\Permissions;
use Dotclear\Plugin\sysInfo\Helper\PhpInfo;
use Dotclear\Plugin\sysInfo\Helper\Plugins;
use Dotclear\Plugin\sysInfo\Helper\PostTypes;
use Dotclear\Plugin\sysInfo\Helper\Repo;
use Dotclear\Plugin\sysInfo\Helper\Rest;
use Dotclear\Plugin\sysInfo\Helper\StaticCache;
use Dotclear\Plugin\sysInfo\Helper\Statuses;
use Dotclear\Plugin\sysInfo\Helper\System;
use Dotclear\Plugin\sysInfo\Helper\Templates;
use Dotclear\Plugin\sysInfo\Helper\Thumbnails;
use Dotclear\Plugin\sysInfo\Helper\TplPaths;
use Dotclear\Plugin\sysInfo\Helper\Undigest;
use Dotclear\Plugin\sysInfo\Helper\UrlHandlers;
use Dotclear\Plugin\sysInfo\Helper\Versions;

class Manage extends Process
{
    /**
     * @var array<string, array<string, string>>
     */
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
                __('Information')      => 'default',
                __('PHP info')         => 'phpinfo',
                __('DC Configuration') => 'config',
                __('DC Constants')     => 'constants',
                __('Globals')          => 'globals',
                __('Folders')          => 'folders',
                __('Integrity')        => 'integrity',
                __('Unexpected')       => 'undigest',
                __('Autoloader')       => 'autoloader',
            ],

            __('Core') => [
                __('URL handlers')        => 'urlhandlers',
                __('Behaviours')          => 'behaviours',
                __('Admin URLs')          => 'adminurls',
                __('Types of permission') => 'permissions',
                __('Entry types')         => 'posttypes',
                __('Statuses')            => 'statuses',
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
                __('Locales')              => 'locales',
                __('Thumbnails')           => 'thumbnails',
            ],

            __('Report') => [
                __('Full report') => 'report',
            ],
        ];

        if (class_exists('\\Dotclear\\Exception\\ExceptionEnum')) {
            self::$checklists[__('Miscellaneous')][__('Exceptions')] = 'exceptions';
        }

        if (App::plugins()->moduleExists('antispam')) {
            self::$checklists[__('Miscellaneous')][__('Antispam filters')] = 'antispamfilters';
        }

        if (App::plugins()->moduleExists('staticCache') && defined('DC_SC_CACHE_DIR')) {
            self::$checklists[__('3rd party')] = [
                __('Static cache') => 'sc',
            ];
        }

        self::$checklist = empty($_POST['checklist']) ? '' : $_POST['checklist'];

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
        $user_ui_colorsyntax       = App::auth()->prefs()->interface->colorsyntax;
        $user_ui_colorsyntax_theme = App::auth()->prefs()->interface->colorsyntax_theme;

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

        Page::openModule(My::name(), $head);

        echo Page::breadcrumb(
            [
                __('System')             => '',
                __('System Information') => '',
            ]
        ) .
        Notices::getNotices();

        echo
        (new Form('frmchecklist'))
            ->action(App::backend()->getPageURL())
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
                        ...My::hiddenFields(),
                    ]),
            ])
            ->render();

        // Display required information
        echo match (self::$checklist) {
            // Affichage des informations relatives à l'autoloader
            'autoloader' => Autoload::render(),

            // Affichage de la liste des variables globales
            'globals' => Globals::render(),

            // Affichage de la liste des types de permission enregistrés
            'permissions' => Permissions::render(),

            // Affichage de la liste des méthodes REST
            'rest' => Rest::render(),

            // Affichage de la liste des plugins (et de leurs propriétés)
            'plugins' => Plugins::render(),

            // Affichage de la liste des éditeurs et des syntaxes par éditeur
            'formaters' => Formaters::render(),

            // Affichage de la configuration de Dotclear
            'config' => Configuration::render(),

            // Affichage des constantes remarquables de Dotclear
            'constants' => Constants::render(),

            // Affichage des dossiers remarquables de Dotclear
            'folders' => Folders::render(),

            // Affichage du contrôle d'intégrité
            'integrity' => Integrity::render(),

            // Affichage des fichiers non attendus
            'undigest' => Undigest::render(),

            // Récupération des behaviours enregistrées
            'behaviours' => Behaviors::render(),

            // Récupération des types d'URL enregistrées
            'urlhandlers' => UrlHandlers::render(),

            // Récupération de la liste des URLs d'admin enregistrées
            'adminurls' => AdminUrls::render(),

            // Get PHP Infos
            'phpinfo' => PhpInfo::render(),

            // Get list of compiled template's files
            'templates' => Templates::render(),

            // Get list of template's paths
            'tplpaths' => TplPaths::render(),

            // Get list of existing cache files
            'sc' => StaticCache::render(),

            // Get list of available plugins
            'dcrepo-plugins', 'dcrepo-plugins-cache' => Repo::renderPlugins(self::$checklist === 'dcrepo-plugins-cache'),

            // Get list of available plugins (alternate repositories)
            'dcrepo-plugins-alt' => Repo::renderAltPlugins(),

            // Get list of available themes
            'dcrepo-themes', 'dcrepo-themes-cache' => Repo::renderThemes(self::$checklist === 'dcrepo-themes-cache'),

            // Get list of available themes (alternate repositories)
            'dcrepo-themes-alt' => Repo::renderAltThemes(),

            // Get list of module's versions
            'versions' => Versions::render(),

            // Get list of Antispam filters
            'antispamfilters' => AntispamFilters::render(),

            // Report
            'report' => CoreHelper::renderReport(),

            // Get list of exceptions
            'exceptions' => Exceptions::render(),

            // Get list of statuses
            'statuses' => Statuses::render(),

            // Get entry types
            'posttypes' => PostTypes::render(),

            // Get current locales
            'locales' => Locales::render(),

            // Get list of thumbnails sizes
            'thumbnails' => Thumbnails::render(),

            // Display PHP version and DB version
            default => System::render()
        };

        Page::closeModule();
    }
}
