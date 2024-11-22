<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** @inheritdoc */
    protected $fillable = [
        'name',
        'fullname',
        'nth_year',
    ];

    /** @inheritdoc */
    protected $casts = [
        'nth_year' => 'integer',
    ];

    /**
     * Get training's trainings
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function classrooms() : HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * Retrieve students of a given training
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function students() : HasMany
    {
        return $this->hasMany(User::class)
                    ->where('is_student', true);
    }

    /**
     * Get training's subjects
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subjects() : BelongsToMany
    {
        return $this->belongsToMany(Subject::class);
    }

    /**
     * Retrieve trainers of a given training
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trainers() : BelongsToMany
    {
        return $this->belongsToMany(User::class)
                    ->where('is_trainer', true);
    }
}
