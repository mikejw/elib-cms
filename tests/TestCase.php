<?php

declare(strict_types=1);

namespace Tests;

use Empathy\MVC\Bootstrap;
use Empathy\MVC\Controller;
use Empathy\MVC\DBC;
use Empathy\MVC\DI;
use Empathy\MVC\EntityManager;
use Empathy\MVC\EntityPopulator;
use Empathy\MVC\Plugin\ELibs;
use Empathy\MVC\Util\Testing\EmpathyApp;
use Empathy\MVC\Util\Testing\Util\Config;
use Empathy\MVC\Util\Testing\Util\DB;
use Nelmio\Alice\Fixtures\Loader;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Project test case: wires Empathy helpers for Pest (and optional PHPUnit tests in ./tests).
 *
 * Properties are public so assignments in Pest beforeEach are obvious to static analysis
 * when combined with universalObjectCratesClasses for PHPUnit\Framework\TestCase.
 */
abstract class TestCase extends BaseTestCase
{
    public EmpathyApp $empathy;

    public ?Bootstrap $bootstrap = null;

    public ?Controller $controller = null;

    public ?DBC $dbc = null;

    /** @var Bootstrap|null Used by URITest */
    public ?Bootstrap $boot = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->empathy = new EmpathyApp();
        $this->bootstrap = $this->empathy->makeFakeBootstrap(ELibs::TESTING_LIB);
        $this->controller = new Controller($this->bootstrap);
        DI::getContainer()->set('Controller', $this->controller);
    }

    public function loadFixtures(string $reset, string $file): void
    {
        $populator = new EntityPopulator();
        DB::reset($reset, true);
        $objectManager = new EntityManager();

        $path = Config::get('base').'/'.ltrim($file, '/');
        $loader = new Loader();
        $loader->addPopulator($populator);

        /** @var list<object> $objects */
        $objects = array_values($loader->load($path));
        $objectManager->persist($objects);
    }
}
