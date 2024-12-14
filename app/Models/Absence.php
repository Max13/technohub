<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
