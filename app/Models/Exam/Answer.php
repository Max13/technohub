<?php

namespace App\Models\Exam;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function Illuminate\Events\queueable;

class Answer extends Model
{
    use HasFactory;

    public const STATUS_OK = 'OK';
    public const STATUS_ONGOING = 'ONGOING';
    public const STATUS_ANSWERED = 'ANSWERED';
    public const STATUS_EXPIRED = 'EXPIRED';

    /** @inheritdoc */
    protected $table = 'exam_answers';

    /** @inheritdoc */
    protected $fillable = [
        'question_id',
        'status',
        'value',
        'is_correct',
    ];

    /** @inheritdoc */
    protected $casts = [
        'value' => 'array',
        'is_correct' => 'boolean',
    ];

    /** @inheritdoc */
    protected $appends = [
        'status',
    ];

    /**
     * Returns the parent Assignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignment() : BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Returns the parent Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question() : BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Return the status of this answer
     *
     * @return string
     */
    public function getStatusAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['status'] = self::STATUS_OK;
        }

        return $value;
    }

    /**
     * This answer is still in the time frame allowed by its duration
     *
     * @return bool
     */
    public function isExpired() : bool
    {
        if ($this->question->duration === 0) {
            return false;
        }

        return $this->created_at
                    ->copy()
                    ->addSeconds($this->question->duration + 3)
                    ->startOfSecond()
                    ->lessThan(now()->startOfSecond());
    }
}
