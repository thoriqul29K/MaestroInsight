<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\PelangganModel;

/**
 * @internal
 */
final class PelangganModelTest extends CIUnitTestCase
{
    protected $refresh = false;
    protected $migrate = false;
    protected PelangganModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new PelangganModel();
    }

    public function testCanListAllPelanggan(): void
    {
        $rows = $this->model->orderBy('nama_pelanggan', 'ASC')->findAll();
        $this->assertIsArray($rows);
        $this->assertGreaterThanOrEqual(10, count($rows));
    }

    public function testCountBySegmentReturnsArrayOfFiveSegments(): void
    {
        $counts = $this->model->countBySegment();
        $this->assertArrayHasKey('loyal', $counts);
        $this->assertArrayHasKey('potential', $counts);
        $this->assertArrayHasKey('budget', $counts);
        $this->assertArrayHasKey('seasonal', $counts);
        $this->assertArrayHasKey('at_risk', $counts);
    }

    public function testCanInsertAndDeletePelanggan(): void
    {
        $newId = $this->model->insert([
            'nama_pelanggan' => 'Test User',
            'email'          => 'test.user@example.com',
            'telepon'        => '081234567890',
            'alamat'         => 'Test Address',
        ]);
        $this->assertIsNumeric($newId);
        $this->assertTrue($this->model->delete($newId));
    }
}
