<?php

namespace App\Imports;

use App\Models\Accounting\StudentStatus;
use App\Models\Accounting\Transaction;
use App\Models\Accounting\TransactionStatus;
use App\Models\Accounting\TransactionType;
use App\Models\User;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TransactionsImport implements WithMultipleSheets
{
    /** @inheritdoc */
    public function sheets(): array
    {
        return [
            '24-25' => new class implements ToModel, SkipsEmptyRows, WithStartRow
            {
                /** @inheritdoc */
                public function model(array $row)
                {
                    $student = User::where('lastname', 'like', explode(' ', $row[2])[0] . '%')
                                   ->where('firstname', 'like', explode(' ', $row[3])[0] . '%')
                                   ->firstOrNew(
                                       [],
                                       [
                                           'lastname' => $row[3],
                                           'firstname' => $row[2],
                                       ]
                                   );

                    $date = Date::excelToDateTimeObject($row[1]);

                    if ($row[8] === 'Inscription' && $student->exists) {
                        $lastClassroom = $student->classrooms()->withTrashed()->latest()->first() ?? null;
                        $lastTraining = $lastClassroom?->training()->withTrashed()->first() ?? null;
                        $amount = 0;

                        if ($lastTraining && $lastTraining->price && $lastTraining->npec_max) {
                            $amount = Str::endsWith($lastClassroom->shortname, '-ALT') ? $lastTraining->npec_max : $lastTraining->price;
                        }

                        Transaction::create([
                            'student_id' => $student->id,
                            'student_firstname' => null,
                            'student_lastname' => null,
                            'staff_id' => null,
                            'type' => TransactionType::UNKNOWN,
                            'amount' => -$amount,
                            'label' => __('Training'),
                            'student_status' => StudentStatus::OK,
                            'rejection_status' => TransactionStatus::OK,
                            'note' => null,
                            'year' => 2024,
                            'created_at' => $date,
                        ]);
                    }

                    return new Transaction([
                        'student_id' => $student->exists ? $student->id : null,
                        'student_firstname' => $student->exists ? null : $student->firstname,
                        'student_lastname' => $student->exists ? null : $student->lastname,
                        'staff_id' => null,
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
                        'student_status' => match (strtolower($row[10])) {
                            'ok', '' => StudentStatus::OK,
                            'attente visa' => StudentStatus::VISA_PENDING,
                            'refusé' => StudentStatus::REFUSED,
                            'e-learning' => StudentStatus::ELEARNING,
                            'annulé' => StudentStatus::CANCELED,
                            'revendu' => StudentStatus::TRANSFERRED_AWAY,
                        },
                        'rejection_status' => strtolower($row[0]) === 'impayé' ? TransactionStatus::MISSED : TransactionStatus::OK,
                        'note' => $row[13],
                        'year' => 2025,
                        'created_at' => $date,
                    ]);
                }

                public function isEmptyWhen(array $row): bool
                {
                    return empty($row[1]) || empty($row[2]);
                }

                public function startRow(): int
                {
                    return 3;
                }
            }
        ];
    }
}
