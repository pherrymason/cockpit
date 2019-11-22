<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use DI\ContainerBuilder;

/**
 * Cockpit start time
 */
define('COCKPIT_START_TIME', microtime(true));

if (!defined('COCKPIT_CLI')) {
    define('COCKPIT_CLI', PHP_SAPI == 'cli');
}

// Autoload vendor libs
define('APP_ROOT', dirname(__DIR__));
include(APP_ROOT.'/vendor/autoload.php');

// include core classes for better performance
if (!class_exists('Lime\\App')) {
    include(APP_ROOT.'/lib/Lime/App.php');
    include(APP_ROOT.'/lib/LimeExtra/App.php');
    include(APP_ROOT.'/lib/LimeExtra/Controller.php');
}

/*
 * Autoload from lib folder (PSR-0)
 */

spl_autoload_register(function($class){
    $class_path = APP_ROOT.'/lib/'.str_replace('\\', '/', $class).'.php';
    if(file_exists($class_path)) include_once($class_path);
});

// load .env file if exists
DotEnv::load(APP_ROOT);

// check for custom defines
if (file_exists(APP_ROOT.'/defines.php')) {
    include(APP_ROOT.'/defines.php');
}

/*
 * Collect needed paths
 */

$COCKPIT_DIR         = str_replace(DIRECTORY_SEPARATOR, '/', APP_ROOT);
$COCKPIT_DOCS_ROOT   = str_replace(DIRECTORY_SEPARATOR, '/', isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : dirname(APP_ROOT));

# make sure that $_SERVER['DOCUMENT_ROOT'] is set correctly
if (strpos($COCKPIT_DIR, $COCKPIT_DOCS_ROOT)!==0 && isset($_SERVER['SCRIPT_NAME'])) {
    $COCKPIT_DOCS_ROOT = str_replace(dirname(str_replace(DIRECTORY_SEPARATOR, '/', $_SERVER['SCRIPT_NAME'])), '', $COCKPIT_DIR);
}

$COCKPIT_BASE        = trim(str_replace($COCKPIT_DOCS_ROOT, '', $COCKPIT_DIR), "/");
$COCKPIT_BASE_URL    = strlen($COCKPIT_BASE) ? "/{$COCKPIT_BASE}": $COCKPIT_BASE;
$COCKPIT_BASE_ROUTE  = $COCKPIT_BASE_URL;

/*
 * SYSTEM DEFINES
 */
if (!defined('COCKPIT_DIR'))                    define('COCKPIT_DIR'            , $COCKPIT_DIR);
if (!defined('COCKPIT_ADMIN'))                  define('COCKPIT_ADMIN'          , 0);
if (!defined('COCKPIT_DOCS_ROOT'))              define('COCKPIT_DOCS_ROOT'      , $COCKPIT_DOCS_ROOT);
if (!defined('COCKPIT_ENV_ROOT'))               define('COCKPIT_ENV_ROOT'       , COCKPIT_DIR);
if (!defined('COCKPIT_BASE_URL'))               define('COCKPIT_BASE_URL'       , $COCKPIT_BASE_URL);
if (!defined('COCKPIT_API_REQUEST'))            define('COCKPIT_API_REQUEST'    , COCKPIT_ADMIN && strpos($_SERVER['REQUEST_URI'], COCKPIT_BASE_URL.'/api/')!==false ? 1:0);
if (!defined('COCKPIT_SITE_DIR'))               define('COCKPIT_SITE_DIR'       , COCKPIT_ENV_ROOT == COCKPIT_DIR ?  ($COCKPIT_DIR == COCKPIT_DOCS_ROOT ? COCKPIT_DIR : dirname(COCKPIT_DIR)) :  COCKPIT_ENV_ROOT);
if (!defined('COCKPIT_CONFIG_DIR'))             define('COCKPIT_CONFIG_DIR'     , COCKPIT_ENV_ROOT.'/config');
if (!defined('COCKPIT_BASE_ROUTE'))             define('COCKPIT_BASE_ROUTE'     , $COCKPIT_BASE_ROUTE);
if (!defined('COCKPIT_STORAGE_FOLDER'))         define('COCKPIT_STORAGE_FOLDER' , COCKPIT_ENV_ROOT.'/storage');
if (!defined('COCKPIT_ADMIN_CP'))               define('COCKPIT_ADMIN_CP'       , COCKPIT_ADMIN && !COCKPIT_API_REQUEST ? 1 : 0);
if (!defined('COCKPIT_PUBLIC_STORAGE_FOLDER'))  define('COCKPIT_PUBLIC_STORAGE_FOLDER' , COCKPIT_ENV_ROOT.'/storage');

if (!defined('COCKPIT_CONFIG_PATH')) {
    $_configpath = COCKPIT_CONFIG_DIR.'/config.'.(file_exists(COCKPIT_CONFIG_DIR.'/config.php') ? 'php':'yaml');
    define('COCKPIT_CONFIG_PATH', $_configpath);
}


function cockpit($module = null) {
    // TODO remove this.
    static $app;

    if (!$app) {
        $customconfig = [];
        $defaultConfiguration = require('config.php');

        // load custom config
        if (file_exists(COCKPIT_CONFIG_PATH)) {
            $customconfig = preg_match('/\.yaml$/', COCKPIT_CONFIG_PATH) ? Spyc::YAMLLoad(COCKPIT_CONFIG_PATH) : include(COCKPIT_CONFIG_PATH);
        }
        $configuration = array_merge($defaultConfiguration, $customconfig);

        // make sure Cockpit module is not disabled
        if (isset($configuration['modules.disabled']) && in_array('Cockpit', $configuration['modules.disabled'])) {
            array_splice($configuration['modules.disabled'], array_search('Cockpit', $configuration['modules.disabled']), 1);
        }

        // Container configuration
        $builder = new ContainerBuilder();
        $builder->useAnnotations(false);
        $builder->addDefinitions(require('services.php'));
//        $builder->enableCompilation($configuration['paths']['#tmp']);
        $container = $builder->build();

        $app = new LimeExtra\App($container, $configuration);

        $app['config'] = $configuration;

        // register paths
//        foreach ($configuration['paths'] as $key => $path) {
//            $app->path($key, $path);
//        }

        // set cache path
        $tmppath = $app->path('#tmp:');

        $app('cache')->setCachePath($tmppath);
        $app('renderer')->setCachePath($tmppath);

        // i18n
        $app('i18n')->locale = $config['i18n'] ?? 'en';

        // handle exceptions
        if (COCKPIT_ADMIN) {

            set_exception_handler(function($exception) use($app) {

                $error = [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];

                if ($app['debug']) {
                    $body = $app->req_is('ajax') || COCKPIT_API_REQUEST ? json_encode(['error' => $error['message'], 'file' => $error['file'], 'line' => $error['line']]) : $app->render('cockpit:views/errors/500-debug.php', ['error' => $error]);
                } else {
                    $body = $app->req_is('ajax') || COCKPIT_API_REQUEST ? '{"error": "500", "message": "system error"}' : $app->view('cockpit:views/errors/500.php');
                }

                $app->trigger('error', [$error, $exception]);

                header('HTTP/1.0 500 Internal Server Error');
                echo $body;

                if (function_exists('cockpit_error_handler')) {
                    cockpit_error_handler($error);
                }
            });
        }

        $modulesPaths = array_merge([
            COCKPIT_DIR.'/modules',  # core
            COCKPIT_DIR.'/addons' # addons
        ], $config['loadmodules'] ?? []);

        if (COCKPIT_ENV_ROOT !== COCKPIT_DIR) {
            $modulesPaths[] = COCKPIT_ENV_ROOT.'/addons';
        }

        // load modules
        $app->loadModules($modulesPaths);

        // load config global bootstrap file
        if ($custombootfile = $app->path('#config:bootstrap.php')) {
            include($custombootfile);
        }

        $app->trigger('cockpit.bootstrap');
    }

    // shorthand modules method call e.g. cockpit('regions:render', 'test');
    if (func_num_args() > 1) {

        $arguments = func_get_args();

        list($module, $method) = explode(':', $arguments[0]);
        array_splice($arguments, 0, 1);
        return call_user_func_array([$app->module($module), $method], $arguments);
    }

    return $module ? $app->module($module) : $app;
}

$cockpit = cockpit();
