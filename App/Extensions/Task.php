<?php

namespace App\Extensions;

use Ice\Cli\Console;
use Ice\Loader;

/**
 * Base task.
 *
 * @category Extensions
 * @package  Base
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class Task extends \Ice\Cli\Task
{

    /**
     * Display info & available tasks.
     *
     * @return void
     */
    public function info()
    {
        echo Console::color("Run some CLI task\n", 'cyan', Console::INVERSE);

        echo "\tphp index.php " .
            // Set the module
            Console::color('--module=', null, Console::BOLD_BRIGHT) .
                Console::color($this->router->getDefaultModule(), 'red') . ' ' .
            // Set the task
            Console::color('--handler=', null, Console::BOLD_BRIGHT) .
                Console::color($this->router->getDefaultHandler(), 'blue') . ' ' .
            // Set the action
            Console::color('--action=', null, Console::BOLD_BRIGHT) .
                Console::color($this->router->getDefaultAction(), 'green') . ' ' .
            // Set the params
            Console::color('--id=', null, Console::BOLD_BRIGHT) . Console::color('1', 'yellow') . ' ' .
            Console::color('--param=', null, Console::BOLD_BRIGHT) . Console::color('"some value"', 'yellow') . PHP_EOL;

        echo PHP_EOL . Console::color("Available tasks:", null, Console::UNDERLINE) . PHP_EOL;

        foreach ($this->console->getModules() as $name => $module) {
            // Module name
            echo '-' . $name . PHP_EOL;

            foreach (new \DirectoryIterator($module['path'] . 'Tasks/') as $file) {
                if ($file->isDot()) {
                    continue;
                }

                $task = $file->getBasename('.php');
                $class = $module['namespace'] . '\\Tasks\\' . $task;

                // Handler name
                echo '  -' . strtolower(strstr($task, 'Task', true)) . PHP_EOL;

                $f = new \ReflectionClass($class);

                foreach ($f->getMethods() as $m) {
                    if ($m->class == $class && strpos($m->name, 'Action') !== false) {
                        // Action name
                        echo '    -' . strstr($m->name, 'Action', true) . PHP_EOL;
                    }
                }
            }
        }
        echo PHP_EOL;
    }

    /**
     * Not found action.
     *
     * @return void
     */
    public function notFound()
    {
        echo "Task not found" . PHP_EOL;
    }
}
