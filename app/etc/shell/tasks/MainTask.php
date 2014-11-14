<?php

namespace App\Modules\Shell\Tasks;

use Ice\Cli\Task;

/**
 * Main Shell Task
 *
 * @package     Ice/Base
 * @category    Task
 */
class MainTask extends Task
{

    public function mainAction()
    {
        echo "OK\n";
    }
}
