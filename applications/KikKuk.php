<?php
namespace {

    use FastRoute\Dispatcher;
    use KikKuk\Session;
    use KikKuk\SlimOverride\Request;
    use KikKuk\SlimOverride\Router;
    use KikKuk\SlimOverride\Uri;
    use KikKuk\Database;
    use KikKuk\Template;
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
            'view',
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
                    /**
                     * PSR-7 Request object
                     *
                     * @param Container $container
                     *
                     * @return ServerRequestInterface
                     */
                    'request' => function ($container) {
                        /** @var Container $container */
                        return Request::createFromEnvironment($container->get('environment'));
                    },
                    'settings' => [
                        'displayErrorDetails' => true,
                        'routerCacheFile' => KIK_KUK_ROUTER_CACHE_FILE
                    ],
                    'environment' => Environment::mock($this->portServerManipulation()),
                    'database_schema' => function () {
                        return require(KIK_KUK_COMPONENT_DIR . '/DatabaseSchema.php');
                    },
                    'view' => function () {
                        return new Template(KIK_KUK_VIEW_DIR);
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
                    'database' => function () {
                        return new Database(
                            [
                                'driver' => KIK_KUK_DB_DRIVER,
                                'prefix' => KIK_KUK_DB_PREFIX,
                                'path' => KIK_KUK_DB_FILE,
                                'dbhost' => KIK_KUK_DB_HOST,
                                'dbname' => KIK_KUK_DB_NAME,
                                'dbuser' => KIK_KUK_DB_USER,
                            ]
                        );
                    }
                ]
            );
            $container = $this->protectedSlim->getContainer();
            $container['slim'] = function () {
                return $this->protectedSlim;
            };

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