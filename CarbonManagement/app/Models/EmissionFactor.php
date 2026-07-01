<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmissionFactor extends Model
{
    use HasFactory;

    protected $fillable = [
        'emission_standard_id',
        'scope',
        'category_name',
        'unit',
        'factor',
        'reference_source',
    ];

    protected $casts = [
        'factor' => 'decimal:6',
        'scope' => 'integer',
    ];

    /**
     * Get the emission standard that owns this factor.
     */
    public function emissionStandard(): BelongsTo
    {
        return $this->belongsTo(EmissionStandard::class);
    }
}
