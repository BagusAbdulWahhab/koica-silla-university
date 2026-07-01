<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmissionRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporting_period',
        'emission_standard_id',
        'scope1_total',
        'scope2_total',
        'scope3_total',
        'total_emission',
    ];

    protected $casts = [
        'scope1_total' => 'decimal:4',
        'scope2_total' => 'decimal:4',
        'scope3_total' => 'decimal:4',
        'total_emission' => 'decimal:4',
    ];

    /**
     * Get the emission standard used for this record.
     */
    public function emissionStandard(): BelongsTo
    {
        return $this->belongsTo(EmissionStandard::class);
    }

    /**
     * Get the category-level details for this record.
     */
    public function details(): HasMany
    {
        return $this->hasMany(EmissionRecordDetail::class);
    }
}
