<?php


include(realpath(dirname(__FILE__).'/../vendor/autoload.php'));

if (!function_exists('loadClass')) {
    function loadClass($class)
    {
        if (strpos($class, 'ESuite') === 0) {
            $base = dirname(realpath(__FILE__));
            
            $class = str_replace('ESuite\\', '', $class);
            $class = str_replace('\\', '/', $class);
            $class_file = $base."/$class.php";
            
            if (!@include($class_file)) {
                echo '[[['.$class_file.']]]';
                throw new \Exception('Could not include class '.$class_file);
            }
        }
    }
    spl_autoload_register('loadClass');
}

Empathy\MVC\Util\Testing\Util\Config::init(realpath(dirname(__FILE__)));
if (Empathy\MVC\Util\Testing\Util\Config::get('set_test_mode')) {
    define('MVC_TEST_MODE', Empathy\MVC\Util\Testing\Util\Config::get('set_test_mode'));
}
if (Empathy\MVC\Util\Testing\Util\Config::get('set_test_mode_output')) {
    define('MVC_TEST_OUTPUT_ON', Empathy\MVC\Util\Testing\Util\Config::get('set_test_mode_output'));
}
