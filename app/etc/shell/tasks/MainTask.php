<?php

namespace App\Modules\Shell\Tasks;

use Ice\Cli\Task;
use Ice\Cli\Console;

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
            Console::color('--param=', null, Console::BOLD_BRIGHT) . Console::color('"some value"', 'yellow') . "\n\n";

        echo Console::color("Available tasks:", null, Console::UNDERLINE) . "\n";
        echo "-" . $this->dispatcher->getModule() . "\n";

        foreach (new \DirectoryIterator(__DIR__) as $file) {
            if ($file->isDot()) {
                continue;
            }
            $task = $file->getBasename('.php');
            echo "  -" . strtolower(strstr($task, 'Task', true)) . "\n";

            $f = new \ReflectionClass(__NAMESPACE__ . '\\' . $task);
            foreach ($f->getMethods() as $m) {
                if ($m->class == __NAMESPACE__ . '\\' . $task && strpos($m->name, 'Action') !== false) {
                    echo "    -" . strstr($m->name, 'Action', true) . "\n";
                }
            }
        }
        echo PHP_EOL;
    }

    /**
     * Not found
     */
    public function notFound()
    {
        echo "Task not found\n";
    }
}
