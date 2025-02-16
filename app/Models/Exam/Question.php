<?php

namespace App\Models\Exam;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory;

    /** @var string */
    const TYPE_TRUEFALSE = 'TrueFalse';
    const TYPE_SINGLECHOICE = 'SingleChoice';
    const TYPE_MULTIPLECHOICE = 'MultipleChoice';
    const TYPE_OPEN = 'Open';

    /** @inheritdoc */
    protected $table = 'exam_questions';

    /** @inheritdoc */
    protected $fillable = [
        'question',
        'image',
        'answer1',
        'answer2',
        'answer3',
        'answer4',
        'valids',
        'duration',
        'points',
    ];

    /** @inheritdoc */
    protected $casts = [
        'valids' => 'array',
        'duration' => 'integer',
        'points' => 'integer',
    ];

    /** @inheritdoc */
    protected $hidden = [
        'valids',
    ];

    /** @var string[] */
    public static $colors = [
        '#aaffc3', // mint
        '#42d4f4', // cyan
        '#dcbeff', // lavender
        '#66ccff', // blue
        '#fabed4', // pink
        '#fffac8', // beige
        '#ffd8b1', // apricot
    ];

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
     * Return possible answers
     *
     * @return array
     */
    public function getAnswersAttribute()
    {
        return collect([
                $this->answer1,
                $this->answer2,
                $this->answer3,
                $this->answer4,
        ])->filter();
    }

    /**
     * Return question type
     *
     * @return array
     */
    public function getTypeAttribute()
    {
        if (is_null($this->valids)) {
            return self::TYPE_OPEN;
        }

        if (count($this->answers) === 2) {
            return self::TYPE_TRUEFALSE;
        }

        if (count($this->valids) > 1) {
            return self::TYPE_MULTIPLECHOICE;
        }

        return self::TYPE_SINGLECHOICE;
    }
}
