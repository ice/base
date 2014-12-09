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

    /**
     * Main Action - display available tasks
     */
    public function mainAction()
    {
        echo "-- CLI tasks --\n";
        foreach (new \DirectoryIterator(__DIR__) as $file) {
            if ($file->isDot() || $file->getBasename('.php') == 'MainTask') {
                continue;
            }
            $task = $file->getBasename('.php');
            echo strtolower(strstr($task, 'Task', true)) . "\n";

            $f = new \ReflectionClass(__NAMESPACE__ . '\\' . $task);
            foreach ($f->getMethods() as $m) {
                if ($m->class == __NAMESPACE__ . '\\' . $task && strpos($m->name, 'Action') !== false) {
                    echo "\t" . strstr($m->name, 'Action', true) . "\n";
                }
            }
        }
    }

    /**
     * Not found
     */
    public function notFound()
    {
        echo "Task not found\n";
    }
}
