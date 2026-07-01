<?php

namespace App\Http\Controllers;

use App\Models\EmissionRecord;
use App\Models\EmissionStandard;
use App\Models\EmissionFactor;
use App\Services\EmissionCalculationService;
use Illuminate\Http\Request;

class CarbonCalculatorController extends Controller
{
    protected $calculationService;

    public function __construct(EmissionCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Display the Home Page.
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Show the Carbon Calculator Page.
     */
    public function create()
    {
        $standards = EmissionStandard::all();

        // Get categories and units dynamically from standard 1
        $baseFactors = EmissionFactor::where('emission_standard_id', 1)->get()->groupBy('scope');

        $scope1Categories = $baseFactors[1] ?? collect();
        $scope2Categories = $baseFactors[2] ?? collect();
        $scope3Categories = $baseFactors[3] ?? collect();

        // Months array for the dropdown
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Years array (e.g. current year - 5 to current year + 1)
        $currentYear = (int) date('Y');
        $years = range($currentYear - 6, $currentYear + 2);

        return view('calculator', compact(
            'standards',
            'scope1Categories',
            'scope2Categories',
            'scope3Categories',
            'months',
            'years'
        ));
    }

    /**
     * Store the calculation and redirect to the result page.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reporting_month' => 'required|string',
            'reporting_year' => 'required|integer',
            'emission_standard_id' => 'required|exists:emission_standards,id',
            'scope1' => 'nullable|array',
            'scope1.*' => 'nullable|numeric|min:0',
            'scope2' => 'nullable|array',
            'scope2.*' => 'nullable|numeric|min:0',
            'scope3' => 'nullable|array',
            'scope3.*' => 'nullable|numeric|min:0',
            'scope3_active' => 'nullable|array',
            'scope3_active.*' => 'string',
        ]);

        try {
            $record = $this->calculationService->calculateAndSave($validated);

            return redirect()->route('records.show', $record->id)
                ->with('success', 'Carbon footprint calculated and saved successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Calculation failed: ' . $e->getMessage());
        }
    }

    /**
     * Show the detailed results of a calculation.
     */
    public function show($id)
    {
        $record = EmissionRecord::with(['emissionStandard', 'details'])->findOrFail($id);

        return view('result', compact('record'));
    }

    /**
     * Show historical calculation records.
     */
    public function history()
    {
        $records = EmissionRecord::with('emissionStandard')
            ->orderBy('id', 'desc')
            ->get();

        return view('history', compact('records'));
    }

    /**
     * Delete a calculation record.
     */
    public function destroy($id)
    {
        $record = EmissionRecord::findOrFail($id);
        $record->delete();

        return redirect()->route('records.history')
            ->with('success', 'Record deleted successfully.');
    }
}
