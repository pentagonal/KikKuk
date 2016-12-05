<?php
namespace {

    require_once __DIR__ . '/Config.php';   # require Config SlimOverride constant
    require_once __DIR__ . '/Constant.php'; # Require Sanitize Constant
    require_once __DIR__ . '/KikKukAutoload.php';
    if (!defined('KIK_KUK_VENDOR_DIR') || !is_file(KIK_KUK_VENDOR_DIR . '/autoload.php')) {
        return;
    }
    require_once KIK_KUK_VENDOR_DIR . '/autoload.php'; # require autoload composer
    require_once __DIR__ . '/KikKuk.php';
    KikKukAutoload::registers(
        [
            'KikKuk\\Controller' => __DIR__ . '/Controller',
            'KikKuk\\Model'      => __DIR__ . '/Model',
            'KikKuk'             => __DIR__ . '/Classes'
        ]
    );
}
