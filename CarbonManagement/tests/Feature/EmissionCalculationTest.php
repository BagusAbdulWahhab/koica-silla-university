<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\EmissionFactorSeeder;
use App\Models\EmissionRecord;
use App\Models\EmissionRecordDetail;

class EmissionCalculationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test calculation accuracy and database persistence.
     */
    public function test_carbon_calculation_and_storage(): void
    {
        // 1. Run seeders
        $this->seed(EmissionFactorSeeder::class);

        // 2. Post calculation data (GHG Protocol Standard ID = 1)
        $response = $this->post(route('records.store'), [
            'reporting_month' => 'June',
            'reporting_year' => 2026,
            'emission_standard_id' => 1, // GHG Protocol
            'scope1' => [
                'Diesel' => 100, // 100 * 2.68 = 268.0
                'Petrol' => 0,
            ],
            'scope2' => [
                'Purchased Electricity' => 1000, // 1000 * 0.85 = 850.0
            ],
            'scope3' => [
                'Business Travel' => 500, // 500 * 0.12 = 60.0
                'Employee Commuting' => 200, // should not be calculated because not in scope3_active
            ],
            'scope3_active' => [
                'Business Travel',
            ],
        ]);

        // 3. Assert redirection to details view
        $record = EmissionRecord::first();
        $this->assertNotNull($record);
        $response->assertRedirect(route('records.show', $record->id));

        // 4. Validate calculations
        // Total = 268 + 850 + 60 = 1178.0
        $this->assertEquals(268.0, (float) $record->scope1_total);
        $this->assertEquals(850.0, (float) $record->scope2_total);
        $this->assertEquals(60.0, (float) $record->scope3_total);
        $this->assertEquals(1178.0, (float) $record->total_emission);

        // 5. Validate detail rows
        $this->assertDatabaseHas('emission_record_details', [
            'emission_record_id' => $record->id,
            'scope' => 1,
            'category_name' => 'Diesel',
            'activity_value' => 100,
            'emission_factor' => 2.68,
            'emission_result' => 268.0,
        ]);

        $this->assertDatabaseHas('emission_record_details', [
            'emission_record_id' => $record->id,
            'scope' => 2,
            'category_name' => 'Purchased Electricity',
            'activity_value' => 1000,
            'emission_factor' => 0.85,
            'emission_result' => 850.0,
        ]);

        $this->assertDatabaseHas('emission_record_details', [
            'emission_record_id' => $record->id,
            'scope' => 3,
            'category_name' => 'Business Travel',
            'activity_value' => 500,
            'emission_factor' => 0.12,
            'emission_result' => 60.0,
        ]);

        // Check that Employee Commuting was not saved since it wasn't in active list
        $this->assertDatabaseMissing('emission_record_details', [
            'emission_record_id' => $record->id,
            'category_name' => 'Employee Commuting',
        ]);
    }

    /**
     * Test record deletion and child cascading delete.
     */
    public function test_delete_emission_record(): void
    {
        $this->seed(EmissionFactorSeeder::class);

        // Create a record
        $record = EmissionRecord::create([
            'reporting_period' => 'June 2026',
            'emission_standard_id' => 1,
            'scope1_total' => 200,
            'scope2_total' => 300,
            'scope3_total' => 100,
            'total_emission' => 600,
        ]);

        // Create detail
        $detail = EmissionRecordDetail::create([
            'emission_record_id' => $record->id,
            'scope' => 1,
            'category_name' => 'Diesel',
            'activity_value' => 100,
            'unit' => 'Liters',
            'emission_factor' => 2.0,
            'emission_result' => 200,
        ]);

        $this->assertDatabaseHas('emission_records', ['id' => $record->id]);
        $this->assertDatabaseHas('emission_record_details', ['id' => $detail->id]);

        // Send delete request
        $response = $this->delete(route('records.destroy', $record->id));

        // Assert redirect and success flash message
        $response->assertRedirect(route('records.history'));
        $response->assertSessionHas('success', 'Record deleted successfully.');

        // Assert record and detail are deleted (cascade)
        $this->assertDatabaseMissing('emission_records', ['id' => $record->id]);
        $this->assertDatabaseMissing('emission_record_details', ['id' => $detail->id]);
    }

    /**
     * Test the EmissionRecordSeeder logic.
     */
    public function test_emission_record_seeder(): void
    {
        $this->seed(EmissionFactorSeeder::class);

        // Run seeder
        $this->seed(\Database\Seeders\EmissionRecordSeeder::class);

        // Verify we have 12 records
        $this->assertEquals(12, EmissionRecord::count());

        // Verify July 2024 has a spike (> 10% increase compared to June 2024)
        $june = EmissionRecord::where('reporting_period', 'June 2024')->first();
        $july = EmissionRecord::where('reporting_period', 'July 2024')->first();
        $this->assertNotNull($june);
        $this->assertNotNull($july);

        $increasePercentage = (($july->total_emission - $june->total_emission) / $june->total_emission) * 100;
        $this->assertTrue($increasePercentage > 10.0, "July spike should be >10%, actual: {$increasePercentage}%");

        // Verify October 2024 has high Scope 2 (> 70% share of total)
        $october = EmissionRecord::where('reporting_period', 'October 2024')->first();
        $this->assertNotNull($october);

        $scope2Share = ($october->scope2_total / $october->total_emission) * 100;
        $this->assertTrue($scope2Share > 70.0, "October Scope 2 share should be >70%, actual: {$scope2Share}%");
    }
}
