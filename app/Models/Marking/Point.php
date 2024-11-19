<?php

namespace App\Models\Marking;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Point extends Model
{
    use HasFactory;

    /** @inheritdoc */
    protected $table = 'marking_points';

    /** @inheritdoc */
    protected $fillable = [
        'points',
        'notes',
    ];

    /**
     * Query the criterion for the points
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function criterion() : BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }

    /**
     * Query the staff giving/removing points
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Query the student being added/removed points
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
