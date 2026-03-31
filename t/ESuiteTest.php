<?php

namespace ESuite;

use Empathy\MVC\Util\Testing\Util\DB;
use Empathy\MVC\Util\Testing\Util\Config;
use Empathy\MVC\EntityPopulator;
use Empathy\MVC\EntityManager;
use Nelmio\Alice\Fixtures\Loader;
use Empathy\MVC\Util\Testing\EmpathyApp;
use Empathy\MVC\DI;
use Empathy\MVC\Controller;

abstract class ESuiteTest extends \PHPUnit\Framework\TestCase
{
   
    protected function setUp(): void
    {
        $e = new EmpathyApp();
        $b = $e->makeFakeBootstrap(\Empathy\MVC\Plugin\ELibs::TESTING_LIB);
        DI::getContainer()->set('Controller', new Controller($b));
    }

    public static function setUpBeforeClass(): void
    {
        //
    }

    public static function tearDownAfterClass(): void
    {
        //
    }

    protected function loadFixtures($reset, $file)
    {
        $populator = new EntityPopulator();
        DB::reset($reset, true);
        $objectManager = new EntityManager();

        $path = Config::get('base') . '/' . $file;
        $loader = new Loader();
        $loader->addPopulator($populator);

        /** @var list<object> $objects */
        $objects = array_values($loader->load($path));
        $objectManager->persist($objects);
    }
}
