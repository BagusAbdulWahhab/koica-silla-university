@extends('layouts.app')

@section('styles')
<style>
    /* Glowing total card */
    .total-emission-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        position: relative;
        overflow: hidden;
    }
    
    .total-emission-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 70%);
        pointer-events: none;
    }
    
    .metric-value {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        line-height: 1;
        background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    /* Stacked progress bar */
    .stacked-progress-container {
        background-color: #0f172a;
        height: 24px;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        border: 1px solid var(--border-color);
    }
    
    .progress-segment {
        height: 100%;
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        color: #ffffff;
    }
    
    .progress-s1 { background-color: var(--scope1); box-shadow: 0 0 10px rgba(16, 185, 129, 0.3); }
    .progress-s2 { background-color: var(--scope2); box-shadow: 0 0 10px rgba(59, 130, 246, 0.3); }
    .progress-s3 { background-color: var(--scope3); box-shadow: 0 0 10px rgba(168, 85, 247, 0.3); }

    /* Custom detailed table */
    .table-custom {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .table-custom th {
        background-color: #162032;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px;
    }
    
    .table-custom td {
        border-bottom: 1px solid #273549;
        color: var(--text-main);
        padding: 16px;
        font-size: 0.9rem;
    }
    
    .table-custom tr:last-child td {
        border-bottom: none;
    }
    
    .table-custom tbody tr:hover {
        background-color: #222f44;
    }
</style>
@endsection

@section('content')
@php
    $s1 = (float) $record->scope1_total;
    $s2 = (float) $record->scope2_total;
    $s3 = (float) $record->scope3_total;
    $total = (float) $record->total_emission;
    
    // Percentages
    $s1_pct = $total > 0 ? round(($s1 / $total) * 100, 1) : 0;
    $s2_pct = $total > 0 ? round(($s2 / $total) * 100, 1) : 0;
    $s3_pct = $total > 0 ? round(($s3 / $total) * 100, 1) : 0;
    
    // Convert to tons (tCO2e)
    $s1_ton = $s1 / 1000;
    $s2_ton = $s2 / 1000;
    $s3_ton = $s3 / 1000;
    $total_ton = $total / 1000;
@endphp

<div class="row mb-4 animated-fade">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('records.history') }}" class="text-secondary text-decoration-none">History</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Result</li>
                </ol>
            </nav>
            <h2 class="display-6 fw-bold text-white mb-1">Calculation Details</h2>
            <p class="text-secondary mb-0">Reporting Period: <strong>{{ $record->reporting_period }}</strong> &bull; Framework: <strong>{{ $record->emissionStandard->name }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('records.history') }}" class="btn-premium-outline">
                <i class="fa-solid fa-list-ul me-2"></i>All Records
            </a>
            <a href="{{ route('records.create') }}" class="btn-premium">
                <i class="fa-solid fa-plus me-2"></i>New Calculation
            </a>
        </div>
    </div>
</div>

<div class="row g-4 mb-5 animated-fade" style="animation-delay: 0.1s;">
    <!-- Large Total Card -->
    <div class="col-lg-4 col-12">
        <div class="total-emission-card p-4 h-100 d-flex flex-column justify-content-between">
            <div>
                <span class="badge badge-scope1 rounded-pill px-3 py-1.5 fw-semibold fs-8 mb-4 text-uppercase">
                    Total Carbon Footprint
                </span>
                <div class="mt-2">
                    <h2 class="display-3 metric-value mb-1">{{ number_format($total_ton, 4) }}</h2>
                    <span class="fs-4 fw-bold text-white">tCO<sub>2</sub>e</span>
                    <span class="text-secondary d-block mt-1">Tonnes Carbon Dioxide Equivalent</span>
                </div>
            </div>
            
            <div class="border-top border-secondary border-opacity-25 pt-4 mt-4">
                <div class="d-flex justify-content-between text-secondary fs-7">
                    <span>Absolute Value:</span>
                    <strong class="text-white">{{ number_format($total, 2) }} kgCO<sub>2</sub>e</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Scopes contribution & bar chart -->
    <div class="col-lg-8 col-12">
        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
            <div>
                <h3 class="h5 text-white mb-3"><i class="fa-solid fa-chart-pie text-primary me-2"></i>Scope Contribution</h3>
                <p class="text-secondary fs-7 mb-4">Emissions breakdown by standard greenhouse gas accounting scopes. Visual representation below shows relative share.</p>
                
                <!-- Stacked Progress Bar -->
                <div class="stacked-progress-container mb-4">
                    @if($s1_pct > 0)
                        <div class="progress-segment progress-s1" style="width: {{ $s1_pct }}%;" title="Scope 1: {{ $s1_pct }}%">
                            {{ $s1_pct >= 8 ? $s1_pct.'%' : '' }}
                        </div>
                    @endif
                    @if($s2_pct > 0)
                        <div class="progress-segment progress-s2" style="width: {{ $s2_pct }}%;" title="Scope 2: {{ $s2_pct }}%">
                            {{ $s2_pct >= 8 ? $s2_pct.'%' : '' }}
                        </div>
                    @endif
                    @if($s3_pct > 0)
                        <div class="progress-segment progress-s3" style="width: {{ $s3_pct }}%;" title="Scope 3: {{ $s3_pct }}%">
                            {{ $s3_pct >= 8 ? $s3_pct.'%' : '' }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Scope Metrics Row -->
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="bg-dark bg-opacity-40 p-3 rounded-3 border border-secondary border-opacity-25">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: var(--scope1);"></span>
                            <span class="fs-8 fw-bold text-secondary uppercase">Scope 1</span>
                        </div>
                        <h4 class="text-white mb-1">{{ number_format($s1_ton, 4) }} <span class="fs-8 text-secondary">t</span></h4>
                        <div class="d-flex justify-content-between fs-8 text-secondary">
                            <span>{{ number_format($s1, 1) }} kg</span>
                            <span>{{ $s1_pct }}%</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="bg-dark bg-opacity-40 p-3 rounded-3 border border-secondary border-opacity-25">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: var(--scope2);"></span>
                            <span class="fs-8 fw-bold text-secondary uppercase">Scope 2</span>
                        </div>
                        <h4 class="text-white mb-1">{{ number_format($s2_ton, 4) }} <span class="fs-8 text-secondary">t</span></h4>
                        <div class="d-flex justify-content-between fs-8 text-secondary">
                            <span>{{ number_format($s2, 1) }} kg</span>
                            <span>{{ $s2_pct }}%</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="bg-dark bg-opacity-40 p-3 rounded-3 border border-secondary border-opacity-25">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: var(--scope3);"></span>
                            <span class="fs-8 fw-bold text-secondary uppercase">Scope 3</span>
                        </div>
                        <h4 class="text-white mb-1">{{ number_format($s3_ton, 4) }} <span class="fs-8 text-secondary">t</span></h4>
                        <div class="d-flex justify-content-between fs-8 text-secondary">
                            <span>{{ number_format($s3, 1) }} kg</span>
                            <span>{{ $s3_pct }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Table Section -->
<div class="row animated-fade" style="animation-delay: 0.2s;">
    <div class="col-12">
        <h3 class="h4 text-white mb-3"><i class="fa-solid fa-list-check text-primary me-2"></i>Detailed Emissions Breakdown</h3>
        
        @if($record->details->isEmpty())
            <div class="glass-card p-5 text-center text-secondary">
                <i class="fa-solid fa-circle-info fs-3 mb-3"></i>
                <p class="mb-0">No non-zero activity data recorded for this period.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Scope</th>
                            <th>Category</th>
                            <th class="text-end">Activity Data</th>
                            <th>Unit</th>
                            <th class="text-end">Emission Factor</th>
                            <th class="text-end">Result (kgCO<sub>2</sub>e)</th>
                            <th class="text-end">Result (tCO<sub>2</sub>e)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($record->details->sortBy('scope') as $detail)
                            <tr>
                                <td>
                                    @if($detail->scope == 1)
                                        <span class="badge badge-scope1">Scope 1</span>
                                    @elseif($detail->scope == 2)
                                        <span class="badge badge-scope2">Scope 2</span>
                                    @else
                                        <span class="badge badge-scope3">Scope 3</span>
                                    @endif
                                </td>
                                <td class="fw-semibold text-white">
                                    @if($detail->category_name == 'Diesel' || $detail->category_name == 'Petrol')
                                        <i class="fa-solid fa-gas-pump text-secondary me-2"></i>
                                    @elseif($detail->category_name == 'Purchased Electricity')
                                        <i class="fa-solid fa-bolt text-secondary me-2"></i>
                                    @elseif($detail->category_name == 'Business Travel')
                                        <i class="fa-solid fa-plane text-secondary me-2"></i>
                                    @elseif($detail->category_name == 'Employee Commuting')
                                        <i class="fa-solid fa-car-side text-secondary me-2"></i>
                                    @elseif($detail->category_name == 'Waste to Landfill')
                                        <i class="fa-solid fa-trash-can text-secondary me-2"></i>
                                    @elseif($detail->category_name == 'Water Supply')
                                        <i class="fa-solid fa-droplet text-secondary me-2"></i>
                                    @else
                                        <i class="fa-solid fa-cubes text-secondary me-2"></i>
                                    @endif
                                    {{ $detail->category_name }}
                                </td>
                                <td class="text-end fw-mono">{{ number_format($detail->activity_value, 2) }}</td>
                                <td class="text-secondary">{{ $detail->unit }}</td>
                                <td class="text-end text-secondary fw-mono">{{ number_format($detail->emission_factor, 6) }}</td>
                                <td class="text-end text-success fw-bold fw-mono">{{ number_format($detail->emission_result, 4) }}</td>
                                <td class="text-end text-success fw-bold fw-mono">{{ number_format($detail->emission_result / 1000, 6) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
