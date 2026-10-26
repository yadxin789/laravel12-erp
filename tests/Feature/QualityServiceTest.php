<?php

namespace Tests\Feature;

use App\Enums\QualityInspectionStatus;
use App\Models\MasterData\Item;
use App\Models\User;
use App\Services\QualityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QualityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $user;
    protected $item;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(QualityService::class);
        $this->user = User::factory()->create();
        
        $this->item = Item::factory()->create([
            'code' => 'ITEM001',
            'name' => 'Test Item',
        ]);
    }

    public function test_create_iqc_inspection()
    {
        $inspection = $this->service->createInspection([
            'inspection_type' => 'iqc',
            'item_id' => $this->item->id,
            'batch_number' => 'B20240101',
            'inspected_quantity' => 100,
            'reference_no' => 'PO001',
        ]);

        $this->assertNotNull($inspection);
        $this->assertEquals('iqc', $inspection->inspection_type->value);
        $this->assertEquals(QualityInspectionStatus::Pending, $inspection->status);
    }

    public function test_create_ipqc_inspection()
    {
        $inspection = $this->service->createInspection([
            'inspection_type' => 'ipqc',
            'item_id' => $this->item->id,
            'batch_number' => 'B20240101',
            'inspected_quantity' => 50,
            'reference_no' => 'WO001',
        ]);

        $this->assertNotNull($inspection);
        $this->assertEquals('ipqc', $inspection->inspection_type->value);
    }

    public function test_create_fqc_inspection()
    {
        $inspection = $this->service->createInspection([
            'inspection_type' => 'fqc',
            'item_id' => $this->item->id,
            'batch_number' => 'B20240101',
            'inspected_quantity' => 100,
            'reference_no' => 'WO001',
        ]);

        $this->assertNotNull($inspection);
        $this->assertEquals('fqc', $inspection->inspection_type->value);
    }

    public function test_create_oqc_inspection()
    {
        $inspection = $this->service->createInspection([
            'inspection_type' => 'oqc',
            'item_id' => $this->item->id,
            'batch_number' => 'B20240101',
            'inspected_quantity' => 100,
            'reference_no' => 'SO001',
        ]);

        $this->assertNotNull($inspection);
        $this->assertEquals('oqc', $inspection->inspection_type->value);
    }

    public function test_complete_inspection_qualified()
    {
        $inspection = $this->service->createInspection([
            'inspection_type' => 'iqc',
            'item_id' => $this->item->id,
            'batch_number' => 'B20240101',
            'inspected_quantity' => 100,
            'reference_no' => 'PO001',
        ]);

        $results = [
            'qualified_quantity' => 95,
            'unqualified_quantity' => 5,
            'defect_types' => ['外观缺陷'],
            'notes' => '5件外观有划痕',
        ];

        $updatedInspection = $this->service->completeInspection($inspection, $results);

        $this->assertEquals(QualityInspectionStatus::Completed, $updatedInspection->status);
        $this->assertEquals(95, $updatedInspection->qualified_size);
        $this->assertEquals(5, $updatedInspection->rejected_size);
    }

    public function test_complete_inspection_unqualified()
    {
        $inspection = $this->service->createInspection([
            'inspection_type' => 'iqc',
            'item_id' => $this->item->id,
            'batch_number' => 'B20240101',
            'inspected_quantity' => 100,
            'reference_no' => 'PO001',
        ]);

        $results = [
            'qualified_quantity' => 50,
            'unqualified_quantity' => 50,
            'defect_types' => ['功能缺陷'],
            'notes' => '合格率低于标准要求',
        ];

        $updatedInspection = $this->service->completeInspection($inspection, $results);

        $this->assertEquals(QualityInspectionStatus::Completed, $updatedInspection->status);
    }

    public function test_generate_inspection_report()
    {
        $inspection = $this->service->createInspection([
            'inspection_type' => 'iqc',
            'item_id' => $this->item->id,
            'batch_number' => 'B20240101',
            'inspected_quantity' => 100,
            'reference_no' => 'PO001',
        ]);

        $results = [
            'qualified_quantity' => 95,
            'unqualified_quantity' => 5,
            'defect_types' => ['外观缺陷'],
            'notes' => '5件外观有划痕',
        ];

        $inspection = $this->service->completeInspection($inspection, $results);
        $report = $this->service->generateInspectionReport($inspection);

        $this->assertNotNull($report);
        $this->assertEquals(95, $report['qualified_quantity']);
        $this->assertEquals(5, $report['unqualified_quantity']);
        $this->assertEquals(95.0, $report['pass_rate']);
    }
}
