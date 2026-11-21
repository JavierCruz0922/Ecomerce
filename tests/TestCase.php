<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use PDO;

abstract class TestCase extends BaseTestCase
{
    protected function migrateIsolatedSqlite(): void
    {
        $this->assertTrue(app()->environment('testing'));

        $connection = DB::connection();
        $this->assertSame('sqlite', $connection->getDriverName());
        $this->assertSame(':memory:', $connection->getDatabaseName());
        $this->assertEmpty($connection->getConfig('url'));
        $this->assertSame('sqlite', $connection->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME));
        $this->assertSame('', $connection->selectOne('pragma database_list')->file);
        $this->assertSame(1, (int) $connection->selectOne('pragma foreign_keys')->foreign_keys);

        $this->artisan('migrate', ['--no-interaction' => true])->assertSuccessful();
    }
}
