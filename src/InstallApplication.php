<?php
namespace XeArts\Eccube;

use Eccube\ControllerProvider\InstallControllerProvider;
use Eccube\InstallApplication as BaseApplication;
use Eccube\ServiceProvider\InstallServiceProvider;
use Symfony\Component\Yaml\Yaml;

class InstallApplication extends BaseApplication
{
    public function __construct(array $values = array())
    {
        $app = $this;

        parent::__construct($values);

        $app->register(new \Silex\Provider\MonologServiceProvider(), array(
            'monolog.logfile' => __DIR__.'/../../app/log/install.log',
        ));

        // load config
        $app['config'] = $app->share(function() {
            $distPath = __DIR__.'/../../src/Eccube/Resource/config';

            $configConstant = array();
            $constantYamlPath = $distPath.'/constant.yml.dist';
            if (file_exists($constantYamlPath)) {
                $configConstant = Yaml::parse(file_get_contents($constantYamlPath));
            }

            $configLog = array();
            $logYamlPath = $distPath.'/log.yml.dist';
            if (file_exists($logYamlPath)) {
                $configLog = Yaml::parse(file_get_contents($logYamlPath));
            }

            $config = array_replace_recursive($configConstant, $configLog);

            return $config;
        });

        $distPath = __DIR__.'/../../src/Eccube/Resource/config';
        $config_dist = Yaml::parse(file_get_contents($distPath.'/config.yml.dist'));
        if (!empty($config_dist['timezone'])) {
            date_default_timezone_set($config_dist['timezone']);
        }

        $app->register(new \Silex\Provider\SessionServiceProvider());

        $app->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => array(__DIR__.'/Resource/template/install'),
            'twig.form.templates' => array('bootstrap_3_horizontal_layout.html.twig'),
        ));

        $this->register(new \Silex\Provider\UrlGeneratorServiceProvider());
        $this->register(new \Silex\Provider\FormServiceProvider());
        $this->register(new \Silex\Provider\ValidatorServiceProvider());

        $this->register(new \Silex\Provider\TranslationServiceProvider(), array(
            'locale' => 'ja',
        ));
        $app['translator'] = $app->share($app->extend('translator', function($translator, \Silex\Application $app) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());

            $r = new \ReflectionClass('Symfony\Component\Validator\Validator');
            $file = dirname($r->getFilename()).'/Resources/translations/validators.'.$app['locale'].'.xlf';
            if (file_exists($file)) {
                $translator->addResource('xliff', $file, $app['locale'], 'validators');
            }

            $file = __DIR__.'/Resource/locale/validator.'.$app['locale'].'.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale'], 'validators');
            }

            $translator->addResource('yaml', __DIR__.'/Resource/locale/ja.yml', $app['locale']);

            return $translator;
        }));

        $app->mount('', new InstallControllerProvider());
        $app->register(new InstallServiceProvider());

        $app->error(function(\Exception $e, $code) use ($app) {
            if ($code === 404) {
                return $app->redirect($app->url('install'));
            } elseif ($app['debug']) {
                return;
            }

            return $app['twig']->render('error.twig', array(
                'error' => 'エラーが発生しました.',
            ));
        });
    }
}
