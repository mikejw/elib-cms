<?php

declare(strict_types=1);

use Empathy\MVC\EntityManager;
use Empathy\MVC\EntityPopulator;
use Empathy\MVC\Util\Testing\Util\Config as TestingConfig;
use Empathy\MVC\Util\Testing\Util\DB;
use Nelmio\Alice\Fixtures\Loader;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)->in('Feature');
pest()->extend(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function loadFixtures(string $reset, string $file): void
{
    $populator = new EntityPopulator();
    DB::reset($reset, true);
    $objectManager = new EntityManager();

    $path = TestingConfig::get('base') . '/' . ltrim($file, '/');
    $loader = new Loader();
    $loader->addPopulator($populator);

    /** @var list<object> $objects */
    $objects = array_values($loader->load($path));
    $objectManager->persist($objects);
}
