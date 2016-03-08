<?php
namespace XeArts\Eccube\ControllerProvider;

use Silex\Application;
use Silex\ControllerProviderInterface;

class InstallControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {

        /* @var $controllers \Silex\ControllerCollection */
        $controllers = $app['controllers_factory'];

        // installer
        $controllers->match('', "\\XeArts\\Eccube\\Controller\\Install\\InstallController::index")
            ->bind('install');
        $controllers->match('/step1', "\\XeArts\\Eccube\\Controller\\Install\\InstallController::step1")
            ->bind('install_step1');
        $controllers->match('/step2', "\\XeArts\\Eccube\\Controller\\Install\\InstallController::step2")
            ->bind('install_step2');
        $controllers->match('/step3', "\\XeArts\\Eccube\\Controller\\Install\\InstallController::step3")
            ->bind('install_step3');
        $controllers->match('/step4', "\\XeArts\\Eccube\\Controller\\Install\\InstallController::step4")
            ->bind('install_step4');
        $controllers->match('/step5', "\\XeArts\\Eccube\\Controller\\Install\\InstallController::step5")
            ->bind('install_step5');

        $controllers->match('/complete', "\\XeArts\\Eccube\\Controller\\Install\\InstallController::complete")
            ->bind('install_complete');

        $controllers->match('/migration', "\\XeArts\\Eccube\\Controller\\Install\\InstallController::migration")
            ->bind('migration');
        $controllers->match('/migration_plugin', "\\XeArts\\Eccube\\Controller\\Install\\InstallController::migration_plugin")
            ->bind('migration_plugin');
        $controllers->match('/migration_end', "\\XeArts\\Eccube\\Controller\\Install\\InstallController::migration_end")
            ->bind('migration_end');
        return $controllers;
    }
}
