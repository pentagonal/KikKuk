<?php
/**
 * Config Override
 */

/* -----------------------------------
 * DEBUGGING & DEV MODE
 * --------------------------------- */
define('KIK_KUK_DEV_MODE', true);    # Development Mode
define('KIK_KUK_DEBUG_LOG', true);   # log level / false to disable

/* -----------------------------------
 * Session
 * --------------------------------- */
define('KIK_KUK_SESSION_NAME', 'KIK_KUK_SSID'); # session name
define('KIK_KUK_SESSION_PATH', '/');            # Session path

/* -----------------------------------
 * Security
 * --------------------------------- */
define('KIK_KUK_HASH', sha1(__DIR__ . $_SERVER['SERVER_ADDR'])); # security hash

/* -----------------------------------
 * Directory
 * --------------------------------- */
define('KIK_KUK_COMPONENT_DIR', __DIR__ .'/Component');     # component directory
define('KIK_KUK_CACHE_DIR', __DIR__ . '/Cache');            # cache directory
define('KIK_KUK_SESSION_DIR', __DIR__ . '/Session');        # session directory
define('KIK_KUK_DATA_DIR', __DIR__ . '/Data');              # data directory
define('KIK_KUK_LOG_DIR', __DIR__ . '/Logs');               # log directory
define('KIK_KUK_VENDOR_DIR', dirname(__DIR__) . '/vendor'); # vendor directory

/* -----------------------------------
 * Directory Templates & Upload
 * --------------------------------- */
define('KIK_KUK_TEMPLATE_DIR', dirname(__DIR__) . '/views'); # templates directory
define('KIK_KUK_UPLOAD_DIR', dirname(__DIR__) . '/uploads'); # uploads directory

/* -----------------------------------
 * Database detail
 * --------------------------------- */
define('KIK_KUK_DB_PREFIX', '');
define('KIK_KUK_DB_USER', '');
define('KIK_KUK_DB_PASS', '');
define('KIK_KUK_DB_NAME', '');
define('KIK_KUK_DB_HOST', 'localhost');
define('KIK_KUK_DB_DRIVER', 'sqlite');
define('KIK_KUK_DB_FILE', KIK_KUK_DATA_DIR .'/Database.sqlite');
