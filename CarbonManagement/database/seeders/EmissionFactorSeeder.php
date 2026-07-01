<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmissionFactorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Emission Standards
        $standards = [
            [
                'id' => 1,
                'name' => 'GHG Protocol',
                'description' => 'World Resources Institute (WRI) and World Business Council for Sustainable Development (WBCSD) Greenhouse Gas Protocol Corporate Standard.',
                'reference_source' => 'WRI/WBCSD GHG Protocol',
                'publication_year' => 2004,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'IPCC',
                'description' => 'Intergovernmental Panel on Climate Change Guidelines for National Greenhouse Gas Inventories.',
                'reference_source' => 'IPCC Guidelines',
                'publication_year' => 2006,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Indonesia National',
                'description' => 'Indonesia National emission factors published by the Ministry of Energy and Mineral Resources (ESDM) and Ministry of Environment and Forestry (KLHK).',
                'reference_source' => 'Kementerian ESDM & KLHK',
                'publication_year' => 2023,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('emission_standards')->insert($standards);

        // 2. Seed Emission Factors
        $factors = [
            // === GHG PROTOCOL (Standard ID: 1) ===
            // Scope 1
            ['emission_standard_id' => 1, 'scope' => 1, 'category_name' => 'Diesel', 'unit' => 'Liters', 'factor' => 2.68, 'reference_source' => 'DEFRA / GHG Protocol'],
            ['emission_standard_id' => 1, 'scope' => 1, 'category_name' => 'Petrol', 'unit' => 'Liters', 'factor' => 2.31, 'reference_source' => 'DEFRA / GHG Protocol'],
            ['emission_standard_id' => 1, 'scope' => 1, 'category_name' => 'LPG', 'unit' => 'kg', 'factor' => 2.98, 'reference_source' => 'DEFRA / GHG Protocol'],
            ['emission_standard_id' => 1, 'scope' => 1, 'category_name' => 'Natural Gas', 'unit' => 'm³', 'factor' => 1.88, 'reference_source' => 'DEFRA / GHG Protocol'],
            ['emission_standard_id' => 1, 'scope' => 1, 'category_name' => 'Refrigerant', 'unit' => 'kg', 'factor' => 1430.00, 'reference_source' => 'GHG Protocol HFC-134a'],
            // Scope 2
            ['emission_standard_id' => 1, 'scope' => 2, 'category_name' => 'Purchased Electricity', 'unit' => 'kWh', 'factor' => 0.85, 'reference_source' => 'GHG Protocol Grid Avg'],
            ['emission_standard_id' => 1, 'scope' => 2, 'category_name' => 'Purchased Steam', 'unit' => 'kg', 'factor' => 0.18, 'reference_source' => 'GHG Protocol Steam Factor'],
            // Scope 3
            ['emission_standard_id' => 1, 'scope' => 3, 'category_name' => 'Business Travel', 'unit' => 'km', 'factor' => 0.12, 'reference_source' => 'DEFRA / GHG Protocol'],
            ['emission_standard_id' => 1, 'scope' => 3, 'category_name' => 'Employee Commuting', 'unit' => 'km', 'factor' => 0.09, 'reference_source' => 'DEFRA / GHG Protocol'],
            ['emission_standard_id' => 1, 'scope' => 3, 'category_name' => 'Waste to Landfill', 'unit' => 'kg', 'factor' => 0.45, 'reference_source' => 'DEFRA / GHG Protocol'],
            ['emission_standard_id' => 1, 'scope' => 3, 'category_name' => 'Water Supply', 'unit' => 'm³', 'factor' => 0.34, 'reference_source' => 'DEFRA / GHG Protocol'],
            ['emission_standard_id' => 1, 'scope' => 3, 'category_name' => 'Wastewater Treatment', 'unit' => 'm³', 'factor' => 0.71, 'reference_source' => 'DEFRA / GHG Protocol'],
            ['emission_standard_id' => 1, 'scope' => 3, 'category_name' => 'Purchased Goods', 'unit' => 'kg', 'factor' => 1.20, 'reference_source' => 'GHG Protocol EEIO'],
            ['emission_standard_id' => 1, 'scope' => 3, 'category_name' => 'Transportation & Distribution', 'unit' => 't-km', 'factor' => 0.16, 'reference_source' => 'GHG Protocol Log'],
            ['emission_standard_id' => 1, 'scope' => 3, 'category_name' => 'Capital Goods', 'unit' => 'kg', 'factor' => 2.50, 'reference_source' => 'GHG Protocol EEIO'],

            // === IPCC (Standard ID: 2) ===
            // Scope 1
            ['emission_standard_id' => 2, 'scope' => 1, 'category_name' => 'Diesel', 'unit' => 'Liters', 'factor' => 2.70, 'reference_source' => 'IPCC 2006 Vol 2'],
            ['emission_standard_id' => 2, 'scope' => 1, 'category_name' => 'Petrol', 'unit' => 'Liters', 'factor' => 2.35, 'reference_source' => 'IPCC 2006 Vol 2'],
            ['emission_standard_id' => 2, 'scope' => 1, 'category_name' => 'LPG', 'unit' => 'kg', 'factor' => 3.00, 'reference_source' => 'IPCC 2006 Vol 2'],
            ['emission_standard_id' => 2, 'scope' => 1, 'category_name' => 'Natural Gas', 'unit' => 'm³', 'factor' => 1.95, 'reference_source' => 'IPCC 2006 Vol 2'],
            ['emission_standard_id' => 2, 'scope' => 1, 'category_name' => 'Refrigerant', 'unit' => 'kg', 'factor' => 1300.00, 'reference_source' => 'IPCC AR4 GWP'],
            // Scope 2
            ['emission_standard_id' => 2, 'scope' => 2, 'category_name' => 'Purchased Electricity', 'unit' => 'kWh', 'factor' => 0.70, 'reference_source' => 'IPCC Global Default'],
            ['emission_standard_id' => 2, 'scope' => 2, 'category_name' => 'Purchased Steam', 'unit' => 'kg', 'factor' => 0.20, 'reference_source' => 'IPCC Default Steam'],
            // Scope 3
            ['emission_standard_id' => 2, 'scope' => 3, 'category_name' => 'Business Travel', 'unit' => 'km', 'factor' => 0.15, 'reference_source' => 'IPCC Default Mob'],
            ['emission_standard_id' => 2, 'scope' => 3, 'category_name' => 'Employee Commuting', 'unit' => 'km', 'factor' => 0.10, 'reference_source' => 'IPCC Default Mob'],
            ['emission_standard_id' => 2, 'scope' => 3, 'category_name' => 'Waste to Landfill', 'unit' => 'kg', 'factor' => 0.50, 'reference_source' => 'IPCC 2006 Waste'],
            ['emission_standard_id' => 2, 'scope' => 3, 'category_name' => 'Water Supply', 'unit' => 'm³', 'factor' => 0.30, 'reference_source' => 'IPCC Default Env'],
            ['emission_standard_id' => 2, 'scope' => 3, 'category_name' => 'Wastewater Treatment', 'unit' => 'm³', 'factor' => 0.80, 'reference_source' => 'IPCC 2006 Waste'],
            ['emission_standard_id' => 2, 'scope' => 3, 'category_name' => 'Purchased Goods', 'unit' => 'kg', 'factor' => 1.30, 'reference_source' => 'IPCC Default Lifecycle'],
            ['emission_standard_id' => 2, 'scope' => 3, 'category_name' => 'Transportation & Distribution', 'unit' => 't-km', 'factor' => 0.18, 'reference_source' => 'IPCC Default Mob'],
            ['emission_standard_id' => 2, 'scope' => 3, 'category_name' => 'Capital Goods', 'unit' => 'kg', 'factor' => 2.80, 'reference_source' => 'IPCC Default Lifecycle'],

            // === INDONESIA NATIONAL (Standard ID: 3) ===
            // Scope 1
            ['emission_standard_id' => 3, 'scope' => 1, 'category_name' => 'Diesel', 'unit' => 'Liters', 'factor' => 2.65, 'reference_source' => 'KLHK Pedoman Inventarisasi'],
            ['emission_standard_id' => 3, 'scope' => 1, 'category_name' => 'Petrol', 'unit' => 'Liters', 'factor' => 2.25, 'reference_source' => 'KLHK Pedoman Inventarisasi'],
            ['emission_standard_id' => 3, 'scope' => 1, 'category_name' => 'LPG', 'unit' => 'kg', 'factor' => 2.90, 'reference_source' => 'KLHK Pedoman Inventarisasi'],
            ['emission_standard_id' => 3, 'scope' => 1, 'category_name' => 'Natural Gas', 'unit' => 'm³', 'factor' => 1.85, 'reference_source' => 'KLHK Pedoman Inventarisasi'],
            ['emission_standard_id' => 3, 'scope' => 1, 'category_name' => 'Refrigerant', 'unit' => 'kg', 'factor' => 1400.00, 'reference_source' => 'KLHK Inventarisasi GWP'],
            // Scope 2
            ['emission_standard_id' => 3, 'scope' => 2, 'category_name' => 'Purchased Electricity', 'unit' => 'kWh', 'factor' => 0.87, 'reference_source' => 'Kementerian ESDM 2023 (Jamali Grid)'],
            ['emission_standard_id' => 3, 'scope' => 2, 'category_name' => 'Purchased Steam', 'unit' => 'kg', 'factor' => 0.15, 'reference_source' => 'KLHK Pedoman'],
            // Scope 3
            ['emission_standard_id' => 3, 'scope' => 3, 'category_name' => 'Business Travel', 'unit' => 'km', 'factor' => 0.11, 'reference_source' => 'KLHK Pedoman'],
            ['emission_standard_id' => 3, 'scope' => 3, 'category_name' => 'Employee Commuting', 'unit' => 'km', 'factor' => 0.08, 'reference_source' => 'KLHK Pedoman'],
            ['emission_standard_id' => 3, 'scope' => 3, 'category_name' => 'Waste to Landfill', 'unit' => 'kg', 'factor' => 0.40, 'reference_source' => 'KLHK Pedoman Waste'],
            ['emission_standard_id' => 3, 'scope' => 3, 'category_name' => 'Water Supply', 'unit' => 'm³', 'factor' => 0.32, 'reference_source' => 'KLHK Pedoman'],
            ['emission_standard_id' => 3, 'scope' => 3, 'category_name' => 'Wastewater Treatment', 'unit' => 'm³', 'factor' => 0.70, 'reference_source' => 'KLHK Pedoman Waste'],
            ['emission_standard_id' => 3, 'scope' => 3, 'category_name' => 'Purchased Goods', 'unit' => 'kg', 'factor' => 1.10, 'reference_source' => 'KLHK Pedoman'],
            ['emission_standard_id' => 3, 'scope' => 3, 'category_name' => 'Transportation & Distribution', 'unit' => 't-km', 'factor' => 0.15, 'reference_source' => 'KLHK Pedoman'],
            ['emission_standard_id' => 3, 'scope' => 3, 'category_name' => 'Capital Goods', 'unit' => 'kg', 'factor' => 2.40, 'reference_source' => 'KLHK Pedoman'],
        ];

        // Fill in timestamps for each factor
        foreach ($factors as &$factor) {
            $factor['created_at'] = now();
            $factor['updated_at'] = now();
        }

        DB::table('emission_factors')->insert($factors);
    }
}
