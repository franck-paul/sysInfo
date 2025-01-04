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

namespace Dotclear\Plugin\sysInfo\MaintenanceTask;

use Dotclear\App;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Plugin\sysInfo\My;
use Dotclear\Plugin\maintenance\MaintenanceTask;

class ReportCache extends MaintenanceTask
{
    protected string $group = 'purge';

    protected function init(): void
    {
        $this->task    = __('Empty sysInfo reports cache directory');
        $this->success = __('sysInfo reports cache directory emptied.');
        $this->error   = __('Failed to empty sysInfo reports cache directory.');

        $this->description = __('');
    }

    public function execute(): bool|int
    {
        $cache_dir = Path::real(implode(DIRECTORY_SEPARATOR, [App::config()->cacheRoot(), My::id()]), false);
        if ($cache_dir !== false && is_dir($cache_dir)) {
            Files::deltree($cache_dir);
        }

        return true;
    }
}
