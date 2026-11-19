# Changelog

## 17.0 — 2026-11-08

- DC 2.40+
- Use PostType getters rather than deprecated get()
- Cope with RTL

## 16.2 — 2026-09-08

- Use name rather than behavior for column header in beheviors list

## 16.1.1 — 2026-08-19

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 16.1 — 2026-08-19

- Add some links about attic available versions (download and information)

## 16.0.1 — 2026-08-17

- Enforces some verification before registering module

## 16.0 — 2026-08-03

- Prevent usage of unknown xdebug functions
- No need to display empty array (global vars)
- Avoid using magic methods for user preferences and blog settings (2.39+)
- Again
- Fav Icon
- Review icon colors (DC 2.39+)

## 15.3 — 2026-06-10

- Use config methods to get plugins/themes store URL

## 15.2 — 2026-06-02

- Add repo list (DC/3rd party) from cache only

## 15.1 — 2026-05-15

- Add support of glaz template set

## 15.0 — 2026-05-13

- Add DC_MODERN and co
- Better this way

## 14.30 — 2026-05-06

- Harmonize usage of App::blogWorkspace()::NS_... and App::userWorkspace()::WS_... (and use short version of constant name for types)

## 14.29 — 2026-04-23

- Add an option to switch the HTTP cache

## 14.28 — 2026-04-21

- Add an option to switch the cache of template engine

## 14.27 — 2026-04-20

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 14.26 — 2026-04-19

- Cope with refined doc of Modules::getDefines()

## 14.25 — 2026-03-25

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 14.24 — 2026-02-26

- DA -&gt; DC

## 14.23 — 2026-01-04

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 14.22 — 2025-12-29

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 14.21 — 2025-12-01

- Add direct access from static cache URL calculator to hierarchy

## 14.20 — 2025-12-01

- Set position sticky to static cache calculator fieldset (easier to find corresponding file in hierarchy this way)

## 14.19 — 2025-11-24

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 14.18.2 — 2025-11-18

- Fix dynamic list of folders and sub-folders for static cache (was restricted to 1st one only)

## 14.18 — 2025-11-04

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 14.17 — 2025-10-29

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 14.16 — 2025-10-16

- Use dataset property rather than getAttribute('data-…') method

## 14.15 — 2025-10-07

- No longer need to instantiate Utility.

## 14.14 — 2025-09-30

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 14.13 — 2025-09-22

- Use backend services rather than static calls

## 14.12 — 2025-09-21

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 14.11.1 — 2025-09-18

- Cope with PDO database driver if necessary

## 14.11 — 2025-09-18

- Add DB drivers ID (as 1st column)

## 14.10 — 2025-09-18

- Review list of exceptions

## 14.9 — 2025-09-14

- Update stats (public), fix status checking depending on context

## 14.8 — 2025-09-11

- Review context checking: super admin only for all module
- Add value type for global variables

## 14.7 — 2025-09-08

- Add factory stats to debug info (frontend)

## 14.6 — 2025-09-07

- DC 2.36 compliance

## 14.5 — 2025-09-06

- DC 2.36 compliance

## 14.4 — 2025-08-31

- Add DC_AUTH_PASSWORD_ONLY constant

## 14.3 — 2025-08-31

- Add list of db drivers

## 14.2 — 2025-08-31

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 14.1 — 2025-08-29

- Add required PHP modules

## 14.0 — 2025-08-27

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 13.2 — 2025-08-08

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 13.1 — 2025-07-20

- Do not embed debug information on non HTML page

## 13.0 — 2025-07-14

- Cope with theme overload system, DC 2.35+

## 12.10 — 2025-06-10

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 12.9 — 2025-05-28

- Add keyboard event management (Enter key) to header

## 12.8 — 2025-05-27

- Use localeCompare for string comparison

## 12.7 — 2025-05-20

- Forgot this label
- Add an option to display debug info on frontend only if an administrator (or super) is currently connected
- Add link for blog parameters 3rd party settings

## 12.6 — 2025-05-18

- Add debug information display on public pages (activated via blog parameters, 3rd party settings)

## 12.5 — 2025-05-05

- Use Span(…) rather than Text('span', …)

## 12.4 — 2025-05-05

- Use Strong(…) rather than Text('strong', …)

## 12.3 — 2025-04-19

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 12.2 — 2025-04-19

- Set some specific sorting
- Add DC_MEDIA_UPDATE_DB_LIMIT constant and mediaUpdateDBLimit() config
- Add media thumbnails sizes information

## 12.1 — 2025-04-18

- Get real path of tmp dir

## 12.0 — 2025-03-18

- Use TemplateHelper for template blocks/values

## 11.4 — 2025-03-16

- Cope with array of lines of PHP code

## 11.3.1 — 2025-03-13

- Fix callableName() helper method
- Better this way, may be, as its hard to say which of spread on array_map() or on generator fn() is more efficient

## 11.3 — 2025-03-13

- Use Helper\Html\Form helpers

## 11.2 — 2025-03-13

- Add current nonce

## 11.1 — 2025-03-11

- More resilient way to check compiled template files

## 11.0 — 2025-03-11

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 10.17 — 2025-03-11

- Better this way, no need to do twice the same job!

## 10.16 — 2025-03-11

- Review public list of template tags (blocks and values)

## 10.15 — 2025-03-11

- Add entry types list

## 10.14 — 2025-03-11

- Review list of statuses
- Update table sorting function

## 10.13 — 2025-02-26

- Add some constants

## 10.12 — 2025-02-26

- Better with this order: version, release info, last available release, older available releases

## 10.11 — 2025-02-25

- Add list of Dotclear releases in attic

## 10.10 — 2025-02-19

- Switch from legacy form:: to Help/Html/Form/... for all rendered results

## 10.9 — 2025-01-26

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 10.8 — 2025-01-23

- Use DateTimeImmutable rather than date

## 10.7 — 2025-01-20

- Display release date if natural form (if present)

## 10.6 — 2025-01-08

- Add Statuses list

## 10.5 — 2025-01-04

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 10.4 — 2024-12-23

- Add sort capability on some tables

## 10.3 — 2024-12-10

- Fix: E_STRICT is not used and deprecated since PHP 8.4

## 10.2 — 2024-12-09

- Use capability of arrow function without braces (implicit return)

## 10.1 — 2024-12-09

- No need to wait for 2.33+ as the _define.php has already this requirement

## 10.0 — 2024-12-08

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 9.8 — 2024-12-07

- Better code (I hope) to check for Cache

## 9.7 — 2024-12-06

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 9.6 — 2024-08-20

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 9.5.1 — 2024-06-09

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 9.5 — 2024-05-29

- New quote

## 9.4.2 — 2024-05-17

- Template path determination was still buggy

## 9.4.1 — 2024-05-17

- Fix template path determination

## 9.4 — 2024-05-14

- Add Antispam filters list

## 9.3 — 2024-05-08

- Add battery level (is API is available in browser)

## 9.2 — 2024-04-04

- Brand new icons :-p

## 9.1 — 2024-03-25

- Add dotclearMigrate / dotclear_migrate / DC_MIGRATE info (configuration and constants)

## 9.0 — 2024-03-21

- Use SVG image rather than PNG ones, 2.30+
- Use dotclear.enableShiftClick() rather than jQuery one, 2.29+

## 8.18 — 2024-01-20

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 8.17.1 — 2024-01-13

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 8.17 — 2024-01-12

- Add path and filename of displayed compiled template file (codemirror header)

## 8.16.1 — 2024-01-10

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 8.16 — 2024-01-09

- Use PHP mode for Codemirror with compiled template files (may contains PHP)

## 8.15 — 2024-01-09

- Use standard dotclear monospace fonts (with smaller size) for code preview (template, cache)

## 8.14 — 2023-12-15

- Remove DC_XMLRPC_URL/xmlrpcUrl config display (obsolete since 2.24)
- Switch from XHTML to HTML5 for self-closing tags

## 8.13.1 — 2023-11-30

- Cope with not existing directory

## 8.13 — 2023-11-29

- Add a purge maintenance task to remove reports in cache

## 8.12 — 2023-11-29

- Use JSON format for REST services

## 8.11 — 2023-11-22

- Add MySQL/MariaDB server to db info if pertinent

## 8.10 — 2023-11-19

- Also display renamed files (suffixed by -OLD) if any (only in first part)

## 8.9.1 — 2023-11-18

- Don't forget to give all args dude!

## 8.9 — 2023-11-18

- Ignore some specific extensions when checking (as .lang.php files constructed from .po)

## 8.8 — 2023-11-09

- Add additional tests for non PHP files
- Prepare a full checkup (not enabled yet)

## 8.7.1 — 2023-11-08

- Cope with empty static cache directory

## 8.7 — 2023-11-08

- Add experimental unexpected/additional files checklist

## 8.6 — 2023-10-27

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 8.5 — 2023-10-25

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 8.4 — 2023-10-25

- Simplify template dir management

## 8.3 — 2023-10-25

- No more need to test DC version

## 8.2 — 2023-10-25

- Use template()-&gt;appendPath(…) rather than -&gt;setPath(-&gt;getPath(), …)

## 8.1 — 2023-10-24

- Allow access to static cache even if not enabled by config

## 8.0 — 2023-10-24

- Use instanced item constants rather than interface ones

## 7.3 — 2023-10-22

- Remove dependency to staticCache

## 7.2 — 2023-10-22

- Display module's version if defined (dependencies)

## 7.1.1 — 2023-10-21

- Don't presume the existence of core version requirement May occurs when requirement is ['core', '2.28'] instead of ['core' =&gt; '2.28']

## 7.1 — 2023-10-21

- Add type of DC configuration return values

## 7.0 — 2023-10-20

- Better this way
- Add Release and Configuration info in system
- Use 2.28 Config methods
- 2.28 compliance

## 6.1.1 — 2023-10-04

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 6.1 — 2023-10-04

- Add Exceptions list (available with 2.28+)

## 6.0.1 — 2023-10-04

- Fix public prepend emulation for 2.28+
- Add session_id() info in system

## 6.0 — 2023-08-26

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.2 — 2023-08-16

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.1 — 2023-08-16

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.0 — 2023-08-16

- Use PHP 8.1 Callable syntax
- Get real method name if possible
- Cope with class::method(...) forms, cf Closure::fromCallable() shortcut
- 2.26 compliance (final touch)
- 2.27 compliance

## 4.25.1 — 2023-08-13

- Fix REST methods list with closure

## 4.25 — 2023-07-14

- Sort plugins/themes on alt repo lists

## 4.24 — 2023-07-10

- use static properties rather than admin dynamic ones

## 4.23 — 2023-07-07

- Add alternate repositories list (plugins/themes)

## 4.22 — 2023-07-05

- Add namespace information (if any) if callback is a closure (behaviors and URL handlers)

## 4.21 — 2023-07-05

- Put public behaviors link on top of page rather than on bottom

## 4.20 — 2023-06-27

- Sort some lists

## 4.19.1 — 2023-06-27

- Add local static $init variable

## 4.19 — 2023-06-26

- Cope with obsolete plugins

## 4.18 — 2023-06-26

- Add a setting (using about:config) to redact in reports

## 4.17 — 2023-06-26

- Reduce path with common prefixes (core, plugins, themes)

## 4.16 — 2023-06-25

- Use dotclear.services rather than $.ajax()

## 4.15.2 — 2023-06-25

- Better this way

## 4.15.1 — 2023-06-25

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.15 — 2023-06-25

- Add release information if release.json exists

## 4.14 — 2023-06-20

- Use dcCore::app()-&gt;adminurl-&gt;redirect() rather than Http::redirect()

## 4.13 — 2023-06-18

- Add HTTP Server information

## 4.12.1 — 2023-06-12

- Remove (temporary) dependency to staticCache until next 2.27

## 4.12 — 2023-06-11

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.11 — 2023-06-11

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.10 — 2023-06-11

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.9 — 2023-06-11

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.8 — 2023-06-11

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.7 — 2023-06-02

- Add DIRECTORY_SEPARATOR and PATH_SEPARATOR in system info

## 4.6.1 — 2023-06-01

- Fix displayed value for array

## 4.6 — 2023-05-31

- Remove CB version, not more relevant, add update channel

## 4.5.1 — 2023-05-22

- Minify fix
- Remove use of deprecated method

## 4.5 — 2023-05-15

- Split Helper.php under Helper subfolder

## 4.4 — 2023-05-14

- Cope with staticCache ns

## 4.3 — 2023-05-14

- Add formater syntaxe names

## 4.2 — 2023-05-10

- Add direct actions
- Use My::checkContext()
- Update Uninstall.php
- Not everywhere
- Use My::id() rather than string

## 4.1.1 — 2023-05-04

- Use My::id() as settings namespace

## 4.1 — 2023-04-30

- Don't use deprecated methods Dude!

## 4.0 — 2023-04-30

- Prepare 2.26
- Add and use src/My.php (thank's JcDenis for inspiration)
- Add Autoloader info
- Add uninstall
- Add two new DC constants
- Add PHP temporary folder in system info

## 3.6.1 — 2023-02-28

- Fix report cache creation, closes #15

## 3.6 — 2023-02-27

- Replace images by emojis, reduce displayed paths if possible (DC_ROOT)

## 3.5 — 2023-02-27

- Better display plugin info in summary

## 3.4.2 — 2023-02-25

- Fix init of count variable

## 3.4.1 — 2023-02-24

- Fix variable init

## 3.4 — 2023-02-24

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 3.3 — 2023-02-22

- Add some information about available versions
- Switch from table to a list a details elements for plugin list Dependencies modules are now clickable, also some external URLs
- Use DC_DEFAULT_THEME rather than 'default'

## 3.2.1 — 2023-02-14

- Fix #12, DC_PLUGINS_ROOT was badly coped

## 3.2 — 2023-02-13

- Better display global variables values Put deprecated at the end of the list

## 3.1 — 2023-02-11

- Add list of global variables
- Sort some lists

## 3.0 — 2023-01-22

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 2.5.1 — 2023-01-18

- Cope with non existing folder, should fix #11

## 2.5 — 2023-01-17

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 2.4 — 2022-12-19

- Use /Dotclear/Plugin/SysInfo namespace everywhere but in _define.php
- No need of files/folders housecleaning on plugin install

## 2.3 — 2022-12-18

- Add PHP cache information (OPCode, APC, Memcache)

## 2.2 — 2022-12-08

- Move css/js to sub dirs

## 2.1 — 2022-12-07

- Add callback for URLHandler list

## 2.0 — 2022-11-30

- Fix favorite permission
- Add counters (if possible)
- Avoid use of method argument given by reference

## 1.23 — 2022-10-15

- Cope with legacy code ;-)
- Use template::CACHE_FOLDER and dcPublic::TPL_ROOT constants
- Add form actions, deletion/update
- 2.24+

## 1.22.1 — 2022-09-20

- Usual oups :-)

## 1.22 — 2022-09-20

- Add PHP Error level information
- Various

## 1.21.1 — 2022-08-14

- Usual oups :-)

## 1.21 — 2022-08-10

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 1.20.3 — 2022-03-10

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 1.20.2 — 2022-03-10

- fix dynamic restMethods callback

## 1.20.1 — 2022-03-05

- Cope with new installation
- fix Closure type for public behaviors

## 1.20 — 2022-03-02

- Add update channel info from cache (if exists)

## 1.19 — 2022-02-07

- Behavior callback may be a closure, fix #7
- Use xxxModuleLoad wrappers
- New SVG icons which replace old in PNG

## 1.18.1 — 2022-01-29

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 1.18 — 2022-01-29

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 1.17 — 2021-11-18

- Remove currywurst templateset
- Back to manual triggering only
- Update dcstore.yml

## 1.16.3 — 2021-11-07

- Fix usage of optional parameter in private method.
- Switch the trigger from push to release event

## 1.16.2 — 2021-11-04

- DC_DA_NOT_UPDATE constant is renamed to DC_STORE_NOT_UPDATE

## 1.16.1 — 2021-11-04

- Add driver syntax to versions info
- Add some quotes
- Organize checklist's list with sub-menus
- Code refactoring
- Update dcstore.yml
- Update and rename manual.yml to dcstore.yml
- Update manual.yml
- Remove unecessary parts
- Autres correctifs et améliorations.

## 1.16 — 2021-11-02

- Add list of types of permissions, closes #3

## 1.15.2 — 2021-11-01

- 1.15.2
- cope with unreachable repository

## 1.15.1 — 2021-10-23

- Add alternative repository

## 1.15 — 2021-08-07

- Dotclear 2.19 compliance

## 1.14 — 2021-08-07

- Add plugins/themes repo list from DA

## 1.13.1 — 2020-08-23

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 1.13 — 2020-08-19

- Add php and db version

## 1.12.1 — 2020-07-21

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 1.12 — 2020-05-21

- Add public list of template tags

## 1.11 — 2020-04-06

- No moNo more inline JS

## 1.10 — 2020-03-23

- Dotclear 2.16 and jQuery 3+ compliance
- Update documentation

## 1.9.3 — 2020-02-20

- Dark admin theme was implemented in 2.14 only
- Cope with dark admin theme Do not display an empty link when there is no link

## 1.9.2 — 2020-02-20

- Switch from table to details for plugins/themes DA lists

## 1.9.1 — 2020-02-19

- Add URLs if possible to plugin/theme info

## 1.9 — 2020-02-19

- Not too fast, 1.9 is ok ;-)
- Add plugins/themes repo list content (from cache if existing either from URL)
- Some system constants missing
- short notation for array (array() → [])

## 1.8.4 — 2018-08-16

- Adjustmemts for 2.14 + doc
- Adjustment for the 2.14
- New DC constant

## 1.8.3 — 2017-03-20

- Don’t do more than necessary!
- mkdocs update

## 1.8.2 — 2017-03-20

- bugfix: need to unbind() before changing DOM
- Add 1.8.1 release in doc

## 1.8.1 — 2017-03-18

- Add link to online doc (mkdocs sources removed from archive)

## 1.8 — 2017-03-17

- Add mkdocs documentation sources
- Add alert on load file error
- Add cache file preview on URL calculator

## 1.7.1 — 2017-02-23

- Better this way, 1.7.1 released

## 1.7 — 2017-02-10

- Better this way
- Tiny adjustments
- Redesign static cache list, version 0.7
- small bugfixes

## 1.6 — 2017-02-09

- Cope with UFT-8 source files, code factorisation and cleanup
- Add static cache files list
- Switch every list (ul or dl) into tables, add template’s paths list
- Escape htmlentities in file content
- Add checkboxes helpers
- Cleanup
- Add compiled template preview

## 1.5 — 2016-08-16

- Add dotty templateset, dc 2.10 min
- Add dump of DC 2.10 DC_VAR constant, next release will be 1.5

## 1.4 — 2016-03-30

- Simplify code for plugin's properties, add DC_CRYPT_ALGO constant (DC 2.10+)
- Add two missing DC constants
- Add new DC_SESSION_TTL constant display, next release will be 1.4 (Dotclear 2.9 min)
- Using dcPage::cssLoad, dcPage::jsLoad and dcPage::getPF is a better way to load plugin resources

## 1.3 — 2015-08-25

- Add listing of registered REST methods, next release will be 1.3

## 1.2 — 2015-06-27

- Indent (tab-4)
- Deal with updated mustek templateset, min version for dotclear will be 2.8

## 1.1.3 — 2015-06-26

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 1.1.2 — 2015-06-26

- Fix behaviours display, thank's to Gvx for patch
- Do not need breadcrumb in pseudo-public page
- Add {{tpl:Breadcrumb}} tag in mustek template
- Cope with arrays in arrays

## 1.1.1 — 2015-01-02

- Try to be the last plugin loaded (as far as possible), avoid caching (in template) of public behaviours list

## 1.1 — 2014-12-31

- Ajout de la liste des éditeurs/syntaxes, et des plugins dans l'ordre de chargement
- Apply heritage/extension template mechanism
- Adapt admin URL for 2.7.1, next release will be 1.1

## 1.0 — 2014-12-02

- Add new Dotclear constant
- Using decode instead of get for loading plugin file
- registercopy is not necessary as it has been done on loading (by the core)
- Use new Dotclear.admin.url class
- Add two missing constants
- Ajout de la liste des URLs d'administration enregistrées
- backward compatibility
- New template set currywurst (HTML5)
- Autres correctifs et améliorations.

## 0.9 — 2013-10-04

- new _define.php
- Cope with new favorites system
- New icons
- New notices system
- Cope with new breadcrumb
- No more $GLOBALS at top level, and various
- Add DC_DEV in constant list
- various
- Autres correctifs et améliorations.

## 0.8 — 2013-03-16

- Version 0.8
- harmonize &lt;label&gt;&lt;/label&gt; in forms
- Now uses core message function (dcPage::message)
- Add missing files
- Revert to original name and desc in define.php of plugins. Localized versions are not required here.

## 0.7 — 2011-11-22

- Ajout de la gestion des fichiers cache des templates fournis par les plugins

## 0.6 — 2011-10-21

- Affichage dans une nouvelle fenêtre des behaviours publics
- Mise en place de la liste des behaviours publics (liste statique nécessitant un vidage de cache après installation d'un nouveau plugin ou thème enregistrant un de ces behaviours)
- Ajout d'un behavior commandé par setting pour désactiver le cache HTTP

## 0.5.1 — 2011-09-20

- Ajout version Clearbricks
- Minor fixes
- Ajout de la suppression sélective de fichiers cache
- Liste des constantes
- Nouvelles icônes (proviennent de Faenza)
- Ajout de la liste des béhaviours
- Correction pour le chemin des templates par défaut
- Ajout de la détection des fichiers XML
- Autres correctifs et améliorations.
