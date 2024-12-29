<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** @inheritdoc */
    protected $fillable = [
        'ypareo_id',
        'name',
        'type',
    ];

    /** @inheritdoc */
    protected $casts = [
        'ypareo_id' => 'integer',
    ];

    /**
     * Get subject's courses
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courses() : HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Get subject's trainings
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function trainings() : BelongsToMany
    {
        return $this->belongsToMany(Training::class);
    }
}
