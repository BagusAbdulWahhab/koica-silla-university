<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\EmissionStandard;
use App\Models\EmissionFactor;
use App\Models\EmissionRecord;
use App\Models\EmissionRecordDetail;

class EmissionRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Safe Reseeding: Clean up existing calculation data to prevent duplicates
        EmissionRecordDetail::query()->delete();
        EmissionRecord::query()->delete();

        // Ensure we use GHG Protocol (Standard ID = 1) for the main historical records
        $standardId = 1;

        // Fetch factors for GHG Protocol to compute emissions programmatically
        $factors = EmissionFactor::where('emission_standard_id', $standardId)
            ->get()
            ->groupBy('scope');

        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Monthly activity inputs
        $monthlyInputs = [
            'January' => [
                'scope1' => ['Diesel' => 150.0, 'Petrol' => 100.0, 'LPG' => 50.0],
                'scope2' => ['Purchased Electricity' => 1600.0, 'Purchased Steam' => 500.0],
                'scope3' => ['Business Travel' => 1200.0, 'Employee Commuting' => 800.0, 'Waste to Landfill' => 100.0, 'Water Supply' => 150.0],
            ],
            'February' => [
                'scope1' => ['Diesel' => 160.0, 'Petrol' => 110.0, 'LPG' => 52.0],
                'scope2' => ['Purchased Electricity' => 1750.0, 'Purchased Steam' => 520.0],
                'scope3' => ['Business Travel' => 1300.0, 'Employee Commuting' => 850.0, 'Waste to Landfill' => 110.0, 'Water Supply' => 160.0],
            ],
            'March' => [
                'scope1' => ['Diesel' => 155.0, 'Petrol' => 105.0, 'LPG' => 50.0],
                'scope2' => ['Purchased Electricity' => 1650.0, 'Purchased Steam' => 510.0],
                'scope3' => ['Business Travel' => 1100.0, 'Employee Commuting' => 800.0, 'Waste to Landfill' => 105.0, 'Water Supply' => 155.0],
            ],
            'April' => [
                'scope1' => ['Diesel' => 170.0, 'Petrol' => 115.0, 'LPG' => 55.0],
                'scope2' => ['Purchased Electricity' => 1800.0, 'Purchased Steam' => 530.0],
                'scope3' => ['Business Travel' => 1350.0, 'Employee Commuting' => 880.0, 'Waste to Landfill' => 115.0, 'Water Supply' => 165.0],
            ],
            'May' => [
                'scope1' => ['Diesel' => 175.0, 'Petrol' => 120.0, 'LPG' => 58.0],
                'scope2' => ['Purchased Electricity' => 1850.0, 'Purchased Steam' => 550.0],
                'scope3' => ['Business Travel' => 1400.0, 'Employee Commuting' => 900.0, 'Waste to Landfill' => 120.0, 'Water Supply' => 170.0],
            ],
            'June' => [
                'scope1' => ['Diesel' => 180.0, 'Petrol' => 125.0, 'LPG' => 60.0],
                'scope2' => ['Purchased Electricity' => 1900.0, 'Purchased Steam' => 560.0],
                'scope3' => ['Business Travel' => 1450.0, 'Employee Commuting' => 920.0, 'Waste to Landfill' => 125.0, 'Water Supply' => 175.0],
            ],
            // July has a major production spike (Diesel and Electricity spiked)
            'July' => [
                'scope1' => ['Diesel' => 450.0, 'Petrol' => 200.0, 'LPG' => 100.0],
                'scope2' => ['Purchased Electricity' => 3200.0, 'Purchased Steam' => 1000.0],
                'scope3' => ['Business Travel' => 3000.0, 'Employee Commuting' => 1500.0, 'Waste to Landfill' => 300.0, 'Water Supply' => 300.0],
            ],
            'August' => [
                'scope1' => ['Diesel' => 185.0, 'Petrol' => 130.0, 'LPG' => 62.0],
                'scope2' => ['Purchased Electricity' => 1950.0, 'Purchased Steam' => 580.0],
                'scope3' => ['Business Travel' => 1500.0, 'Employee Commuting' => 950.0, 'Waste to Landfill' => 130.0, 'Water Supply' => 180.0],
            ],
            'September' => [
                'scope1' => ['Diesel' => 175.0, 'Petrol' => 120.0, 'LPG' => 58.0],
                'scope2' => ['Purchased Electricity' => 1850.0, 'Purchased Steam' => 550.0],
                'scope3' => ['Business Travel' => 1400.0, 'Employee Commuting' => 900.0, 'Waste to Landfill' => 120.0, 'Water Supply' => 170.0],
            ],
            // October has a high Scope 2 period where Scope 2 contributes > 85% of total
            'October' => [
                'scope1' => ['Diesel' => 40.0, 'Petrol' => 30.0, 'LPG' => 10.0],
                'scope2' => ['Purchased Electricity' => 3200.0, 'Purchased Steam' => 300.0],
                'scope3' => ['Business Travel' => 800.0, 'Employee Commuting' => 800.0, 'Waste to Landfill' => 50.0, 'Water Supply' => 100.0],
            ],
            'November' => [
                'scope1' => ['Diesel' => 200.0, 'Petrol' => 140.0, 'LPG' => 70.0],
                'scope2' => ['Purchased Electricity' => 2100.0, 'Purchased Steam' => 650.0],
                'scope3' => ['Business Travel' => 1600.0, 'Employee Commuting' => 1000.0, 'Waste to Landfill' => 150.0, 'Water Supply' => 200.0],
            ],
            'December' => [
                'scope1' => ['Diesel' => 210.0, 'Petrol' => 150.0, 'LPG' => 75.0],
                'scope2' => ['Purchased Electricity' => 2200.0, 'Purchased Steam' => 700.0],
                'scope3' => ['Business Travel' => 1800.0, 'Employee Commuting' => 1100.0, 'Waste to Landfill' => 160.0, 'Water Supply' => 220.0],
            ],
        ];

        foreach ($months as $month) {
            $reportingPeriod = "{$month} 2024";
            $inputs = $monthlyInputs[$month];

            $scope1Total = 0.0;
            $scope2Total = 0.0;
            $scope3Total = 0.0;
            $detailsToSave = [];

            // Scope 1 details calculation
            if (isset($factors[1])) {
                foreach ($factors[1] as $factor) {
                    $category = $factor->category_name;
                    if (isset($inputs['scope1'][$category])) {
                        $val = $inputs['scope1'][$category];
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

            // Scope 2 details calculation
            if (isset($factors[2])) {
                foreach ($factors[2] as $factor) {
                    $category = $factor->category_name;
                    if (isset($inputs['scope2'][$category])) {
                        $val = $inputs['scope2'][$category];
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

            // Scope 3 details calculation
            if (isset($factors[3])) {
                foreach ($factors[3] as $factor) {
                    $category = $factor->category_name;
                    if (isset($inputs['scope3'][$category])) {
                        $val = $inputs['scope3'][$category];
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
        }
    }
}
