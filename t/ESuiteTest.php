<?php

namespace ESuite;

use Empathy\MVC\EntityManager;
use Empathy\MVC\EntityPopulator;
use Nelmio\Alice\Fixtures\Loader;


abstract class ESuiteTest extends \PHPUnit_Framework_TestCase
{
   
    protected function setUp()
    {
        \ESuite\Util\DB::loadDefDBCreds();
    }

    public static function setUpBeforeClass()
    {
        //
    }

    public static function tearDownAfterClass()
    {
        //
    }

    protected function loadFixtures($reset, $file)
    {
        $populator = new EntityPopulator();
        \ESuite\Util\DB::reset($reset);
        $objectManager = new EntityManager();

        $file = \ESuite\Util\Config::get('base').$file;
        $loader = new Loader();
        $loader->addPopulator($populator);

        $objects = $loader->load($file);
        $objectManager->persist($objects);
    }

}
