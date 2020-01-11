<?php

namespace App\Modules\Shell\Tasks;

use App\Extensions\Task;

/**
 * Main task.
 *
 * @category Task
 * @package  App
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class MainTask extends Task
{
    /**
     * Main Action - display info & available tasks.
     *
     * @return void
     */
    public function mainAction()
    {
        parent::info();
    }
}
