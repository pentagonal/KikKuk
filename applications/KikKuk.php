<?php
namespace {

    use FastRoute\Dispatcher;
    use KikKuk\Handler\StreamHandlerMinimized;
    use KikKuk\Logger;
    use KikKuk\Session;
    use KikKuk\SlimOverride\Request;
    use KikKuk\SlimOverride\Router;
    use KikKuk\SlimOverride\Uri;
    use KikKuk\Database;
    use KikKuk\Template;
    use KikKuk\Utilities\DatabaseUtility;
    use Monolog\ErrorHandler;
    use Psr\Http\Message\ServerRequestInterface;
    use Slim\App as Slim;
    use Slim\Container;
    use Slim\Http\Environment;

    /**
     * Class KikKuk
     */
    final class KikKuk implements \ArrayAccess
    {
        /**
         * @var KikKuk
         */
        protected static $instance;

        /**
         * protected Applications
         *
         * @var array
         */
        protected $protectedApplication = [
            'slim',
            'app',
            'database',
            'database_schema',
            'session',
            'admin_view',
            'view',
            'log',
            'error_handler',
            // default
            'environment',
            'router',
            'request',
            'response',
            'settings',
        ];

        /**
         * @var Slim
         */
        protected $protectedSlim;

        /**
         * App constructor.
         */
        public function __construct()
        {
            // stop here application only run once
            if (self::$instance) {
                return;
            }
            self::$instance = $this;
            $this->process();
        }

        /**
         * Process Application
         */
        protected function process()
        {
            $this->protectedSlim = new Slim(
                [
                    /**
                     * FallBack ApplicationInitiator
                     *
                     * @return KikKuk
                     */
                    'app' => $this,
                    'log' => function() {
                        // try to log
                        try {
                            $log_level = ! is_numeric(KIK_KUK_LOG_LEVEL)
                                ? strtoupper(KIK_KUK_LOG_LEVEL)
                                : KIK_KUK_LOG_LEVEL;
                            $log_level = Logger::getLevelName($log_level);
                        } catch(\Exception $e) {
                            $log_level = (KIK_KUK_DEBUG ? 'INFO' : 'NOTICE');
                        }

                        /**
                         * Create index.html & htacess
                         */
                        if (is_dir(KIK_KUK_LOG_DIR) && is_writable(KIK_KUK_LOG_DIR)) {
                            if (!file_exists(KIK_KUK_LOG_DIR .'/index.html')) {
                                @file_put_contents(KIK_KUK_LOG_DIR.'/index.html', '');
                            }
                            if (!file_exists(KIK_KUK_LOG_DIR .'/.htaccess')) {
                                @file_put_contents(KIK_KUK_LOG_DIR.'/.htaccess', 'Deny From All');
                            }
                        }

                        $level  = Logger::toMonologLevel($log_level);
                        $array_handler = new KikKuk\Handler\ArrayHandler($level);
                        $logger = new Logger(__CLASS__, [$array_handler]);
                        if (KIK_KUK_DEBUG) {
                            // NO DEBUG - DEBUG HANDLED BY IT SELF
                            // ['DEBUG'    => new StreamHandler(KIK_KUK_LOG_DIR . "/debug.log", Logger::DEBUG)]
                            $logger->setHandlers(
                                [
                                    'ALERT'    => new StreamHandlerMinimized(
                                        KIK_KUK_LOG_DIR . "/info/info.log",
                                        Logger::ALERT
                                    ),
                                    'CRITICAL' => new StreamHandlerMinimized(
                                        KIK_KUK_LOG_DIR . "/critical/critical.log",
                                        Logger::CRITICAL
                                    ),
                                    'INFO'     => new StreamHandlerMinimized(
                                        KIK_KUK_LOG_DIR . "/info/info.log",
                                        Logger::INFO
                                    ),
                                    'EMERGENCY'=> new StreamHandlerMinimized(
                                        KIK_KUK_LOG_DIR . "/emergency/emergency.log",
                                        Logger::EMERGENCY
                                    ),
                                    'ERROR'    => new StreamHandlerMinimized(
                                        KIK_KUK_LOG_DIR . "/error/error.log",
                                        Logger::ERROR
                                    ),
                                    'WARNING'  => new StreamHandlerMinimized(
                                        KIK_KUK_LOG_DIR . "/warning/warning.log",
                                        Logger::WARNING
                                    ),
                                    'NOTICE'   => new StreamHandlerMinimized(
                                        KIK_KUK_LOG_DIR . "/notice/notice.log",
                                        Logger::NOTICE
                                    ),
                                ]
                            );
                        }

                        return $logger;
                    },
                    /**
                     * PSR-7 Request object
                     *
                     * @param Container $container
                     *
                     * @return ServerRequestInterface
                     */
                    'request' => function ($container) {
                        /**
                         * @var Container $container
                         */
                        return Request::createFromEnvironment($container->get('environment'));
                    },
                    'settings' => [
                        'displayErrorDetails' => true,
                        'routerCacheFile' => KIK_KUK_ROUTER_CACHE_FILE
                    ],
                    'environment' => Environment::mock($this->portServerManipulation()),
                    'database_schema' => function () {
                        return [
                            'base'  => require(KIK_KUK_COMPONENT_DIR . '/DatabaseSchema/BaseSchema.php'),
                            'event' => require(KIK_KUK_COMPONENT_DIR . '/DatabaseSchema/EventSchema.php'),
                            'license' => require(KIK_KUK_COMPONENT_DIR . '/DatabaseSchema/LicensingSchema.php'),
                        ];
                    },
                    'view' => function ($container) {
                        /**
                         * @var Container|Logger[]  $container
                         * @var Session    $session Session
                         * @var Template   $template Template
                         * @var Uri        $uri
                         */
                        $session  = $this['session'];
                        $uri = $container->get('request')->getUri();
                        $base_url = $uri->getBaseUrl();
                        $base_dir = realpath(KIK_KUK_WEB_DIR);
                        if (!$base_dir) {
                            $container['log']->debug(
                                sprintf(
                                    'php function realpath does not work. Fallback to Default %s',
                                    'KIK_KUK_WEB_DIR'
                                ),
                                [
                                    'KIK_KUK_WEB_DIR' => KIK_KUK_WEB_DIR
                                ]
                            );
                            $base_dir = preg_replace(
                                '/(\\\|\/)+/',
                                '/',
                                dirname($_SERVER['SCRIPT_FILENAME'])
                            );
                        }
                        if (!is_dir($base_dir)) {
                            throw new \RuntimeException(
                                'Web directory does not exists.',
                                E_USER_ERROR
                            );
                        }
                        if (!is_dir(KIK_KUK_TEMPLATE_DIR)) {
                            throw new \RuntimeException(
                                'Directory views does not exists.',
                                E_USER_ERROR
                            );
                        }
                        $view_dir = realpath(KIK_KUK_TEMPLATE_DIR);
                        if (!$view_dir) {
                            $container['log']->debug(
                                sprintf(
                                    'php function realpath does not work. Fallback to Default %s',
                                    'KIK_KUK_TEMPLATE_DIR'
                                ),
                                [
                                    'KIK_KUK_TEMPLATE_DIR' => KIK_KUK_TEMPLATE_DIR
                                ]
                            );
                            $view_dir = KIK_KUK_TEMPLATE_DIR;
                        }
                        $view_dir = preg_replace(
                            '/(\\\|\/)+/',
                            '/',
                            $view_dir
                        );
                        $view_dir = rtrim($view_dir, '/') . '/';
                        $base_dir = rtrim($base_dir, '/') . '/';
                        if ($base_dir == $view_dir
                            || stripos(PHP_OS, 'win') !== false
                            && (
                                stripos($view_dir, $base_dir) !== 0
                                || $base_dir == $view_dir
                            )
                            || strpos($view_dir, $base_dir) !== 0
                        ) {
                            throw new \RuntimeException(
                                'Directory views invalid, Directory views must be inside of Web directory.',
                                E_USER_ERROR
                            );
                        }

                        $template = new Template(rtrim($view_dir, '/'));
                        /**
                         * Set Token
                         */
                        $template->setAttributes(
                            [
                                'token'    => $session->getCsrfTokenValue(),
                                'base_url' => $base_url,
                                'template_url' => rtrim($base_url) . '/' . substr($view_dir, strlen($base_dir)),
                            ]
                        );

                        return $template;
                    },
                    'admin_view' => function ($container) {
                        /**
                         * @var Container  $container
                         * @var Session    $session Session
                         * @var Template   $template Template
                         * @var Uri        $uri
                         */
                        $session  = $this['session'];
                        $template = new Template(__DIR__ .'/AdminViews');
                        $uri = $container->get('request')->getUri();

                        /**
                         * Set Token
                         */
                        $template->setAttributes(
                            [
                                'token'    => $session->getCsrfTokenValue(),
                                'base_url'     => $uri->getBaseUrl(),
                                'template_url' => $uri->getBaseUrl(),
                            ]
                        );
                        return $template;
                    },
                    /**
                     * This service MUST return a SHARED instance
                     * of \Slim\Interfaces\RouterInterface.
                     *
                     * @param \Slim\Container $container
                     *
                     * @return \Slim\Interfaces\RouterInterface
                     */
                    'router' => function ($container) {
                        /** @var Container $container */
                        $routerCacheFile = false;
                        if (isset($container->get('settings')['routerCacheFile'])) {
                            $routerCacheFile = $container->get('settings')['routerCacheFile'];
                        }

                        $router = (new Router)->setCacheFile($routerCacheFile);
                        if (method_exists($router, 'setContainer')) {
                            $router->setContainer($container);
                        }

                        return $router;
                    },
                    'session' => function () {
                        $session = new Session();
                        $session->getSession()->setSavePath(KIK_KUK_SESSION_DIR . '/');
                        $session->getSession()->setName(KIK_KUK_SESSION_NAME);
                        $session->getSession()->setCookieParams(
                            [
                                'path' => KIK_KUK_SESSION_PATH,
                                'lifetime' => null,
                                'domain' => null,
                                'secure' => null,
                                'httponly' => null,
                            ]
                        );

                        return $session;
                    },
                    'database' => function ($container) {
                        $database = new Database(
                            [
                                'driver' => KIK_KUK_DB_DRIVER,
                                'prefix' => KIK_KUK_DB_PREFIX,
                                'path' => KIK_KUK_DB_FILE,
                                'dbhost' => KIK_KUK_DB_HOST,
                                'dbname' => KIK_KUK_DB_NAME,
                                'dbuser' => KIK_KUK_DB_USER,
                            ]
                        );
                        // call self Resolve Database
                        $this->selfResolveDatabase($database, $container);
                        return $database;
                    }
                ]
            );
            $container = $this->protectedSlim->getContainer();

            /**
             * Register Error Handler
             */
            $container['error_handler'] = ErrorHandler::register($container['log']);

            /**
             * @return Slim
             */
            $container['slim'] = function () {
                return $this->protectedSlim;
            };

            // create upload dir & index
            if (!is_dir(KIK_KUK_UPLOAD_DIR) && is_writable(dirname(KIK_KUK_UPLOAD_DIR))) {
                if (@mkdir(KIK_KUK_UPLOAD_DIR, 0777, true)) {
                    @file_put_contents(KIK_KUK_UPLOAD_DIR . '/index.html', '');
                }
            } elseif (is_dir(KIK_KUK_UPLOAD_DIR) && !file_exists(KIK_KUK_UPLOAD_DIR .'/index.html')
                && is_writable(KIK_KUK_UPLOAD_DIR)
            ) {
                @file_put_contents(KIK_KUK_UPLOAD_DIR . '/index.html', '');
            }

            /**
             * Fix Rewrite
             */
            $env = clone $container->get('environment');
            $env['SCRIPT_NAME'] = dirname($env['SCRIPT_NAME']);
            $request = clone $container->get('request');
            unset($container['request']);
            $container['request'] = $request->withUri(Uri::createFromEnvironment($env));
            $this->protectedSlim = $container->get('slim');
            $this->runSlim($this->protectedSlim);
        }

        /**
         * @param string $path
         * @return string
         */
        public static function getBaseUrl($path = '')
        {
            $baseUrl = rtrim(KikKuk::get('request')->getUri()->getBaseUrl(), '/');
            if (!is_string($path)) {
                settype($path, 'string');
            }
            if ($path != '') {
                if (strpos($path, '?') !== false) {
                    $path = explode('?', $path);
                    $path[0] = preg_replace('/(\\\|\/)+/', '/', $path[0]);
                    $path = implode('?', $path);
                } else {
                    $path = preg_replace('/(\\\|\/)+/', '/', $path);
                }

                $path = ltrim($path, '/');
                if ($path != '') {
                    $path = '/' . $path;
                }
            }

            return $baseUrl . $path;
        }

        /**
         * @param Slim $slim
         */
        protected function runSlim(Slim &$slim)
        {
            if (!file_exists(KIK_KUK_COMPONENT_DIR . '/Container.php')) {
                throw new \RuntimeException(
                    'Container file not found',
                    E_COMPILE_ERROR
                );
            };
            if (!file_exists(KIK_KUK_COMPONENT_DIR . '/Middleware.php')) {
                throw new \RuntimeException(
                    'Middleware file not found',
                    E_COMPILE_ERROR
                );
            };
            if (!file_exists(KIK_KUK_COMPONENT_DIR . '/Routes.php')) {
                throw new \RuntimeException(
                    'Route file not found',
                    E_COMPILE_ERROR
                );
            };

            require_once KIK_KUK_COMPONENT_DIR . '/Container.php';
            require_once KIK_KUK_COMPONENT_DIR . '/Middleware.php';
            require_once KIK_KUK_COMPONENT_DIR . '/Routes.php';
            if (!$slim instanceof Slim) {
                throw new \RuntimeException(
                    'Slim Application has been override.',
                    E_COMPILE_ERROR
                );
            }

            $slim->run();
        }

        /**
         * Self Resolve Database Structures
         * @param Database $database
         * @param Container $container
         */
        protected function selfResolveDatabase(Database $database, $container)
        {
            /* --------------------------------------------------------
             * SELF RESOLVE DATABASE
             * --------------------------------------------------------
             */

            /**
             * Database Structures
             * @var array
             */
            $database_schema = (array) $container['database_schema'];

            if (!isset($database_schema['base']) || !is_array($database_schema['base'])) {
                $database_schema['base'] = [];
            }

            if (!isset($database_schema['event']) || !is_array($database_schema['event'])) {
                $database_schema['event'] = [];
            }

            if (!isset($database_schema['license']) || !is_array($database_schema['license'])) {
                $database_schema['license'] = [];
            }

            $structures = $database_schema['base'];

            /* -----------------------------------------------
                        WITH ADDITIONAL DATABASE
             ----------------------------------------------- */

            if (KIK_KUK_DB_WITH_EVENT == true) {
                $structures = array_merge($structures, $database_schema['event']);
            }
            if (KIK_KUK_DB_WITH_LICENSE == true) {
                $structures = array_merge($structures, $database_schema['license']);
            }

            /**
             * @var array $tables
             */
            $tables = $database->getSchemaManager()->listTableNames();
            $theTables = [];
            foreach (array_diff(array_keys($structures), $tables) as $value) {
                $theTables[$value] = $structures[$value];
            }
            if (!empty($theTables)) {
                DatabaseUtility::execSchema($theTables);
            }
        }

        /**
         * Run The Application
         * @return KikKuk
         */
        public static function run()
        {
            if (!self::$instance) {
                new KikKuk();
            }

            return self::$instance;
        }

        /**
         * Detecting & Fix Environment on some cases
         *     Default Environment uses $_SERVER to attach
         *     just to fix https
         *
         * @return array
         */
        protected function portServerManipulation()
        {
            static $server;
            if (isset($server)) {
                return $server;
            }

            $server = $_SERVER;
            if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off'
                // hide behind proxy / maybe cloud flare cdn
                || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
                || !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off'
            ) {
                // detect if non standard protocol
                if ($_SERVER['SERVER_PORT'] == 80
                    && (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                        || isset($_SERVER['HTTP_FRONT_END_HTTPS'])
                    )
                ) {
                    $_SERVER['SERVER_PORT_MANIPULATED'] = 443;
                    $server['SERVER_PORT'] = 443;
                    $server['SERVER_PORT_MANIPULATED'] = 80;
                }
                // fixing HTTPS Environment
                $_SERVER['HTTPS_MANIPULATED'] = 'on';
                $server['HTTPS'] = 'on';
                $server['HTTPS_MANIPULATED'] = 'on';
            }

            return $server;
        }

        /**
         * Get Current Matched Route
         *
         * @return array
         */
        public function getCurrentRouteInfo()
        {
            return $this->get('router')->dispatch($this->get('request'));
        }

        /**
         * Check if Route Found
         *
         * @return bool
         */
        public function isCurrentRouteFound()
        {
            $dispatchedRoute = $this->getCurrentRouteInfo();

            return $dispatchedRoute[0] == Dispatcher::FOUND;
        }

        /**
         * Check Container
         *
         * @param string $name
         * @return bool
         */
        public static function has($name)
        {
            $return = self::run()->protectedSlim->getContainer()->has($name);

            return $return;
        }

        /**
         * Get Container value
         *
         * @param string $name
         * @param string $default
         * @return mixed
         */
        public static function get($name, $default = null)
        {
            if (self::has($name)) {
                $default = self::$instance->protectedSlim->getContainer()->get($name);
            }

            return $default;
        }

        /**
         * Set Container
         *
         * @param string $name
         * @param string $value
         */
        public static function set($name, $value)
        {
            if (self::has($name)) {
                if (in_array($name, self::$instance->protectedApplication)) {
                    throw new \InvalidArgumentException(
                        sprintf('Can not overrdie protected application %s', $name),
                        E_USER_WARNING
                    );
                }
            }

            $container = self::$instance->protectedSlim->getContainer();
            unset($container[$name]);
            if (is_callable($value)) {
                $container[$name] = function ($container) use ($name, $value) {
                    return $value($container);
                };
            } else {
                $container[$name] = $value;
            }
        }

        /**
         * Unset Container
         *
         * @param string $name
         */
        public static function remove($name)
        {
            if (self::has($name)) {
                if (in_array($name, self::$instance->protectedApplication)) {
                    throw new \InvalidArgumentException(
                        sprintf('Can not remove protected application %s', $name),
                        E_USER_WARNING
                    );
                }
                $container = self::$instance['slim']->getContainer();
                unset($container[$name]);
            }
        }

        /**
         * Magic Method Get
         *
         * @param string $name
         * @return mixed
         */
        public function __get($name)
        {
            return $this->get($name);
        }

        /**
         * Magic Method Set Container
         *
         * @param string $name
         * @param mixed  $value
         */
        public function __set($name, $value)
        {
            $this->set($name, $value);
        }

        /**
         * Magic Method Isset
         *
         * @param string $name
         * @return bool
         */
        public function __isset($name)
        {
            return $this->has($name);
        }

        /**
         * Whether a offset exists
         * @link http://php.net/manual/en/arrayaccess.offsetexists.php
         *
         * @param mixed $offset An offset to check for.
         * @return boolean true on success or false on failure.
         */
        public function offsetExists($offset)
        {
            return $this->has($offset);
        }

        /**
         * Offset to retrieve
         * @link http://php.net/manual/en/arrayaccess.offsetget.php
         *
         * @param mixed $offset The offset to retrieve.
         * @return mixed Can return all value types.
         */
        public function offsetGet($offset)
        {
            return $this->get($offset);
        }

        /**
         * Offset to set
         * @link http://php.net/manual/en/arrayaccess.offsetset.php
         *
         * @param mixed $offset The offset to assign the value to.
         * @param mixed $value  The value to set.
         * @return void
         */
        public function offsetSet($offset, $value)
        {
            $this->set($offset, $value);
        }

        /**
         * Offset to unset
         * @link http://php.net/manual/en/arrayaccess.offsetunset.php
         *
         * @param mixed $offset The offset to unset.
         * @return void
         */
        public function offsetUnset($offset)
        {
            $this->remove($offset);
        }
    }
}