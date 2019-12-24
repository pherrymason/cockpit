<?php declare(strict_types=1);

namespace Cockpit;

use CLI;

final class App
{
    const MODE_CLI = 1;
    const MODE_HTTP = 2;

    /** @var \Psr\Container\ContainerInterface */
    private $container;
    /** @var string */
    private $appPath;
    /** @var string */
    private $publicPath;
    /** @var array */
    private $configuration;
    /** @var bool */
    private $mode;
    /** @var \LimeExtra\App */
    private $cockpit;

    /**
     * @param \Psr\Container\ContainerInterface $container
     * @param string $appPath The path to the root your application.
     * @param string $publicPath The path from where the Server serves its document. Usually the path where index.php is located.
     * @param array $configuration Configuration parameters.
     * @param int $mode Request mode
     */
    public function __construct(\Psr\Container\ContainerInterface $container, string $appPath, string $publicPath, array $configuration, int $mode)
    {
        $this->appPath = $appPath;
        $this->publicPath = $publicPath;
        $this->configuration = $configuration;
        $this->mode = $mode;
        $this->container = $container;
    }

    public function boot(): self
    {
        define('COCKPIT_START_TIME', microtime(true));

        // set default timezone
        date_default_timezone_set('UTC');

        $cockpitRootPath = dirname(__DIR__, 2);

        /*
         * Autoload from lib folder (PSR-0)
         */

        spl_autoload_register(function ($class) use ($cockpitRootPath) {
            $class_path = $cockpitRootPath . '/lib/' . str_replace('\\', '/', $class) . '.php';
            if (file_exists($class_path)) {
                include_once($class_path);
            }
        });

        // load .env file if exists
        \DotEnv::load($this->appPath);

        /*
         * Collect needed paths
         */


        /*
         * SYSTEM DEFINES
         *
         * Definitions
         * COCKPIT_DIR:         Root directory of cockpit src
         * COCKPIT_ENV_ROOT:    Application path. Not necessarily the same path where cockpit is located.
         *                      This is where customization will be stored.
         *                      For example:
         *                        - /addons
         *                        - /config
         *                        - /storage.
         * COCKPIT_DOCS_ROOT:   The path to the public folder.
         * COCKPIT_SITE_DIR:    ????
         *
         * Behavioural constants
         * COCKPIT_ADMIN          Are we handling a request to the Dashboard?
         * COCKPIT_API_REQUEST    Are we handling an API request?
         */
        define('COCKPIT_DIR', $cockpitRootPath);
        define('COCKPIT_ENV_ROOT', $this->appPath);
        define('COCKPIT_DOCS_ROOT', $this->publicPath);
        define('COCKPIT_CONFIG_DIR', COCKPIT_ENV_ROOT . '/config');
        define('COCKPIT_STORAGE_FOLDER', COCKPIT_ENV_ROOT . '/storage');
        define('COCKPIT_PUBLIC_STORAGE_FOLDER', COCKPIT_ENV_ROOT . '/storage');
        if (COCKPIT_ENV_ROOT === COCKPIT_DIR) {
            define('COCKPIT_SITE_DIR', $cockpitRootPath === COCKPIT_DOCS_ROOT ? COCKPIT_DIR : dirname(COCKPIT_DIR));
        } else {
            define('COCKPIT_SITE_DIR', COCKPIT_ENV_ROOT);
        }

        if (!defined('COCKPIT_CONFIG_PATH')) {
            $_configpath = COCKPIT_CONFIG_DIR . '/config.' . (file_exists(COCKPIT_CONFIG_DIR . '/config.php') ? 'php' : 'yaml');
            define('COCKPIT_CONFIG_PATH', $_configpath);
        }


        $baseURL = trim($this->configuration['base_url'], '/'); //trim($this->baseURL, "/");
        $baseURL = strlen($baseURL) ? "/{$baseURL}" : $baseURL;
        define('COCKPIT_BASE_URL', $baseURL);
        define('COCKPIT_BASE_ROUTE', $baseURL);

        define('COCKPIT_ADMIN', $this->isHTTP());
        define('COCKPIT_CLI', $this->isCLI());
        define('COCKPIT_API_REQUEST', $this->isHTTP() && strpos($_SERVER['REQUEST_URI'], COCKPIT_BASE_URL . '/api/') !== false ? 1 : 0);
        define('COCKPIT_ADMIN_CP', $this->isHTTP() && !COCKPIT_API_REQUEST ? 1 : 0);


        // load config
        $config = array_replace_recursive($this->defaultConfiguration(), $this->configuration);
        $this->configuration = $config;
        // Must overwrite final configuration parameters into container
        foreach ($config as $key => $value) {
            $this->container->set($key, $value);
        }

        // make sure Cockpit module is not disabled
        if (isset($this->configuration['modules.disabled']) && in_array('Cockpit', $this->configuration['modules.disabled'])) {
            array_splice($this->configuration['modules.disabled'], array_search('Cockpit', $this->configuration['modules.disabled']), 1);
        }

        $app = new \LimeExtra\App($this->container, $this->configuration);
        $this->container->set('app', $app);
        $this->container->set('root_path', $this->appPath);
        $app['config'] = $this->configuration;

        // set cache path
        $tmppath = $app->path('#tmp:');

        $app('cache')->setCachePath($tmppath);
        $app->renderer->setCachePath($tmppath);

        // i18n
        $app('i18n')->locale = $config['i18n'] ?? 'en';

        switch ($this->mode) {
            case self::MODE_HTTP:
                $this->configureDashboard($app);
                break;
            case self::MODE_CLI:
                $this->configureCLI($app);
                break;
        }

        $modulesPaths = array_merge([
            COCKPIT_DIR . '/modules',  # core
            COCKPIT_DIR . '/addons' # addons
        ], $config['loadmodules'] ?? []);

        if (COCKPIT_ENV_ROOT !== COCKPIT_DIR) {
            $modulesPaths[] = COCKPIT_ENV_ROOT . '/addons';
        }

        // load modules
        $app->loadModules($modulesPaths);

        // load config global bootstrap file
        if ($custombootfile = $app->path('#config:bootstrap.php')) {
            include($custombootfile);
        }

        $app->trigger('cockpit.bootstrap');

        // shorthand modules method call e.g. cockpit('regions:render', 'test');
        if (func_num_args() > 1) {

            $arguments = func_get_args();

            list($module, $method) = explode(':', $arguments[0]);
            array_splice($arguments, 0, 1);
            return call_user_func_array([$app->module($module), $method], $arguments);
        }

        $this->cockpit = $app;

        return $this;
    }

    public function run()
    {
        $this->cockpit->set('route', COCKPIT_ADMIN_ROUTE)->trigger('admin.init')->run();
    }

    public function cockpit(): \LimeExtra\App
    {
        return $this->cockpit;
    }

    protected function defaultConfiguration(): array
    {
        return [
            'debug' => preg_match('/(localhost|::1|\.local)$/', @$_SERVER['SERVER_NAME']),
            'app.name' => 'Cockpit',
            'base_url' => COCKPIT_BASE_URL,
            'base_route' => COCKPIT_BASE_ROUTE,
            'docs_root' => COCKPIT_DOCS_ROOT,
            'session.name' => md5(COCKPIT_ENV_ROOT),
            'session.init' => ($this->isHTTP() && !COCKPIT_API_REQUEST),
            'sec-key' => 'xxxxx-SiteSecKeyPleaseChangeMe-xxxxx',
            'ui.i18n' => 'en',
            // Content languages
            'languages' => [
                //'default' => 'English',       #setting a default language is optional
                'fr' => 'French',
                'de' => 'German'
            ],
            'modules.disabled' => [],
            'database.config'     => [
                'driver' => 'mongolite',
                'server' => 'mongolite://'.(COCKPIT_STORAGE_FOLDER.'/data'),
                'options' => ['db' => 'cockpitdb'],
                'driverOptions' => []
            ],
            'memory.config'       => [
                'server' => 'redislite://'.(COCKPIT_STORAGE_FOLDER.'/data/cockpit.memory.sqlite'),
                'options' => []
            ],
            'mailer.config' => [
                'from'       => 'info@mydomain.tld',
                'transport'  => 'mail',   //mail|smtp
                'host'       => 'smtp.myhost.tld',
                'user'       => 'username',
                'password'   => 'xxpasswordxx',
                'port'       => 25,
                'auth'       => true,
                'encryption' => ''
            ],
            'groups' => [],
            'cors' => [],

            'paths' => [
                '#root' => COCKPIT_DIR,
                '#storage' => COCKPIT_STORAGE_FOLDER,
                '#pstorage' => COCKPIT_PUBLIC_STORAGE_FOLDER,
                '#data' => COCKPIT_STORAGE_FOLDER . '/data',
                '#cache' => COCKPIT_STORAGE_FOLDER . '/cache',
                '#tmp' => COCKPIT_STORAGE_FOLDER . '/tmp',
                '#thumbs' => COCKPIT_PUBLIC_STORAGE_FOLDER . '/thumbs',
                '#uploads' => COCKPIT_PUBLIC_STORAGE_FOLDER . '/uploads',
                '#modules' => COCKPIT_DIR . '/modules',
                '#addons' => COCKPIT_ENV_ROOT . '/addons',
                '#config' => COCKPIT_CONFIG_DIR,
                'assets' => COCKPIT_DIR . '/assets',
                'site' => COCKPIT_SITE_DIR
            ],
            'filestorage.config' => []
        ];
    }

    protected function configureDashboard(\LimeExtra\App $app): void
    {
        $whoops = new \Whoops\Run;
        $whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();

        # admin route
        if (!defined('COCKPIT_ADMIN_ROUTE')) {
            $route = preg_replace('#' . preg_quote(COCKPIT_BASE_URL, '#') . '#', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), 1);
            define('COCKPIT_ADMIN_ROUTE', $route === '' ? '/' : $route);
        }
    }

    private function configureAPI(\LimeExtra\App $app): void
    {
        $_cors = $app->retrieve('config/cors', []);

        header('Access-Control-Allow-Origin: ' . ($_cors['allowedOrigins'] ?? '*'));
        header('Access-Control-Allow-Credentials: ' . ($_cors['allowCredentials'] ?? 'true'));
        header('Access-Control-Max-Age: ' . ($_cors['maxAge'] ?? '1000'));
        header('Access-Control-Allow-Headers: ' . ($_cors['allowedHeaders'] ?? 'X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding, Cockpit-Token'));
        header('Access-Control-Allow-Methods: ' . ($_cors['allowedMethods'] ?? 'PUT, POST, GET, OPTIONS, DELETE'));
        header('Access-Control-Expose-Headers: ' . ($_cors['exposedHeaders'] ?? 'true'));

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }

    private function configureCLI(\LimeExtra\App $app): void
    {
        set_exception_handler(function ($exception) use ($app) {
            /** @var \Exception $exception */
            $error = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];

            $app->trigger('error', [$error, $exception]);

            if (function_exists('cockpit_error_handler')) {
                cockpit_error_handler($error);
            }

            CLI::writeln('COCKPIT CLI ERROR:', false);
            CLI::writeln('-> in ' . $error['file'] . ':' . $error['line'] . "\n");
            CLI::writeln($error['message'] . "\n");
        });

        register_shutdown_function(function () use ($app) {
            $app->trigger('shutdown');
        });
    }

    private function isHTTP(): bool
    {
        return $this->mode === self::MODE_HTTP;
    }

    private function isCLI(): bool
    {
        return $this->mode === self::MODE_CLI;
    }
}
