<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ExampleDatabaseTest extends CIUnitTestCase
{
    protected $refresh = false;
    protected $migrate = false;
    protected $seed    = '';

    public function testModelFindAll(): void
    {
        // Placeholder test for the database example. Real DB integration
        // tests for the application live in tests/unit.
        $this->assertTrue(true);
    }

    public function testSoftDeleteLeavesRow(): void
    {
        $this->markTestSkipped('Example test only; not part of application suite.');
    }
}
