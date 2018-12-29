<?php

namespace App\Modules\Shell\Tasks;

use Ice\Auth\Driver\Model\Roles;
use Ice\Cli\Console;
use Ice\Mvc\View\Engine\Sleet;
use Ice\Mvc\View\Engine\Sleet\Compiler;

/**
 * Prepare task.
 *
 * @category Task
 * @package  App
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class PrepareTask extends MainTask
{

    /**
     * Chmod for folders.
     *
     * @return void
     */
    public function chmodAction()
    {
        $dirs = [
            '/Appvar/cache',
            '/var/log',
            '/public/min',
            '/public/upload',
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

        @symlink(__ROOT__ . '/public/fonts/', __ROOT__ . '/public/min/fonts');
    }

    /**
     * Remove data from directories.
     * Parameters:
     *  recursive: yes
     *  all: no
     *
     * @return void
     */
    public function rmAction()
    {
        if (APP_ENV == 'development' || APP_ENV == 'testing') {
            $params = $this->dispatcher->getParams();

            if (isset($params["dirs"])) {
                $dirs = explode('|', $params["dirs"]);
            } else {
                $dirs = [
                    '/Appvar/cache/',
                    '/var/log/',
                    '/public/min/',
                ];

                if (APP_ENV == 'development'
                    && isset($params["upload"]) && $params["upload"] == 'yes'
                ) {
                    $dirs[] = '/public/upload/';
                }
            }

            if (isset($params["recursive"])) {
                $recursive = $params["recursive"] = 'no' ? false : true;
            } else {
                $recursive = true;
            }

            foreach ($dirs as $dir) {
                // Make sure if directory exist
                if (!is_dir(__ROOT__ . $dir)) {
                    continue;
                }

                echo $dir . "\n";

                if (isset($params["all"]) && $params["all"] == "yes") {
                    exec('rm -f ' . ($recursive ? '-r ' : '')  . __ROOT__ . $dir . '*');
                } else {
                    $command = 'find ' . __ROOT__ . $dir . ' -not -name .gitignore -type f';

                    exec($command . (!$recursive ? ' -maxdepth 1' : '') . ' | xargs rm -f');

                    if (isset($params["all"]) && $params["all"] == "dir") {
                        exec('find ' . __ROOT__ . $dir .
                        ' -type d -not -path ' . __ROOT__ . $dir . ' | xargs rm -frd');
                    }
                }
            }
        }
    }

    /**
     * Compile views from sleet files.
     *
     * @return void
     */
    public function sleetAction()
    {
        error_reporting(E_ALL ^ E_NOTICE);

        $sleet = new Sleet($this->view, $this->di);
        $sleet->setOptions([
            'compileDir' => __ROOT__ . '/Appvar/cache/sleet/',
            'trimPath' => __ROOT__,
            'compile' => Compiler::ALWAYS
        ]);

        $dirs = [
            '/App/Modules/Admin/views/',
            '/App/Modules/Doc/views/',
            '/App/Modules/Front/views/',
            '/templates/',
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

    /**
     * Minify assets (css & js) files.
     *
     * @return void
     */
    public function assetsAction()
    {
        // Set the assets service
        $assets = new Assets();
        $assets->setOptions([
            'source' => __ROOT__ . '/public/',
            'target' => 'min/',
            'minify' => Assets::ALWAYS
        ]);

        foreach (['css', 'js'] as $type) {
            $path = __ROOT__ . '/public/' . $type . '/';

            foreach ($iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            ) as $item) {
                if (!$item->isDir() && $item->getExtension() == $type) {
                    echo $type . '/' . $iterator->getSubPathName() . "\n";
                    $assets->add($type . '/' . $iterator->getSubPathName());
                }
            }
        }
    }

    /**
     * Create basic roles.
     *
     * @return void
     */
    public function rolesAction()
    {
        $roles = Roles::find();

        if (!$roles->count()) {
            $login = new Roles();
            $login->name = 'login';
            $login->description = 'Login privileges, granted after account confirmation.';
            $login->create();

            $admin = new Roles();
            $admin->name = 'admin';
            $admin->description = 'Administrative user, has access to everything.';
            $admin->create();

            echo "The roles have been added." . PHP_EOL;
        }
    }

    /**
     * Help to find untranslated messages.
     * Parameters:
     *  0: lang, eg. pl
     *
     * @return void
     */
    public function langAction()
    {
        $lang = $this->dispatcher->getParam(0, null, 'en', true);

        $scan = [];
        $this->translationSearch($scan, '/src/');
        $this->translationSearch($scan, '/templates/');

        $path = $this->config->i18n->dir . $lang . '.php';
        $file = file_exists($path) ? include_once $path : [];
        $merge = array_merge($scan, $file);

        ksort($merge);

        foreach ($merge as $key => $value) {
            if (isset($file[$key]) && isset($scan[$key])) {
                $color = null;
            } elseif (!isset($file[$key]) && isset($scan[$key])) {
                $color = 'green';
            } elseif (isset($file[$key]) && !isset($scan[$key])) {
                $color = 'yellow';
            } else {
                $color = 'red';
            }

            echo Console::color("    '" . $key . "' => '" . $value . "'," . PHP_EOL, $color);
        }
    }

    /**
     * @param array  $scan
     * @param string $dir
     */
    private function translationSearch(array & $scan, $dir)
    {
        foreach ($iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(__ROOT__ . $dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        ) as $item) {
            if (!$item->isDir() && in_array($item->getExtension(), ['php', 'sleet'])) {
                $content = file_get_contents(__ROOT__ . $dir . $iterator->getSubPathName());

                preg_match_all('/(?:field\s=\s|_t\()[\'"]([^\'"]+)/i', $content, $matches);

                if (count($matches[1])) {
                    foreach ($matches[1] as $value) {
                        $scan[$value] = "";
                    }
                }
            }
        }
    }
}
