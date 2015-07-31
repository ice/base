<?php

namespace App\Modules\Shell\Tasks;

use Ice\Mvc\View\Engine\Sleet;
use Ice\Mvc\View\Engine\Sleet\Compiler;

/**
 * Prepare CLI Task
 *
 * @package     Ice/Base
 * @category    Task
 */
class PrepareTask extends MainTask
{

    /**
     * Chmod for folders
     */
    public function chmodAction()
    {
        $dirs = [
            '/app/tmp',
            '/app/log',
            '/public/min',
            '/public/upload/tmp',
        ];

        foreach ($dirs as $dir) {
            echo $dir . "\n";
            if (!is_dir(__ROOT__ . $dir)) {
                $old = umask(0);

                mkdir(__ROOT__ . $dir, 0777, true);
                umask($old);
            } else {
                chmod(__ROOT__ . $dir, 0777);
            }
        }
    }

    /**
     * Remove data from public folder
     */
    public function rmAction()
    {
        if ($this->config->app->env == 'development' || $this->config->app->env == 'testing') {
            $dirs = array(
                '/app/tmp/*',
                '/app/log/*',
                '/public/min/*',
                '/public/upload/tmp/*',
            );
            foreach ($dirs as $dir) {
                echo $dir . "\n";
                exec('rm -f -R ' . __ROOT__ . $dir);
            }
        }
    }

    /**
     * Compile views from sleet files
     */
    public function sleetAction()
    {
        $sleet = new Sleet($this->view, $this->di);
        $sleet->setOptions([
            'compileDir' => __ROOT__ . '/app/tmp/sleet/',
            'trimPath' => __ROOT__,
            'compile' => Compiler::ALWAYS
        ]);

        $dirs = [
            '/app/modules/frontend/views/',
            '/app/modules/admin/views/',
            '/app/modules/doc/views/',
            '/app/views/',
        ];
        foreach ($dirs as $dir) {
            foreach ($iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(__ROOT__ . $dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            ) as $item) {
                if (!$item->isDir() && $item->getExtension() == 'sleet') {
                    echo $sleet->compile(__ROOT__ . $dir . $iterator->getSubPathName()) . "\n";
                }
            }
        }
    }
}
