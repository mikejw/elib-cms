<?php

declare(strict_types=1);
use Empathy\MVC\Util\Testing\Util\Config;

require dirname(__DIR__).'/vendor/autoload.php';

Config::init(realpath(dirname(__FILE__)));
if (Config::get('set_test_mode')) {
    define('MVC_TEST_MODE', Config::get('set_test_mode'));
}
if (Config::get('set_test_mode_output')) {
    define('MVC_TEST_OUTPUT_ON', Config::get('set_test_mode_output'));
}
