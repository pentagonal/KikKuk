<?php
/**
 * Pentagonal Autoload Register
 *
 * @author pentagonal <org@pentagonal.org>
 */
namespace {

    /**
     * Class KikKukAutoload
     */
    class KikKukAutoload
    {
        /**
         * @var string
         */
        protected $nameSpace = '';

        /**
         * @var string
         */
        protected $directory;

        /**
         * Load Class Name
         *
         * @param string $className
         * @throws \InvalidArgumentException
         */
        public function load($className)
        {
            if (!is_string($className)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Invalid argument 1, Class Name must be as a string %s given',
                        gettype($className)
                    ),
                    E_ERROR
                );
            }
            $className = ltrim($className, '\\');
            // stop here if not match
            if (stripos($className, $this->nameSpace) !== 0) {
                return;
            }

            $className = preg_replace('/(\\\|\/)+/', '/', substr($className, strlen($this->nameSpace)));

            if (file_exists($this->directory . '/' . $className . '.php')) {
                /** @noinspection PhpIncludeInspection */
                require_once $this->directory . '/' . $className . '.php';

                return;
            }
        }

        /**
         * Create Object instance
         *
         * @param $nameSpace
         * @param $directory
         * @return KikKukAutoload
         * @throws \InvalidArgumentException
         */
        public static function create($nameSpace, $directory)
        {
            if ($nameSpace && !is_string($nameSpace)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Autoload Name Space must be as a string %s given',
                        gettype($nameSpace)
                    ),
                    E_USER_ERROR
                );
            }
            if (!is_string($directory)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Autoload Source Directory must be as a string %s given',
                        gettype($directory)
                    ),
                    E_USER_ERROR
                );
            }
            if (trim($directory) == '') {
                throw new \InvalidArgumentException(
                    'Autoload Source Directory could not be empty',
                    E_USER_ERROR
                );
            }
            $autoload = new static();
            $autoload->nameSpace = !$nameSpace ? '' : trim($nameSpace);
            if ($autoload->nameSpace != '') {
                $autoload->nameSpace = preg_replace('/(\\\|\/)+/', '\\', $autoload->nameSpace);
                $autoload->nameSpace = trim($autoload->nameSpace, '\\') . '\\';
            }

            $autoload->directory = rtrim(preg_replace('/(\\\|\/)+/', '/', $directory), '/');

            return $autoload;
        }

        /**
         * Register Autoload
         *
         * @param string $nameSpace
         * @param string $directory
         * @return bool
         */
        public static function register($nameSpace, $directory)
        {
            return \spl_autoload_register(
                call_user_func_array(__CLASS__ . '::create', func_get_args())
            );
        }

        /**
         * Multiple registers
         *
         * @param array $details
         */
        public static function registers(array $details)
        {
            foreach ($details as $nameSpace => $directory) {
                self::register($nameSpace, $directory);
            }
        }

        /**
         * @invokable
         */
        public function __invoke()
        {
            call_user_func_array([$this, 'load'], func_get_args());
        }
    }
}
