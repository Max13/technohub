<?php

namespace App\Imports;

use App\Jobs\Accounting\MatchRelatedParties;
use App\Models\Accounting\DisputeType;
use App\Models\Accounting\StudentStatus;
use App\Models\Accounting\Transaction;
use App\Models\Accounting\TransactionOrigin;
use App\Models\Accounting\TransactionType;
use App\Models\User;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TransactionsImport implements WithMultipleSheets
{
    /** @inheritdoc */
    public function sheets(): array
    {
        return [
            '24-25' => new class implements ToModel, SkipsEmptyRows, WithCalculatedFormulas, WithStartRow
            {
                /** @inheritdoc */
                public function isEmptyWhen(array $row): bool
                {
                    return empty($row[1]) || empty($row[2]);
                }

                /** @inheritdoc */
                public function startRow(): int
                {
                    return 3;
                }

                /** @inheritdoc */
                public function model(array $row)
                {
                    // $date = Date::excelToDateTimeObject($row[1]);
                    // $student = [
                    //     'lastname' => $row[2],
                    //     'firstname' => $row[3],
                    // ];
                    //
                    // if ($row[8] === 'Inscription') {
                    //     $lastClassroom = $student->classrooms()->withTrashed()->latest()->first() ?? null;
                    //     $lastTraining = $lastClassroom?->training()->withTrashed()->first() ?? null;
                    //     $amount = 0;
                    //
                    //     if ($lastTraining && $lastTraining->price && $lastTraining->npec_max) {
                    //         $amount = Str::endsWith($lastClassroom->shortname, '-ALT') ? $lastTraining->npec_max : $lastTraining->price;
                    //     }
                    //
                    //     Transaction::create([
                    //         'student_id' => $student->id,
                    //         'student_firstname' => null,
                    //         'student_lastname' => null,
                    //         'staff_id' => null,
                    //         'type' => TransactionType::UNKNOWN,
                    //         'amount' => -$amount,
                    //         'label' => __('Training'),
                    //         'student_status' => StudentStatus::OK,
                    //         'rejection_status' => TransactionStatus::OK,
                    //         'note' => null,
                    //         'year' => 2024,
                    //         'created_at' => $date,
                    //     ]);
                    // }

                    $trx = new Transaction([
                        'origin' => TransactionOrigin::EXCEL,
                        'type' => value(function ($type) {
                            $type = strtolower($type);
                            if (Str::contains($type, 'cb')) {
                                return TransactionType::CREDIT_CARD;
                            }
                            if (Str::contains($type, 'virement')) {
                                return TransactionType::WIRE_TRANSFER;
                            }
                            if (is_int($type)) {
                                return TransactionType::CHECK;
                            }
                            return TransactionType::DIRECT_DEBIT;
                        }, $row[12]),
                        'amount' => - floatval($row[4]) + floatval($row[5]) - floatval($row[6]) + floatval($row[7]),
                        'label' => $row[8],
                        'details' => $row[13],
                        'is_queued' => true,
                        'dispute_type' => strtolower($row[0]) === 'impayé' ? DisputeType::UNKNOWN : null,
                        'related_parties' => [
                            [
                                'lastname' => $row[2],
                                'firstname' => $row[3]
                            ],
                        ],
                        'student_status' => match (strtolower($row[10])) {
                            'ok', '' => StudentStatus::OK,
                            'attente visa' => StudentStatus::VISA_PENDING,
                            'refusé' => StudentStatus::REFUSED,
                            'e-learning' => StudentStatus::ELEARNING,
                            'annulé' => StudentStatus::CANCELED,
                            'revendu' => StudentStatus::TRANSFERRED_AWAY,
                        },
                        'year' => 2025,
                        'created_at' => Date::excelToDateTimeObject($row[1]),
                    ]);

                    $trx->staff()->associate(
                        cache()->remember('admin_id', 60 * 5, function () {
                            return User::whereRelation('roles', 'name', 'Admin')
                                       ->firstOrFail()
                                       ->id;
                        })
                    );

                    $trx->save();

                    MatchRelatedParties::dispatchNow($trx);

                    return $trx;
                }
            }
        ];
    }
}
