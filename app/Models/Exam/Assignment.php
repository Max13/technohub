<?php

namespace App\Models\Exam;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Assignment extends Model
{
    use HasFactory;

    /** @inheritdoc */
    protected $table = 'exam_assignments';

    /** @inheritdoc */
    protected $fillable = [
        'order',
        'valid_at',
        'valid_until',
        'started_at',
        'ended_at',
        'duration',
    ];

    /** @inheritdoc */
    protected $casts = [
        'order' => 'array',
        'valid_at' => 'datetime',
        'valid_until' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration' => 'integer',
    ];

    /** @inheritdoc */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!isset($attributes['uuid'])) {
            $this->uuid = Str::orderedUuid()->toString();
        }
        if (!isset($attributes['group_uuid'])) {
            $this->group_uuid = Str::orderedUuid()->toString();
        }
    }

    /**
     * Return the answers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers() : HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Return the exam
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exam() : BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Return the next unanswered question
     *
     * @return \App\Models\Exam\Question|null
     */
    public function nextQuestion() : ?Question
    {
        $this->loadMissing('answers');

        $unansweredIds = array_values(array_diff($this->order, $this->answers->pluck('question_id')->all()));

        if (count($unansweredIds) === 0) {
            return null;
        }

        return Question::firstWhere('id', $unansweredIds[0]);
    }

    /**
     * Return the user assigned
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Return if the exam can be started by the user
     *
     * @return bool
     */
    public function getIsValidAttribute()
    {
        return ($this->valid_at ? $this->valid_at->isPast() : true)
            && ($this->valid_until ? $this->valid_until->isFuture() : true);
    }

    /**
     * Return if the exam was started by the user
     *
     * @return bool
     */
    public function getIsStartedAttribute()
    {
        return isset($this->started_at);
    }

    /**
     * Return if the exam was completed by the user
     *
     * @return bool
     */
    public function getIsFinishedAttribute()
    {
        return isset($this->ended_at);
    }

    /**
     * Returns and store duration attribute
     *
     * @return int
     */
    public function getDurationAttribute()
    {
        if (
            is_null($this->attributes['duration'])
         && isset($this->started_at, $this->ended_at)
        ) {
            $duration = $this->started_at->diffInMinutes($this->ended_at);

            $this->update(['duration' => $duration]);
        }

        return $this->attributes['duration'];
    }

    /**
     * Returns points attribute over /20
     *
     * @return int
     */
    public function getPointsAttribute()
    {
        $points = $this->raw_points;

        $this->exam->loadSum('questions', 'points');

        return round($points / $this->exam->questions_sum_points * 20, 2);
    }

    /**
     * Returns raw points attribute
     *
     * @return int
     */
    public function getRawPointsAttribute()
    {
        if (!$this->is_valid || !$this->is_finished) {
            return null;
        }

        $this->loadMissing('answers.question');

        $points = 0;

        foreach ($this->answers as $answer) {
            if ($answer->is_correct === true) {
                $points += $answer->question->points;
            }
        }

        return $points;
    }
}
