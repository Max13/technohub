<?php

namespace App\Models;

use App\Models\Marking\Criterion;
use App\Models\Marking\Point;
use App\Services\Ypareo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships as HasDeepRelationships;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
    use HasDeepRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'is_staff',
        'is_student',
        'is_trainer',
        'ypareo_id',
        'ypareo_login',
        'ypareo_uuid',
        'ypareo_sso',
        'firstname',
        'lastname',
        'email',
        'password',
        'training_id',
        'birthdate',
        'last_logged_in_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'fullname',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_staff' => 'boolean',
        'is_student' => 'boolean',
        'is_trainer' => 'boolean',
        'ypareo_id' => 'integer',
        'email_verified_at' => 'datetime',
        'training_id' => 'integer',
        'birthdate' => 'date',
        'last_logged_in_at' => 'date',
    ];

    /**
     * Retrieve user's absences
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function absences() : HasMany
    {
        return $this->hasMany(Absence::class);
    }

    /**
     * Retrieve user's classroom
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function classrooms() : BelongsToMany
    {
        return $this->belongsToMany(Classroom::class);
    }

    /**
     * Retrieve user's current classroom
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function currentClassroom() : HasOneThrough
    {
        return $this->hasOneThrough(Classroom::class, ClassroomUser::class, 'user_id', 'id', 'id', 'classroom_id')
                    ->where('year', substr(app(Ypareo::class)->getCurrentPeriod()['dateDeb'], -4))
                    ->latest('id');
    }

    /**
     * Retrieve user's courses
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function courses() : BelongsToMany
    {
        return $this->belongsToMany(Course::class);
    }

    /**
     * Retrieve user's roles
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles() : BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Retrieve user's trainings
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function trainings() : HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->classrooms(), (new Classroom)->training());
    }

    /**
     * Retrieve user's classroom
     *
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep
     */
    public function currentTraining() : HasOneDeep
    {
        return $this->hasOneDeepFromRelations($this->currentClassroom(), (new Classroom)->training());
    }

    /**
     * Query user's criteria
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function markingCriteria() : HasMany
    {
        return $this->hasMany(Criterion::class);
    }

    /**
     * Query user's points
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function points() : HasMany
    {
        return $this->hasMany(Point::class, 'student_id');
    }

    /**
     * Get full name
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function getFullnameAttribute()
    {
        return "$this->firstname $this->lastname";
    }
}
