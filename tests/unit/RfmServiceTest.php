<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\RfmService;

/**
 * @internal
 */
final class RfmServiceTest extends CIUnitTestCase
{
    protected $refresh = false;
    protected $migrate = false;
    protected RfmService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RfmService();
    }

    public function testHitungRFMPopulatesTable(): void
    {
        $result = $this->service->hitungRFM();
        $this->assertSame('ok', $result['status']);
        $this->assertGreaterThan(0, $result['count']);

        $db = \Config\Database::connect();
        $rows = $db->table('tb_rfm')->get()->getResultArray();
        $this->assertGreaterThan(0, count($rows));
    }

    public function testRFMValuesArePopulated(): void
    {
        $this->service->hitungRFM();

        $db = \Config\Database::connect();
        $row = $db->table('tb_rfm')->get()->getFirstRow();

        $this->assertGreaterThanOrEqual(0, $row->frequency);
        $this->assertGreaterThanOrEqual(0, $row->monetary);
    }

    public function testExportToCSV(): void
    {
        $this->service->hitungRFM();
        $path = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'test_rfm.csv';
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $this->assertTrue($this->service->exportToCSV($path));
        $this->assertFileExists($path);
        $content = file_get_contents($path);
        $this->assertStringContainsString('id_pelanggan,recency,frequency,monetary', $content);
        @unlink($path);
    }
}
