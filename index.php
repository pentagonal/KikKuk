<?php
namespace {

    /* --------------------------------------------------*\
     * Determine custom config file that contains
     * Config constant @see applications/Loader.php
     * for existing config file
     * @note* must be php file!!
    \* ------------------------------------------------- */
    // define('KIK_KUK_CONFIG_FILE', 'file/to/config.php');

    require __DIR__ . '/applications/Loader.php';
    return KikKuk::run();
}
