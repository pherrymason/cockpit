<?php declare(strict_types=1);

namespace Cockpit\App;

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Spyc;

final class App
{
    /** @var array */
    private $config;
    /** @var \LimeExtra\App */
    private $cockpit;
    /** @var \Psr\Container\ContainerInterface */
    private $container;

    public function __construct(\Psr\Container\ContainerInterface $container, array $config)
    {
        $this->config = $config;
        $this->container = $container;
    }

    public function app(): \LimeExtra\App
    {
        return $this->cockpit;
    }

    public function boot(): \LimeExtra\App
    {
        define('COCKPIT_ADMIN', 1);
        date_default_timezone_set('UTC');

        define('COCKPIT_START_TIME', microtime(true));

        if (!defined('COCKPIT_CLI')) {
            define('COCKPIT_CLI', PHP_SAPI === 'cli');
        }

        define('APP_ROOT', dirname(__DIR__));

        spl_autoload_register(function($class){
            $class_path = APP_ROOT.'/lib/'.str_replace('\\', '/', $class).'.php';
            if(file_exists($class_path)) {
                include_once($class_path);
            }
        });

        // load .env file if exists
        DotEnv::createImmutable(APP_ROOT);

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
        if (!defined('COCKPIT_SITE_DIR'))               define('COCKPIT_SITE_DIR'       , COCKPIT_ENV_ROOT == COCKPIT_DIR ?  ($COCKPIT_DIR === COCKPIT_DOCS_ROOT ? COCKPIT_DIR : dirname(COCKPIT_DIR)) :  COCKPIT_ENV_ROOT);
        if (!defined('COCKPIT_CONFIG_DIR'))             define('COCKPIT_CONFIG_DIR'     , COCKPIT_ENV_ROOT.'/config');
        if (!defined('COCKPIT_BASE_ROUTE'))             define('COCKPIT_BASE_ROUTE'     , $COCKPIT_BASE_ROUTE);
        if (!defined('COCKPIT_STORAGE_FOLDER'))         define('COCKPIT_STORAGE_FOLDER' , COCKPIT_ENV_ROOT.'/storage');
        if (!defined('COCKPIT_ADMIN_CP'))               define('COCKPIT_ADMIN_CP'       , COCKPIT_ADMIN && !COCKPIT_API_REQUEST ? 1 : 0);
        if (!defined('COCKPIT_PUBLIC_STORAGE_FOLDER'))  define('COCKPIT_PUBLIC_STORAGE_FOLDER' , COCKPIT_ENV_ROOT.'/storage');

        if (!defined('COCKPIT_CONFIG_PATH')) {
            $_configpath = COCKPIT_CONFIG_DIR.'/config.'.(file_exists(COCKPIT_CONFIG_DIR.'/config.php') ? 'php':'yaml');
            define('COCKPIT_CONFIG_PATH', $_configpath);
        }


        $customconfig = [];
        $defaultConfiguration = require(APP_ROOT.'/config.php');

        // load custom config
        if (file_exists(COCKPIT_CONFIG_PATH)) {
            $customconfig = preg_match('/\.yaml$/', COCKPIT_CONFIG_PATH) ? Spyc::YAMLLoad(COCKPIT_CONFIG_PATH) : include(COCKPIT_CONFIG_PATH);
        }
        $configuration = array_merge($defaultConfiguration, $customconfig);

        // make sure Cockpit module is not disabled
        if (isset($configuration['modules.disabled']) && in_array('Cockpit', $configuration['modules.disabled'])) {
            array_splice($configuration['modules.disabled'], array_search('Cockpit', $configuration['modules.disabled']), 1);
        }

        $app = new \LimeExtra\App($this->container, $configuration);
        $this->container->set('app', $app);
        $app['config'] = $configuration;

        // register paths
//        foreach ($configuration['paths'] as $key => $path) {
//            $app->path($key, $path);
//        }

        // set cache path
        $tmppath = $app->path('#tmp:');

        $app->get('cache')->setCachePath($tmppath);
        $app->get('renderer')->setCachePath($tmppath);

        // i18n
        $app->get('i18n')->locale = $config['i18n'] ?? 'en';

        // handle exceptions
        if (COCKPIT_ADMIN) {
            $whoops = new \Whoops\Run;
            $whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
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

        if (func_num_args() > 1) {

            $arguments = func_get_args();

            list($module, $method) = explode(':', $arguments[0]);
            array_splice($arguments, 0, 1);
            return call_user_func_array([$app->module($module), $method], $arguments);
        }

        # admin route
        if (COCKPIT_ADMIN && !defined('COCKPIT_ADMIN_ROUTE')) {
            $route = preg_replace('#'.preg_quote(COCKPIT_BASE_URL, '#').'#', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), 1);
            define('COCKPIT_ADMIN_ROUTE', $route == '' ? '/' : $route);
        }

        if (COCKPIT_API_REQUEST) {

            $_cors = $app->retrieve('config/cors', []);

            header('Access-Control-Allow-Origin: '      .($_cors['allowedOrigins'] ?? '*'));
            header('Access-Control-Allow-Credentials: ' .($_cors['allowCredentials'] ?? 'true'));
            header('Access-Control-Max-Age: '           .($_cors['maxAge'] ?? '1000'));
            header('Access-Control-Allow-Headers: '     .($_cors['allowedHeaders'] ?? 'X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding, Cockpit-Token'));
            header('Access-Control-Allow-Methods: '     .($_cors['allowedMethods'] ?? 'PUT, POST, GET, OPTIONS, DELETE'));
            header('Access-Control-Expose-Headers: '    .($_cors['exposedHeaders'] ?? 'true'));

            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                exit(0);
            }
        }

        $this->cockpit = $app;

        return $this->cockpit;
    }

    public function run(): void
    {
        $this->cockpit = $this->boot();
        $this->cockpit->set('route', COCKPIT_ADMIN_ROUTE)->trigger('admin.init')->run();
    }
}
