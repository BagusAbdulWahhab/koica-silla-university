<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmissionRecordDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'emission_record_id',
        'scope',
        'category_name',
        'activity_value',
        'unit',
        'emission_factor',
        'emission_result',
    ];

    protected $casts = [
        'scope' => 'integer',
        'activity_value' => 'decimal:4',
        'emission_factor' => 'decimal:6',
        'emission_result' => 'decimal:4',
    ];

    /**
     * Get the emission record that owns this detail.
     */
    public function emissionRecord(): BelongsTo
    {
        return $this->belongsTo(EmissionRecord::class);
    }
}
