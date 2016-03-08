<?php
namespace XeArts\Eccube;

use Eccube\Application\ApplicationTrait;
use XeArts\Eccube\ControllerProvider\InstallControllerProvider;
use Eccube\ServiceProvider\InstallServiceProvider;
use Symfony\Component\Yaml\Yaml;

class InstallApplication extends ApplicationTrait
{
    public function __construct(array $values = array())
    {
        $app = $this;
        if (empty($values['base_path'])) {
            throw new \InvalidArgumentException('base_path must be contained in values.');
        }

        parent::__construct($values);

        $baePath = $app['base_path'];

        $app->register(new \Silex\Provider\MonologServiceProvider(), array(
            'monolog.logfile' => $baePath.'/app/log/install.log',
        ));

        $resourcePath = $baePath.'/vendor/ec-cube/ec-cube/src/Eccube/Resource';
        $configPath = $resourcePath . '/config';

        // load config
        $app['config'] = $app->share(function() use ($configPath) {

            $configConstant = array();
            $constantYamlPath = $configPath.'/constant.yml.dist';
            if (file_exists($constantYamlPath)) {
                $configConstant = Yaml::parse(file_get_contents($constantYamlPath));
            }

            $configLog = array();
            $logYamlPath = $configPath.'/log.yml.dist';
            if (file_exists($logYamlPath)) {
                $configLog = Yaml::parse(file_get_contents($logYamlPath));
            }

            $config = array_replace_recursive($configConstant, $configLog);

            return $config;
        });

        $config_dist = Yaml::parse(file_get_contents($configPath.'/config.yml.dist'));
        if (!empty($config_dist['timezone'])) {
            date_default_timezone_set($config_dist['timezone']);
        }

        $app->register(new \Silex\Provider\SessionServiceProvider());

        $app->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => array($resourcePath.'/template/install'),
            'twig.form.templates' => array('bootstrap_3_horizontal_layout.html.twig'),
        ));

        $this->register(new \Silex\Provider\UrlGeneratorServiceProvider());
        $this->register(new \Silex\Provider\FormServiceProvider());
        $this->register(new \Silex\Provider\ValidatorServiceProvider());

        $this->register(new \Silex\Provider\TranslationServiceProvider(), array(
            'locale' => 'ja',
        ));
        $app['translator'] = $app->share($app->extend('translator', function($translator, \Silex\Application $app) use ($resourcePath) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());

            $r = new \ReflectionClass('Symfony\Component\Validator\Validator');
            $file = dirname($r->getFilename()).'/Resources/translations/validators.'.$app['locale'].'.xlf';
            if (file_exists($file)) {
                $translator->addResource('xliff', $file, $app['locale'], 'validators');
            }

            $file = $resourcePath.'/locale/validator.'.$app['locale'].'.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale'], 'validators');
            }

            $translator->addResource('yaml', $resourcePath.'/locale/ja.yml', $app['locale']);

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
