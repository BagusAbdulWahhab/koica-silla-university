<?php

namespace App\Services;

use App\Models\EmissionFactor;
use App\Models\EmissionRecord;
use App\Models\EmissionRecordDetail;
use Illuminate\Support\Facades\DB;
use Exception;

class EmissionCalculationService
{
    /**
     * Calculate carbon footprint and save results to database.
     *
     * @param array $data Input data containing standard, reporting period, and activity values.
     * @return EmissionRecord
     * @throws Exception
     */
    public function calculateAndSave(array $data): EmissionRecord
    {
        return DB::transaction(function () use ($data) {
            $standardId = $data['emission_standard_id'];
            $month = $data['reporting_month'];
            $year = $data['reporting_year'];
            $reportingPeriod = "{$month} {$year}";

            // Fetch all emission factors for the selected standard
            $factors = EmissionFactor::where('emission_standard_id', $standardId)
                ->get()
                ->groupBy('scope');

            $scope1Total = 0.0;
            $scope2Total = 0.0;
            $scope3Total = 0.0;

            // Details to be inserted
            $detailsToSave = [];

            // Process Scope 1
            if (isset($factors[1])) {
                foreach ($factors[1] as $factor) {
                    $category = $factor->category_name;
                    $val = (float) ($data['scope1'][$category] ?? 0);
                    if ($val > 0) {
                        $result = $val * $factor->factor;
                        $scope1Total += $result;

                        $detailsToSave[] = [
                            'scope' => 1,
                            'category_name' => $category,
                            'activity_value' => $val,
                            'unit' => $factor->unit,
                            'emission_factor' => $factor->factor,
                            'emission_result' => $result,
                        ];
                    }
                }
            }

            // Process Scope 2
            if (isset($factors[2])) {
                foreach ($factors[2] as $factor) {
                    $category = $factor->category_name;
                    $val = (float) ($data['scope2'][$category] ?? 0);
                    if ($val > 0) {
                        $result = $val * $factor->factor;
                        $scope2Total += $result;

                        $detailsToSave[] = [
                            'scope' => 2,
                            'category_name' => $category,
                            'activity_value' => $val,
                            'unit' => $factor->unit,
                            'emission_factor' => $factor->factor,
                            'emission_result' => $result,
                        ];
                    }
                }
            }

            // Process Scope 3 (Dynamic / Flexible selection)
            $activeScope3 = $data['scope3_active'] ?? [];
            if (isset($factors[3])) {
                foreach ($factors[3] as $factor) {
                    $category = $factor->category_name;
                    // Only process if it is in the active list of Scope 3 categories
                    if (in_array($category, $activeScope3)) {
                        $val = (float) ($data['scope3'][$category] ?? 0);
                        if ($val > 0) {
                            $result = $val * $factor->factor;
                            $scope3Total += $result;

                            $detailsToSave[] = [
                                'scope' => 3,
                                'category_name' => $category,
                                'activity_value' => $val,
                                'unit' => $factor->unit,
                                'emission_factor' => $factor->factor,
                                'emission_result' => $result,
                            ];
                        }
                    }
                }
            }

            $totalEmission = $scope1Total + $scope2Total + $scope3Total;

            // Create Emission Record
            $record = EmissionRecord::create([
                'reporting_period' => $reportingPeriod,
                'emission_standard_id' => $standardId,
                'scope1_total' => $scope1Total,
                'scope2_total' => $scope2Total,
                'scope3_total' => $scope3Total,
                'total_emission' => $totalEmission,
            ]);

            // Save details
            foreach ($detailsToSave as $detail) {
                $detail['emission_record_id'] = $record->id;
                $detail['created_at'] = now();
                $detail['updated_at'] = now();
                EmissionRecordDetail::create($detail);
            }

            return $record;
        });
    }
}
