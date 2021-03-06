<?php
/**
 * Project: zaralab
 * Filename: Config.php
 *
 * @author Miroslav Yovchev <m.yovchev@corllete.com>
 * @since 02.11.15
 */

namespace Zaralab\Framework;


use Slim\Container;

class Config
{
    const VERSION = '0.0.1';

    /**
     * @param string $appPath path to app/ folder
     * @param string|null $env Force environment
     * @param bool|null $debug Force debug
     * @return Container
     * @todo Application environment based settings
     */
    public static function containerFactory($appPath, $env = null, $debug = null)
    {
        include $appPath.'/functions.php';

        $appPath = realpath($appPath);
        $rootPath = realpath($appPath.'/..');
        $configPath = $appPath.'/config';

        if (!is_readable($configPath.'/settings.php')) {
            throw new \RuntimeException("Application configuration file not found.");
        }

        if (null === $env) {
            $env = getenv ('SLIM3_ENV') ?: 'dev';
        }

        if (null === $debug) {
            $debug = getenv('SLIM3_DEBUG') !== '0' && $env != 'prod';
        }

        $params = [];
        if (is_readable($configPath.'/parameters.php')) {
            $params = include $configPath.'/parameters.php';
        }

        if (is_readable($configPath.'/parameters_'.$env.'.php')) {
            $paramsEnv = include $configPath.'/parameters_'.$env.'.php';
            $params = array_replace_recursive($params, $paramsEnv);
        }

        $config = require $configPath.'/settings.php';
        $config['PROJECT_ROOT'] = $rootPath;
        $config['APP_PATH'] = $appPath;
        $config['CONF_PATH'] = $configPath;

        if (is_readable($configPath.'/settings_'.$env.'.php')) {
            $configEnv = include $configPath.'/settings_'.$env.'.php';
            $config = array_replace_recursive($config, $configEnv);
        }


        $config['SECRET'] = base64_encode(vartrue($params['secret'], 'secret'));
        $config['ENV'] = $env;
        $config['DEBUG'] = $debug;
        $config['VERSION'] = static::VERSION;
        $config['APPNAME'] = isset($params['app.name']) ? $params['app.name'] : basename($config['BASEPATH']);

        return new Container($config);
    }
}