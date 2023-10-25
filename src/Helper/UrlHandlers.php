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
use Dotclear\Plugin\sysInfo\CoreHelper;

class UrlHandlers
{
    /**
     * Return list of registered URLs
     *
     * @return     string
     */
    public static function render(): string
    {
        // Récupération des types d'URL enregistrées
        $urls = App::url()->getTypes();

        // Tables des URLs non gérées par le menu
        //$excluded = ['xmlrpc','preview','trackback','feed','spamfeed','hamfeed','pagespreview','tag_feed'];
        $excluded = [];

        $str = '<table id="urls" class="sysinfo"><caption>' . __('List of known URLs') . ' (' . sprintf('%d', count($urls)) . ')' . '</caption>' .
            '<thead><tr><th scope="col">' . __('Type') . '</th>' .
            '<th scope="col">' . __('base URL') . '</th>' .
            '<th scope="col">' . __('Regular expression') . '</th>' .
            '<th scope="col">' . __('Callback') . '</th>' .
            '</tr></thead>' .
            '<tbody>' .
            '<tr>' .
            '<td scope="row">' . 'home' . '</td>' .
            '<td>' . '' . '</td>' .
            '<td><code>' . '^$' . '</code></td>' .
            '<td><code>' . '(default)' . '</code></td>' .
            '</tr>';
        foreach ($urls as $type => $param) {
            if (!in_array($type, $excluded)) {
                $fi      = $param['handler'];
                $handler = CoreHelper::callableName($fi);
                $str .= '<tr>' .
                    '<td scope="row">' . $type . '</td>' .
                    '<td>' . $param['url'] . '</td>' .
                    '<td><code>' . $param['representation'] . '</code></td>' .
                    '<td><code>' . $handler . '</code></td>' .
                    '</tr>';
            }
        }
        $str .= '</tbody>' .
            '</table>';

        return $str;
    }
}
