<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships as HasDeepRelationships;

class Training extends Model
{
    use HasFactory;
    use HasDeepRelationships;
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
     * Retrieve training's courses through classrooms
     *
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep
     */
    public function courses() : HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->classrooms(), (new Classroom)->courses());
    }

    /**
     * Retrieve students of a given training
     *
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep
     */
    public function students() : HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
                        $this->classrooms(), (new Classroom)->users()
                    )
                    ->whereRelation('roles', 'name', 'Student');
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
        return $this->hasManyDeepFromRelations(
                        $this->classrooms(), (new Classroom)->users()
                    )
                    ->whereRelation('roles', 'name', 'Trainer');
    }
}
