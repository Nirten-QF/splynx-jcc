<?php

// Check to enable development mode
if (file_exists(__DIR__ . '/../config/dev.php')) {
    require(__DIR__ . '/../config/dev.php');
}

// Load Splynx Base Add-on vendor
require(__DIR__ . '/../../splynx-addon-base/vendor/autoload.php');
require(__DIR__ . '/../../splynx-addon-base/vendor/yiisoft/yii2/Yii.php');

// Load add-on vendor
require(__DIR__ . '/../vendor/autoload.php');

// Check splynx-base-addon version
if (!file_exists('/var/www/splynx/addons/splynx-addon-base/vendor/splynx/splynx-addon-helpers/helpers/ConfigHelper.php')) {
    exit("Error: Your Add-On Base is very old!\nPlease update your Add-On Base\n");
}

$baseDir = dirname(__DIR__);
$configPath = $baseDir . '/config/web.php';

(new splynx\base\WebApplication($baseDir, $configPath))->run();
