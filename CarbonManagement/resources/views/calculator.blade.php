@extends('layouts.app')

@section('styles')
<style>
    /* Radio Cards for Standards */
    .standard-card {
        cursor: pointer;
        position: relative;
        transition: all 0.3s ease;
        border: 2px solid var(--border-color);
        background-color: var(--bg-card);
        border-radius: 12px;
        padding: 1.25rem;
        height: 100%;
    }
    
    .standard-card:hover {
        border-color: #475569;
        transform: translateY(-2px);
    }
    
    .standard-radio:checked + .standard-card {
        border-color: #3b82f6;
        background-color: rgba(59, 130, 246, 0.08);
        box-shadow: 0 0 0 1px #3b82f6;
    }
    
    .standard-radio {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    /* Checked indicator */
    .standard-card::after {
        content: '\f058';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        top: 15px;
        right: 15px;
        color: #3b82f6;
        font-size: 1.25rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .standard-radio:checked + .standard-card::after {
        opacity: 1;
    }

    /* Checkbox list styled */
    .category-checkbox-wrapper {
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 12px 16px;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        background-color: #162032;
    }
    
    .category-checkbox-wrapper:hover {
        border-color: var(--scope3);
        background-color: rgba(168, 85, 247, 0.05);
    }
    
    .category-checkbox-wrapper input[type="checkbox"]:checked + span {
        color: var(--text-main);
        font-weight: 600;
    }

    .category-checkbox-wrapper input[type="checkbox"] {
        accent-color: var(--scope3);
        width: 18px;
        height: 18px;
        margin-right: 12px;
    }

    /* Dynamic inputs container */
    .dynamic-input-group {
        display: none;
        opacity: 0;
        transform: translateY(10px);
        transition: opacity 0.4s ease, transform 0.4s ease;
    }
    
    .dynamic-input-group.show {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }
</style>
@endsection

@section('content')
<div class="row mb-4 animated-fade">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Calculator</li>
            </ol>
        </nav>
        <h2 class="display-6 fw-bold text-white mb-2">Carbon Footprint Calculator</h2>
        <p class="text-secondary">Input your activity metrics and select your standard framework to record emissions.</p>
    </div>
</div>

<form action="{{ route('records.store') }}" method="POST" id="calculatorForm" class="animated-fade" style="animation-delay: 0.1s;">
    @csrf

    <div class="row g-4">
        <!-- left Column: configuration (Standard, Period) -->
        <div class="col-xl-4 col-lg-5">
            <!-- Reporting Period Card -->
            <div class="glass-card p-4 mb-4">
                <h3 class="h5 text-white mb-4"><i class="fa-solid fa-calendar-days text-primary me-2"></i>Reporting Period</h3>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="reporting_month" class="form-label text-secondary fs-7 fw-semibold">Reporting Month</label>
                        <select name="reporting_month" id="reporting_month" class="form-select form-select-custom" required>
                            @foreach($months as $month)
                                <option value="{{ $month }}" {{ (date('F') == $month) ? 'selected' : '' }}>{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="reporting_year" class="form-label text-secondary fs-7 fw-semibold">Reporting Year</label>
                        <select name="reporting_year" id="reporting_year" class="form-select form-select-custom" required>
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ (date('Y') == $year) ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Emission Factor Standard Card -->
            <div class="glass-card p-4 mb-4">
                <h3 class="h5 text-white mb-3"><i class="fa-solid fa-shield-halved text-primary me-2"></i>Emission Factor Standard</h3>
                <p class="text-secondary fs-7 mb-4">Select the reference standard. The calculation engine will apply factors specific to this framework.</p>
                
                <div class="row g-3">
                    @foreach($standards as $standard)
                        <div class="col-12">
                            <label class="w-100">
                                <input type="radio" name="emission_standard_id" value="{{ $standard->id }}" class="standard-radio" {{ $loop->first ? 'checked' : '' }} required>
                                <div class="standard-card">
                                    <h4 class="h6 text-white mb-1"><i class="fa-solid fa-file-invoice text-primary me-2"></i>{{ $standard->name }}</h4>
                                    <p class="text-secondary mb-2" style="font-size: 0.8rem;">{{ $standard->description }}</p>
                                    <div class="d-flex justify-content-between align-items-center mt-2 border-top border-secondary border-opacity-25 pt-2 fs-8 text-secondary">
                                        <span>Source: <strong>{{ $standard->reference_source }}</strong></span>
                                        <span>Year: <strong>{{ $standard->publication_year }}</strong></span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column: Scope Inputs -->
        <div class="col-xl-8 col-lg-7">
            <!-- Scope 1: Direct Emissions -->
            <div class="glass-card p-4 border-scope1 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="h5 text-white mb-0"><i class="fa-solid fa-fire-burner text-success me-2"></i>Scope 1 - Direct Emissions</h3>
                    <span class="badge badge-scope1 rounded-pill px-3 py-1.5 fw-bold fs-8">DIRECT</span>
                </div>
                
                <div class="row g-3">
                    @foreach($scope1Categories as $category)
                        <div class="col-md-6 col-12">
                            <label for="scope1_{{ str_replace(' ', '_', $category->category_name) }}" class="form-label text-secondary fs-7 fw-semibold">
                                {{ $category->category_name }} ({{ $category->unit }})
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary border-opacity-50 text-secondary">
                                    @if($category->category_name == 'Diesel' || $category->category_name == 'Petrol')
                                        <i class="fa-solid fa-gas-pump"></i>
                                    @elseif($category->category_name == 'LPG' || $category->category_name == 'Natural Gas')
                                        <i class="fa-solid fa-fire"></i>
                                    @else
                                        <i class="fa-solid fa-snowflake"></i>
                                    @endif
                                </span>
                                <input type="number" step="any" min="0" 
                                       name="scope1[{{ $category->category_name }}]" 
                                       id="scope1_{{ str_replace(' ', '_', $category->category_name) }}" 
                                       class="form-control form-control-custom" 
                                       placeholder="0.00">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Scope 2: Purchased Energy -->
            <div class="glass-card p-4 border-scope2 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="h5 text-white mb-0"><i class="fa-solid fa-bolt text-primary me-2"></i>Scope 2 - Purchased Energy</h3>
                    <span class="badge badge-scope2 rounded-pill px-3 py-1.5 fw-bold fs-8">UTILITIES</span>
                </div>
                
                <div class="row g-3">
                    @foreach($scope2Categories as $category)
                        <div class="col-md-6 col-12">
                            <label for="scope2_{{ str_replace(' ', '_', $category->category_name) }}" class="form-label text-secondary fs-7 fw-semibold">
                                {{ $category->category_name }} ({{ $category->unit }})
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary border-opacity-50 text-secondary">
                                    @if($category->category_name == 'Purchased Electricity')
                                        <i class="fa-solid fa-bolt"></i>
                                    @else
                                        <i class="fa-solid fa-cloud"></i>
                                    @endif
                                </span>
                                <input type="number" step="any" min="0" 
                                       name="scope2[{{ $category->category_name }}]" 
                                       id="scope2_{{ str_replace(' ', '_', $category->category_name) }}" 
                                       class="form-control form-control-custom" 
                                       placeholder="0.00">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Scope 3: Indirect Value Chain -->
            <div class="glass-card p-4 border-scope3 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h5 text-white mb-0"><i class="fa-solid fa-briefcase text-purple me-2" style="color: var(--scope3);"></i>Scope 3 - Indirect Value Chain</h3>
                    <span class="badge badge-scope3 rounded-pill px-3 py-1.5 fw-bold fs-8">VALUE CHAIN</span>
                </div>
                <p class="text-secondary fs-7 mb-4">Scope 3 calculations are flexible. Check the boxes corresponding to the categories you want to include in this period. Input fields will appear dynamically.</p>
                
                <!-- Category Selectors -->
                <div class="row g-2 mb-4">
                    @foreach($scope3Categories as $category)
                        <div class="col-md-6 col-12">
                            <div class="category-checkbox-wrapper" onclick="toggleCheckbox('checkbox_{{ $category->id }}')">
                                <input type="checkbox" name="scope3_active[]" value="{{ $category->category_name }}" 
                                       id="checkbox_{{ $category->id }}" class="scope3-selector" 
                                       data-category-id="group_{{ $category->id }}"
                                       onclick="event.stopPropagation(); handleCheckboxChange(this);">
                                <span class="text-secondary" style="font-size: 0.9rem;">
                                    @if($category->category_name == 'Business Travel') <i class="fa-solid fa-plane text-purple me-2 fs-6"></i>
                                    @elseif($category->category_name == 'Employee Commuting') <i class="fa-solid fa-car-side text-purple me-2 fs-6"></i>
                                    @elseif($category->category_name == 'Waste to Landfill') <i class="fa-solid fa-trash-can text-purple me-2 fs-6"></i>
                                    @elseif($category->category_name == 'Water Supply') <i class="fa-solid fa-droplet text-purple me-2 fs-6"></i>
                                    @elseif($category->category_name == 'Wastewater Treatment') <i class="fa-solid fa-soap text-purple me-2 fs-6"></i>
                                    @elseif($category->category_name == 'Purchased Goods') <i class="fa-solid fa-basket-shopping text-purple me-2 fs-6"></i>
                                    @elseif($category->category_name == 'Transportation & Distribution') <i class="fa-solid fa-truck text-purple me-2 fs-6"></i>
                                    @else <i class="fa-solid fa-cubes text-purple me-2 fs-6"></i>
                                    @endif
                                    {{ $category->category_name }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Dynamic Input Fields -->
                <div class="border-top border-secondary border-opacity-25 pt-4" id="scope3InputsContainer">
                    <p class="text-secondary text-center py-4" id="noScope3Message">
                        <i class="fa-solid fa-circle-info fs-5 me-2"></i>Select categories above to reveal input fields.
                    </p>
                    
                    <div class="row g-3">
                        @foreach($scope3Categories as $category)
                            <div class="col-md-6 col-12 dynamic-input-group" id="group_{{ $category->id }}">
                                <label for="scope3_{{ $category->id }}" class="form-label text-white fs-7 fw-semibold">
                                    {{ $category->category_name }} ({{ $category->unit }})
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary border-opacity-50 text-secondary">
                                        @if($category->category_name == 'Business Travel') <i class="fa-solid fa-plane"></i>
                                        @elseif($category->category_name == 'Employee Commuting') <i class="fa-solid fa-car-side"></i>
                                        @elseif($category->category_name == 'Waste to Landfill') <i class="fa-solid fa-trash-can"></i>
                                        @elseif($category->category_name == 'Water Supply') <i class="fa-solid fa-droplet"></i>
                                        @elseif($category->category_name == 'Wastewater Treatment') <i class="fa-solid fa-soap"></i>
                                        @elseif($category->category_name == 'Purchased Goods') <i class="fa-solid fa-basket-shopping"></i>
                                        @elseif($category->category_name == 'Transportation & Distribution') <i class="fa-solid fa-truck"></i>
                                        @else <i class="fa-solid fa-cubes"></i>
                                        @endif
                                    </span>
                                    <input type="number" step="any" min="0" 
                                           name="scope3[{{ $category->category_name }}]" 
                                           id="scope3_{{ $category->id }}" 
                                           class="form-control form-control-custom" 
                                           placeholder="0.00">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Submit Button Section -->
            <div class="text-end">
                <button type="submit" class="btn-premium btn-lg w-100 py-3 d-flex align-items-center justify-content-center">
                    <i class="fa-solid fa-bolt me-2"></i> Run Calculations & Save Record
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    // Toggle check box when clicking card wrapper
    function toggleCheckbox(checkboxId) {
        const checkbox = document.getElementById(checkboxId);
        checkbox.checked = !checkbox.checked;
        handleCheckboxChange(checkbox);
    }
    
    // Trigger on checkbox check
    function handleCheckboxChange(checkbox) {
        const targetId = checkbox.getAttribute('data-category-id');
        const inputGroup = document.getElementById(targetId);
        const inputField = inputGroup.querySelector('input[type="number"]');
        
        if (checkbox.checked) {
            inputGroup.classList.add('show');
            // Enable and clear input
            inputField.removeAttribute('disabled');
        } else {
            inputGroup.classList.remove('show');
            // Disable input so it won't be sent or validated as required, and reset value
            inputField.value = '';
            inputField.setAttribute('disabled', 'true');
        }
        
        // Check if any checkbox is checked to toggle the placeholder message
        updatePlaceholderMessage();
    }
    
    function updatePlaceholderMessage() {
        const checkboxes = document.querySelectorAll('.scope3-selector');
        const message = document.getElementById('noScope3Message');
        let anyChecked = false;
        
        checkboxes.forEach(cb => {
            if (cb.checked) anyChecked = true;
        });
        
        if (anyChecked) {
            message.style.display = 'none';
        } else {
            message.style.display = 'block';
        }
    }
    
    // Initialize inputs as disabled on load
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.scope3-selector');
        checkboxes.forEach(cb => {
            const targetId = cb.getAttribute('data-category-id');
            const inputGroup = document.getElementById(targetId);
            const inputField = inputGroup.querySelector('input[type="number"]');
            
            if (!cb.checked) {
                inputField.setAttribute('disabled', 'true');
            } else {
                inputGroup.classList.add('show');
            }
        });
        updatePlaceholderMessage();
    });
</script>
@endsection
