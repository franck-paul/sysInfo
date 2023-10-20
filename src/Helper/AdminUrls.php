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
use Dotclear\Interface\Core\LexicalInterface;

class AdminUrls
{
    /**
     * Return list of admin registered URLs
     *
     * @return     string
     */
    public static function render(): string
    {
        // Récupération de la liste des URLs d'admin enregistrées
        $urls = App::backend()->url()->dumpUrls();
        $urls = $urls->getArrayCopy();
        App::lexical()->lexicalKeySort($urls, LexicalInterface::ADMIN_LOCALE);

        $str = '<table id="urls" class="sysinfo"><caption>' . __('Admin registered URLs') . ' (' . sprintf('%d', count($urls)) . ')' . '</caption>' . // @phpstan-ignore-line
            '<thead><tr><th scope="col" class="nowrap">' . __('Name') . '</th>' .
            '<th scope="col">' . __('URL') . '</th>' .
            '<th scope="col">' . __('Query string') . '</th></tr></thead>' .
            '<tbody>';
        foreach ($urls as $name => $url) {
            $str .= '<tr>' .
                '<td scope="row" class="nowrap">' . $name . '</td>' .
                '<td><code>' . $url['url'] . '</code></td>' .
                '<td><code>' . http_build_query($url['qs']) . '</code></td>' .
                '</tr>';
        }
        $str .= '</tbody>' .
            '</table>';

        return $str;
    }
}
