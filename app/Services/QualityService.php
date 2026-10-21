<?php

namespace App\Services;

use App\Enums\InventoryQualityStatus;
use App\Enums\InspectionType;
use App\Enums\QualityResult;
use App\Models\Inventory\InventoryBalance;
use App\Models\Quality\QualityInspection;
use App\Models\Quality\QualityInspectionLine;
use App\Models\System\NumberSequence;
use Illuminate\Support\Facades\DB;

class QualityService
{
    public function createInspection(array $data)
    {
        return DB::transaction(function () use ($data) {
            $inspectionNo = $this->generateInspectionNo($data['inspection_type']);

            $inspection = QualityInspection::create([
                'inspection_no' => $inspectionNo,
                'inspection_type' => $data['inspection_type'],
                'source_document_id' => $data['source_document_id'] ?? null,
                'source_document_type' => $data['source_document_type'] ?? null,
                'item_id' => $data['item_id'] ?? null,
                'batch_number' => $data['batch_number'] ?? null,
                'inspection_date' => $data['inspection_date'] ?? now(),
                'sample_size' => $data['sample_size'] ?? 0,
                'inspected_size' => 0,
                'qualified_size' => 0,
                'rejected_size' => 0,
                'result' => QualityResult::Pass,
                'status' => 'pending',
                'remark' => $data['remark'] ?? null,
                'created_by' => auth()->id(),
            ]);

            if (!empty($data['lines'])) {
                $this->createInspectionLines($inspection, $data['lines']);
            }

            return $inspection->load('lines');
        });
    }

    public function completeInspection(QualityInspection $inspection, array $results)
    {
        return DB::transaction(function () use ($inspection, $results) {
            $qualified = $results['qualified_quantity'] ?? 0;
            $rejected = $results['unqualified_quantity'] ?? 0;

            $inspection->inspected_size = $qualified + $rejected;
            $inspection->qualified_size = $qualified;
            $inspection->rejected_size = $rejected;

            if ($rejected === 0) {
                $inspection->result = QualityResult::Pass;
                $this->updateInventoryStatus($inspection, InventoryQualityStatus::Qualified);
            } else {
                $inspection->result = QualityResult::Fail;
                $this->updateInventoryStatus($inspection, InventoryQualityStatus::Rejected);
            }

            $inspection->status = 'completed';
            $inspection->approved_by = auth()->id();
            $inspection->approved_at = now();
            $inspection->save();

            return $inspection;
        });
    }

    protected function updateInventoryStatus(QualityInspection $inspection, $qualityStatus)
    {
        if ($inspection->batch_number && $inspection->item_id) {
            $balance = InventoryBalance::where('item_id', $inspection->item_id)
                ->where('batch_number', $inspection->batch_number)
                ->where('quality_status', InventoryQualityStatus::Pending)
                ->first();

            if ($balance) {
                $balance->quality_status = $qualityStatus;
                $balance->save();
            }
        }
    }

    protected function createInspectionLines(QualityInspection $inspection, array $lines)
    {
        foreach ($lines as $index => $lineData) {
            QualityInspectionLine::create([
                'quality_inspection_id' => $inspection->id,
                'line_number' => $index + 1,
                'inspection_item' => $lineData['inspection_item'],
                'specification' => $lineData['specification'] ?? null,
                'result' => null,
                'judgment' => 'pending',
                'remark' => $lineData['remark'] ?? null,
            ]);
        }
    }

    public function generateInspectionReport(QualityInspection $inspection)
    {
        return [
            'inspection_no' => $inspection->inspection_no,
            'inspection_type' => $inspection->inspection_type->value,
            'inspection_date' => $inspection->inspection_date,
            'item_id' => $inspection->item_id,
            'batch_number' => $inspection->batch_number,
            'inspected_quantity' => $inspection->inspected_size,
            'qualified_quantity' => $inspection->qualified_size,
            'unqualified_quantity' => $inspection->rejected_size,
            'pass_rate' => $inspection->inspected_size > 0 
                ? round(($inspection->qualified_size / $inspection->inspected_size) * 100, 1) 
                : 0,
            'result' => $inspection->result->value,
            'status' => $inspection->status,
            'created_by' => $inspection->created_by,
            'approved_by' => $inspection->approved_by,
            'approved_at' => $inspection->approved_at,
        ];
    }

    protected function generateInspectionNo($inspectionType)
    {
        $prefix = match ($inspectionType) {
            InspectionType::IQC->value => 'IQC',
            InspectionType::IPQC->value => 'IPQC',
            InspectionType::FQC->value => 'FQC',
            InspectionType::OQC->value => 'OQC',
            default => 'QC',
        };

        $sequence = NumberSequence::firstOrCreate(
            ['code' => $prefix],
            ['name' => "{$prefix}检验单", 'prefix' => $prefix, 'number_length' => 6, 'year_reset' => true]
        );

        $year = now()->format('Y');
        $number = str_pad($sequence->current_number, $sequence->number_length, '0', STR_PAD_LEFT);
        $inspectionNo = "{$sequence->prefix}-{$year}-{$number}";

        $sequence->current_number++;
        $sequence->save();

        return $inspectionNo;
    }
}
