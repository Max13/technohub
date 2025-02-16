<?php

namespace App\Models;

use App\Models\Exam\Assignment;
use App\Models\Exam\Question;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property      bool                                                        is_public
 * @property      string                                                      name
 * @property-read \Illuminate\Support\Collection<\App\Models\Exam\Assignment> assignments
 * @property-read \App\Models\User                                            author
 * @property-read \Illuminate\Support\Collection<\App\Models\Exam\Question    questions
 * @property-read \Illuminate\Support\Collection<\App\Models\User>            assignees
 */
class Exam extends Model
{
    use HasFactory;

    /** @inheritdoc */
    protected $fillable = [
        'is_public',
        'name',
        'seb_config_file',
        'seb_config_key',
        'seb_exam_key',
    ];

    /** @inheritdoc */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /** @inheritdoc */
    protected $hidden = [
        'seb_config_file',
        'seb_config_key',
        'seb_exam_key',
    ];

    /** @inheritdoc */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->uuid = Str::orderedUuid()->toString();
    }

    /**
     * Return the assignees
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignees() : BelongsToMany
    {
        return $this->belongsToMany(User::class, 'exam_assignments');
    }

    /**
     * Return the assignments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignments() : HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Return the author
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Return the questions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions() : HasMany
    {
        return $this->hasMany(Question::class)->oldest('id');
    }
}
