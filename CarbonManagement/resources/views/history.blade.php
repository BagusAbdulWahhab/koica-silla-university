@extends('layouts.app')

@section('styles')
<style>
    /* Table Glassmorphism styling */
    .table-history {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .table-history th {
        background-color: #162032;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px;
    }
    
    .table-history td {
        border-bottom: 1px solid #273549;
        color: var(--text-main);
        padding: 16px;
        vertical-align: middle;
        font-size: 0.9rem;
    }
    
    .table-history tr:last-child td {
        border-bottom: none;
    }
    
    .table-history tbody tr:hover {
        background-color: #222f44;
    }

    .btn-action-view {
        background-color: rgba(59, 130, 246, 0.1);
        border: 1px solid #3b82f6;
        color: #60a5fa;
        border-radius: 8px;
        padding: 6px 12px;
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-action-view:hover {
        background-color: #3b82f6;
        color: #ffffff;
    }

    .btn-action-delete {
        background-color: rgba(239, 68, 68, 0.1);
        border: 1px solid #ef4444;
        color: #f87171;
        border-radius: 8px;
        padding: 6px 12px;
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    .btn-action-delete:hover {
        background-color: #ef4444;
        color: #ffffff;
    }
</style>
@endsection

@section('content')
<div class="row mb-4 animated-fade">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-secondary text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">History</li>
                </ol>
            </nav>
            <h2 class="display-6 fw-bold text-white mb-2">Historical Records</h2>
            <p class="text-secondary mb-0">Review previously stored carbon calculation records. Data is synced to MySQL and ready for Tableau connector import.</p>
        </div>
        <div>
            <a href="{{ route('records.create') }}" class="btn-premium">
                <i class="fa-solid fa-plus me-2"></i>New Calculation
            </a>
        </div>
    </div>
</div>

<div class="row animated-fade" style="animation-delay: 0.1s;">
    <div class="col-12">
        @if($records->isEmpty())
            <div class="glass-card p-5 text-center text-secondary">
                <i class="fa-solid fa-folder-open fs-1 mb-4 text-muted"></i>
                <h4 class="text-white mb-2">No Records Found</h4>
                <p class="mb-4">You haven't run any carbon calculations yet.</p>
                <a href="{{ route('records.create') }}" class="btn-premium">
                    <i class="fa-solid fa-calculator me-2"></i>Run First Calculation
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-history">
                    <thead>
                        <tr>
                            <th>Reporting Period</th>
                            <th>Standard Framework</th>
                            <th class="text-end">Scope 1 (tCO<sub>2</sub>e)</th>
                            <th class="text-end">Scope 2 (tCO<sub>2</sub>e)</th>
                            <th class="text-end">Scope 3 (tCO<sub>2</sub>e)</th>
                            <th class="text-end">Total (tCO<sub>2</sub>e)</th>
                            <th class="text-center" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                            <tr>
                                <td class="fw-bold text-white">
                                    <i class="fa-solid fa-calendar text-secondary me-2"></i>{{ $record->reporting_period }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-25 border border-secondary border-opacity-50 text-white">
                                        {{ $record->emissionStandard->name }}
                                    </span>
                                </td>
                                <td class="text-end fw-mono text-success">{{ number_format($record->scope1_total / 1000, 4) }}</td>
                                <td class="text-end fw-mono text-success">{{ number_format($record->scope2_total / 1000, 4) }}</td>
                                <td class="text-end fw-mono text-success">{{ number_format($record->scope3_total / 1000, 4) }}</td>
                                <td class="text-end fw-mono text-success fw-bold fs-7">{{ number_format($record->total_emission / 1000, 4) }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('records.show', $record->id) }}" class="btn-action-view" title="View Details">
                                            <i class="fa-solid fa-eye me-1"></i>View
                                        </a>
                                        
                                        <form action="{{ route('records.destroy', $record->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this record?\n\nThis action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action-delete" title="Delete Record">
                                                <i class="fa-solid fa-trash-can me-1"></i>Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
