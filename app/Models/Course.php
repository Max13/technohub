<?php

namespace App\Models;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Course extends Model
{
    use HasFactory;

    /** @inheritdoc */
    protected $fillable = [
        'ypareo_id',
        'label',
        'started_at',
        'ended_at',
        'duration',
    ];

    /** @inheritdoc */
    protected $casts = [
        'ypareo_id' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration' => 'integer',
    ];

    /** @inheritDoc */
    protected static function booted()
    {
        static::saved(function (Course $course) {
            DB::transaction(function () use ($course) {
                $course->absences()->sync(
                    Absence::whereBetween('started_at', [$course->started_at, $course->ended_at])
                           ->orWhereBetween('ended_at', [$course->started_at, $course->ended_at])
                           ->orWhereBetweenColumns($course->started_at, ['started_at', 'ended_at'])
                           ->orWhereBetweenColumns($course->ended_at, ['started_at', 'ended_at'])
                           ->pluck('id')
                );
            });
        });
    }

    /**
     * Retrieve the absences of this course
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function absences() : BelongsToMany
    {
        return $this->belongsToMany(Absence::class);
    }

    /**
     * Retrieve the student of this absence
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function classrooms() : BelongsToMany
    {
        return $this->belongsToMany(Classroom::class);
    }

    /**
     * Retrieve users participating in this course
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users() : BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Mutate dration attribute, as minutes if it's an interval in H:i:s format
     *
     * @param  int|string  $duration
     * @return void
     */
    public function setDurationAttribute($duration)
    {
        if (is_numeric($duration)) {
            $this->attributes['duration'] = $duration;
        } else {
            $this->attributes['duration'] = CarbonInterval::createFromFormat('H:i:s', $duration)->totalMinutes;
        }
    }
}
