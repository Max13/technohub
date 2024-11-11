<?php

namespace App\Models\Marking;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Criterion extends Model
{
    use HasFactory;

    /** @inheritdoc */
    protected $table = 'marking_criteria';

    /** @inheritdoc */
    protected $fillable = [
        'name',
        'min_points',
        'max_points',
    ];

    /**
     * Query the author of the criterion
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
