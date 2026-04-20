<?php

declare(strict_types=1);

namespace App\Tests;

use App\Tests\Support\LoadsAppFixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base class for browser tests that need the same reference data as {@see App\DataFixtures\AppFixtures}.
 *
 * - One {@see \Symfony\Bundle\FrameworkBundle\Test\WebTestCase::createClient()} call per test (in {@see setUp})
 * - Then a consistent fixture load (purge + load), compatible with DAMA transactions
 */
abstract class AbstractWebFixtureTestCase extends WebTestCase
{
    use LoadsAppFixturesTrait;

    protected function setUp(): void
    {
        parent::setUp();

        static::createClient();
        static::loadAppFixtures();
    }
}
