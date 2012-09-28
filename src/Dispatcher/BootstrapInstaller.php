<?php
namespace Dispatcher;

class BootstrapInstaller
{
    const BOOTSTRAP_CONTROLLER = 'dispatcher_bootstrap';

    public static function install()
    {
        if (!defined('CI_VERSION') || !defined('APPPATH')) {
            die('No CodeIgniter installation found.');
        }

        $files = array(
            'dispatcher.php' => array(
                'srcDir' => __DIR__ . '/templates/',
                'destDir' => APPPATH . 'config/'
            ),
            'dependencies.php' => array(
                'srcDir' => __DIR__ . '/templates/',
                'destDir' => APPPATH . 'config/'
            ),
            'dispatcher_bootstrap.php' => array(
                'srcDir' => __DIR__ . '/templates/',
                'destDir' => APPPATH . 'controllers/'
            ),
        );

        foreach ($files as $f => $dirs) {
            if (!file_exists($dirs['destDir'] . $f)) {
                copy($dirs['srcDir'] . $f , $dirs['destDir'] . $f)
                    OR die('Unable to install ' . $f);
            }
        }
    }

    public static function run(array &$route, $installed = FALSE)
    {
        if (!$installed) {
            self::install();
        }

        $route['default_controller'] = 'dispatcher_bootstrap';
        $route['(.*)'] = $route['default_controller'] . '/$1';
    }
}
