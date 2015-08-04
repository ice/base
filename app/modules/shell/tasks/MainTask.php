<?php

namespace App\Modules\Shell\Tasks;

use App\Extensions\Task;

/**
 * Main Shell Task
 *
 * @package     Ice/Base
 * @category    Task
 */
class MainTask extends Task
{

    /**
     * Main Action - display info & available tasks
     */
    public function mainAction()
    {
        parent::info();
    }
}
