<?php
/**
 * Constant Collection Normalization
 *
 * @author nawa <nawa@yahoo.com>
 */

/* -----------------------------------
 * DEBUGGING & DEV MODE
 * --------------------------------- */
if (! defined('KIK_KUK_DEV_MODE')) {
    # Debug (boolean)
    define('KIK_KUK_DEV_MODE', false);
}

if (! defined('KIK_KUK_DEBUG_LOG')) {
    # Debug (string|int)
    define('KIK_KUK_DEBUG_LOG', false);
}

/* -----------------------------------
 * Session
 * --------------------------------- */
if (!defined('KIK_KUK_SESSION_NAME')) {
    define('KIK_KUK_SESSION_NAME', 'KIK_KUK_SSID');
}
if (!defined('KIK_KUK_SESSION_PATH')) {
    define('KIK_KUK_SESSION_PATH', '/');
}

/* -----------------------------------
 * Security
 * --------------------------------- */
if (!defined('KIK_KUK_HASH')) {
    define('KIK_KUK_HASH', sha1(__DIR__ . $_SERVER['SERVER_ADDR']));
}

/* -----------------------------------
 * Directory Core
 * --------------------------------- */

if (!defined('KIK_KUK_COMPONENT_DIR')) {
    define('KIK_KUK_COMPONENT_DIR', __DIR__ . '/Component');
}
if (!defined('KIK_KUK_CACHE_DIR')) {
    define('KIK_KUK_CACHE_DIR', __DIR__ . '/Cache');
}
if (!defined('KIK_KUK_SESSION_DIR')) {
    define('KIK_KUK_SESSION_DIR', __DIR__ . '/Session');
}
if (!defined('KIK_KUK_DATA_DIR')) {
    define('KIK_KUK_DATA_DIR', __DIR__ . '/Data');
}
if (!defined('KIK_KUK_LOG_DIR')) {
    define('KIK_KUK_LOG_DIR', __DIR__ . '/Logs');
}
if (!defined('KIK_KUK_VENDOR_DIR')) {
    define('KIK_KUK_VENDOR_DIR', dirname(__DIR__) . '/vendor');
}

// maybe Web Dir must me not defined
if (!defined('KIK_KUK_WEB_DIR')) {
    define(
        'KIK_KUK_WEB_DIR',
        dirname($_SERVER['SCRIPT_FILENAME'])
    );
}

/* -----------------------------------
 * Directory Templates & Upload
 * --------------------------------- */
if (!defined('KIK_KUK_TEMPLATE_DIR')) {
    define('KIK_KUK_TEMPLATE_DIR', dirname(__DIR__) . '/views');
}
if (!defined('KIK_KUK_UPLOAD_DIR')) {
    define('KIK_KUK_UPLOAD_DIR', KIK_KUK_WEB_DIR . '/uploads');
}

/* -----------------------------------
 * Router Cache File
 * --------------------------------- */
if (! defined('KIK_KUK_ROUTER_CACHE_FILE')) {
    define('KIK_KUK_ROUTER_CACHE_FILE', false);
}

/* -----------------------------------
 * Database detail
 * --------------------------------- */
if (!defined('KIK_KUK_DB_PREFIX')) {
    define('KIK_KUK_DB_PREFIX', '');
}
if (!defined('KIK_KUK_DB_USER')) {
    define('KIK_KUK_DB_USER', '');
}
if (!defined('KIK_KUK_DB_PASS')) {
    define('KIK_KUK_DB_PASS', '');
}
if (!defined('KIK_KUK_DB_NAME')) {
    define('KIK_KUK_DB_NAME', '');
}
if (!defined('KIK_KUK_DB_HOST')) {
    define('KIK_KUK_DB_HOST', 'localhost');
}
if (!defined('KIK_KUK_DB_DRIVER')) {
    define('KIK_KUK_DB_DRIVER', 'sqlite');
}
if (!defined('KIK_KUK_DB_FILE')) {
    define('KIK_KUK_DB_FILE', KIK_KUK_DATA_DIR .'/Database.sqlite');
}

/**
 * WIth DB Schema
 */
if (!defined('KIK_KUK_DB_WITH_EVENT')) {
    define('KIK_KUK_DB_WITH_EVENT', false);
}
if (!defined('KIK_KUK_DB_WITH_LICENSE')) {
    define('KIK_KUK_DB_WITH_LICENSE', false);
}
