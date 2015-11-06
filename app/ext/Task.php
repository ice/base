<?php

namespace App\Extensions;

use Ice\Cli\Console;
use Ice\Loader;

/**
 * Base Task
 *
 * @package     Ice/Base
 * @category    Task
 */
class Task extends \Ice\Cli\Task
{

    /**
     * Display info & available tasks
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
            (new Loader())
                ->addNamespace($module['namespace'] . '\Tasks', $module['path'] . '/tasks/')
                ->register();

            // Module name
            echo '-' . $name . PHP_EOL;

            foreach (new \DirectoryIterator($module['path'] . 'tasks/') as $file) {
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
     * Not found
     */
    public function notFound()
    {
        echo "Task not found\n";
    }
}
