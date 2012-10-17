<?php
namespace Dispatcher;

/**
 * Installs the needed config files to codeigniter's config and controllers
 * directory.
 * <code>
 * // e.g.
 * // routes.php
 *
 * // $route['default_controller'] = 'welcome';
 * $route['404_override'] = '';
 * \Dispatcher\BootstrapInstaller::run($route);
 *
 * // After the first run, you can disable the file checks in the
 * // second argument
 * \Dispatcher\BootstrapInstaller::run($route, TRUE);
 *
 * // For manual route configurations, uses
 * \Dispatcher\BootstrapInstaller::install();
 * </code>
 */
class BootstrapInstaller
{
    const BOOTSTRAP_CONTROLLER = 'dispatcher_bootstrap';

    /**
     * Checks and installs the needed files to codeigniter's
     * config and controllers directory.
     */
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
                    || die('Unable to install ' . $f);
            }
        }
    }

    /**
     * Installs the needed files and configs the $route.
     * <i>Note: By default, the default_controller will be set to
     * the dispatcher_bootstrap controller and all routes will be pushed to
     * the default_controller.</i>
     * @param array   $route     The route array to be configurated
     * @param boolean $installed Whether to skip the file check
     */
    public static function run(array &$route, $installed = false)
    {
        if (!$installed) {
            self::install();
        }

        $route['default_controller'] = 'dispatcher_bootstrap';
        $route['(.*)'] = $route['default_controller'] . '/$1';
    }
}
