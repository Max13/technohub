<?php

namespace App\Models\Accounting;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    /** @inheritdoc  */
    protected $table = 'accounting_transactions';

    /** @inheritdoc */
    protected $fillable = [
        'student_id',
        'student_firstname',
        'student_lastname',
        'staff_id',
        'type',
        'amount',
        'label',
        'student_status',
        'rejection_status',
        'note',
        'year',
        'created_at',
    ];

    /** @inheritdoc */
    protected $casts = [
        'type' => TransactionType::class,
        'amount' => 'float',
        'student_status' => StudentStatus::class,
        'rejection_status' => TransactionStatus::class,
        'year' => 'integer',
    ];

    /**
     * Staff creating this transaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff() : BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    /**
     * Student related to this transaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student() : BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'firstname' => $this->student_firstname,
            'lastname' => $this->student_lastname,
        ]);
    }
}
