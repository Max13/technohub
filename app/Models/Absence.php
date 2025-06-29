<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Absence extends Model
{
    use HasFactory;

    /** @inheritdoc */
    protected $fillable = [
        'ypareo_id',
        'label',
        'is_delay',
        'is_justified',
        'started_at',
        'ended_at',
        'duration',
    ];

    /** @inheritdoc */
    protected $casts = [
        'ypareo_id' => 'integer',
        'is_delay' => 'boolean',
        'is_justified' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration' => 'integer',
    ];

    /** @inheritDoc */
    protected static function booted()
    {
        static::saved(function (Absence $absence) {
            DB::transaction(function () use ($absence) {
                $absence->courses()->sync(
                    Course::whereBetween('started_at', [$absence->started_at, $absence->ended_at])
                          ->orWhereBetween('ended_at', [$absence->started_at, $absence->ended_at])
                          ->orWhereBetweenColumns($absence->started_at, ['started_at', 'ended_at'])
                          ->orWhereBetweenColumns($absence->ended_at, ['started_at', 'ended_at'])
                          ->pluck('id')
                );
            });
        });
    }

    /**
     * Retrieve the courses related to this absence
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function courses() : BelongsToMany
    {
        return $this->belongsToMany(Course::class);
    }

    /**
     * Retrieve the student of this absence
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Retrieve the training related to this absence
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function training() : BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    /**
     * Color for a given duration
     *
     * @param  int      $duration
     * @param  string   $default
     * @return string
     */
    public static function color($duration, $default = 'secondary')
    {
        if ($duration >= 40 * 60) {
            return 'danger';
        }

        if ($duration >= 20 * 60) {
            return 'warning';
        }

        return $default;
    }
}
