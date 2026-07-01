<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmissionStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'reference_source',
        'publication_year',
    ];

    /**
     * Get the emission factors for this standard.
     */
    public function emissionFactors(): HasMany
    {
        return $this->hasMany(EmissionFactor::class);
    }

    /**
     * Get the emission records using this standard.
     */
    public function emissionRecords(): HasMany
    {
        return $this->hasMany(EmissionRecord::class);
    }
}
