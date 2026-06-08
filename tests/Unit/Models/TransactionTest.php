<?php

namespace Unit\Models;

use App\Models\Bank\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_instanciate_from_camt053_for_missing_nb_of_transactions()
    {
        $bankTrx = [
            'Amt' => '2530.12',
            'CdtDbtInd' => 'CRDT',
            'Sts' => 'BOOK',
            'ValDt' => [
                'Dt' => '2026-01-01',
            ],
        ];

        $trx = Transaction::fromBankTransaction($bankTrx);

        $this->assertSame(1, $trx->nb_of_transactions);
    }

    public function test_instanciate_from_camt053_for_given_nb_of_transactions()
    {
        $bankTrx = [
            'Amt' => '2530.12',
            'CdtDbtInd' => 'CRDT',
            'Sts' => 'BOOK',
            'ValDt' => [
                'Dt' => '2026-01-01',
            ],
            'NtryDtls' => [
                'Btch' => [
                    'NbOfTxs' => 4,
                ],
            ],
        ];

        $trx = Transaction::fromBankTransaction($bankTrx);

        $this->assertSame($bankTrx['NtryDtls']['Btch']['NbOfTxs'], $trx->nb_of_transactions);
    }

    public function test_instanciate_from_camt053_for_positive_amount()
    {
        $bankTrx = [
            'Amt' => '2530.12',
            'CdtDbtInd' => 'CRDT',
            'Sts' => 'BOOK',
            'ValDt' => [
                'Dt' => '2026-01-01',
            ],
        ];

        $trx = Transaction::fromBankTransaction($bankTrx);

        $this->assertIsFloat($trx->amount);
        $this->assertEquals(2530.12, $trx->amount);
    }

    public function test_instanciate_from_camt053_for_negative_amount()
    {
        $bankTrx = [
            'Amt' => '2530.12',
            'CdtDbtInd' => 'DBIT',
            'Sts' => 'BOOK',
            'ValDt' => [
                'Dt' => '2026-01-01',
            ],
        ];

        $trx = Transaction::fromBankTransaction($bankTrx);

        $this->assertIsFloat($trx->amount);
        $this->assertEquals(-2530.12, $trx->amount);
    }

    public function test_instanciate_from_camt053_for_various_details()
    {
        $variousDetails = [
            '/LIB/COMCB01925 NB0001 TPE727275601' => 'COMCB01925 NB0001 TPE727275601',
            '/LIB/IMP TURPIN SOLIM SUR ORDRE DU /LIB/SCOLARITE ITIC PARIS' => 'TURPIN SOLIM SUR ORDRE DU / SCOLARITE ITIC PARIS',
            '/LIB/IMP BUISSON SARA V PROVISION INS/LIB/SCOLARITE ITIC PARIS' => 'BUISSON SARA V PROVISION INS / SCOLARITE ITIC PARIS',
            '/LIB/IMP DUFOUR KLU PAS D\'AUTORIS/LIB/SCOLARITE ITIC PARIS' => 'DUFOUR KLU PAS D\'AUTORIS / SCOLARITE ITIC PARIS',
            '/LIB/IMP BARRE AKOUVI CONTESTATION /LIB/SCOLARITE ITIC PARIS' => 'BARRE AKOUVI CONTESTATION / SCOLARITE ITIC PARIS',
            '/LIB/IMP MARTINEZ BISSELO SERVICE SPECI/LIB/SCOLARITE ITIC PARIS' => 'MARTINEZ BISSELO SERVICE SPECI / SCOLARITE ITIC PARIS',
            '/LIB/REMCB01925 NB0001 TPE727275601' => 'REMCB01925 NB0001 TPE727275601',
            '/LIB/VIR INST M. PAULIN MORIN /LIB/ETUDIANTE MIHEAYE KEZIA' => 'PAULIN MORIN / ETUDIANTE MIHEAYE KEZIA',
            '/LIB/VIR INST M. LAURY GERARD /LIB/SCOLARITE ITIC PARIS' => 'LAURY GERARD / SCOLARITE ITIC PARIS',
            '/LIB/VIR INST M.OU MME RUIZ SE' => 'RUIZ SE',
            '/LIB/VIR INST MLLE LUCAS AFI DELAL/LIB/FIDELE LUCAS FRAIS DE SCOLARI' => 'LUCAS AFI DELAL / FIDELE LUCAS FRAIS DE SCOLARI',
            '/LIB/VIR INST MADAME LEBON HAMKA E/LIB/FRAIS DE SCOLARITE ITIC' => 'LEBON HAMKA E / FRAIS DE SCOLARITE ITIC',
            '/LIB/VIR INST M CLEMENT GACHUESSI CHRI/LIB/FRAIS DE SCOLARITE MARS 2026 KU' => 'CLEMENT GACHUESSI CHRI / FRAIS DE SCOLARITE MARS 2026 KU',
            '/LIB/VIR M NELVAN MAILLARD-ROUSSET /LIB/SCOLARITE ILAMA THYROLE 2026' => 'NELVAN MAILLARD-ROUSSET / SCOLARITE ILAMA THYROLE 2026',
            '/LIB/VIR NOVA TRAD /LIB/REFERENCE NON TRANSMISE' => 'NOVA TRAD / REFERENCE NON TRANSMISE',
            '/LIB/VIR INST M HUBERT ANTOINE RENAUD' => 'HUBERT ANTOINE RENAUD',
            '/LIB/VIR INST MLLE KEVINE EPEE CAMUS/LIB/LE ROUX FRANCIS ERWIN BTS MOIS DE' => 'KEVINE EPEE CAMUS / LE ROUX FRANCIS ERWIN BTS MOIS DE',
            '/LIB/VIR INST MME LIRLANE HOARAU/LIB/FRAIS DE SCOLARITE' => 'LIRLANE HOARAU / FRAIS DE SCOLARITE',
            '/LIB/VIR INST M. DIVIN LECOQ /LIB/FRAIS D\'INSCRIPTION DE LECOQ K' => 'DIVIN LECOQ / FRAIS D\'INSCRIPTION DE LECOQ K',
            '/LIB/VIR INST MLLE VICTOIR PASQUIER DJ/LIB/VIREMENT DE MLLE VICTOIR PASQUIER' => 'VICTOIR PASQUIER DJ / VIREMENT DE MLLE VICTOIR PASQUIER',
            '/LIB/VIR L\'AGENCE DE SAINT-OUEN /LIB/20250063' => 'L\'AGENCE DE SAINT-OUEN / 20250063',
            '/LIB/VIR INST MONSIEUR MARTINEZ BISSELO' => 'MARTINEZ BISSELO',
            '/LIB/VIR INST MLLE KEVINE EPEE CAMUS/LIB/FRAIS DE SCOLARITE FEVRIER LE R' => 'KEVINE EPEE CAMUS / FRAIS DE SCOLARITE FEVRIER LE R',
            '/LIB/VIR ITIC /LIB/30066-12-26086345037880' => 'ITIC / 30066-12-26086345037880',
            '/LIB/VIR INST MONSIEUR COLAS SOSSO' => 'COLAS SOSSO',
            '/LIB/VIR INST ROSETTE PONS /LIB/VIREMENT DE ROSETTE PONS' => 'ROSETTE PONS / VIREMENT DE ROSETTE PONS',
            '/LIB/VOTRE REMISE PRELEVMT DU 010101/LIB/PREL FB ABILANGA NDOKI YDE ORLA' => 'PRELEVMT DU 010101 / ABILANGA NDOKI YDE ORLA',
            '/LIB/VOTRE REMISE PRELEVMT DU 010101/LIB/PREL FB TURPIN SOLIM' => 'PRELEVMT DU 010101 / TURPIN SOLIM',
            '/LIB/VOTRE REMISE PRELEVMT DU 010101/LIB/PREL FB PICHON MILO VIA PICHON ALEXAN' => 'PRELEVMT DU 010101 / PICHON MILO VIA PICHON ALEXAN',
            '/LIB/VOTRE REMISE PRELEVMT DU 010101/LIB/PREL FB NOEL DEDE MAWUSE' => 'PRELEVMT DU 010101 / NOEL DEDE MAWUSE',
            '/LIB/VOTRE REMISE PRELEVMT DU 010101/LIB/PREL FB ADJI ATEH' => 'PRELEVMT DU 010101 / ADJI ATEH',
            '/LIB/VOTRE REMISE PRELEVMT DU 010101/LIB/PREL FB BUISSON SAR VICTOIRE ABR' => 'PRELEVMT DU 010101 / BUISSON SAR VICTOIRE ABR',
            '/LIB/VOTRE REMISE PRELEVMT DU 010101/LIB/PREL FB ADOUKOE AFFOVI' => 'PRELEVMT DU 010101 / ADOUKOE AFFOVI',
            '/LIB/VOTRE REMISE PRELEVMT DU 010101/LIB/PREL FB AFANGNIBO DELA-DEM-DELA' => 'PRELEVMT DU 010101 / AFANGNIBO DELA-DEM-DELA',
            '/LIB/VOTRE REMISE PRELEVMT DU 010101/LIB/PREL FB ZAMBLE DJE LOU' => 'PRELEVMT DU 010101 / ZAMBLE DJE LOU',
            '/LIB/VOTRE REMISE PRELEVMT DU 010101/LIB/PREL FB CHEVALLIER' => 'PRELEVMT DU 010101 / CHEVALLIER',
        ];

        foreach ($variousDetails as $detail => $expected) {
            $this->assertEquals($expected, Transaction::cleanDetailsAttribute($detail));
        }
    }

    public function test_instanciate_from_camt053_for_details_as_details_is_null()
    {
        $this->assertNull(Transaction::cleanDetailsAttribute(null));
    }

    public function test_instanciate_from_camt053_for_1_related_parties()
    {
        $bankTrx = [
            'Amt' => '2530.12',
            'CdtDbtInd' => 'DBIT',
            'Sts' => 'BOOK',
            'ValDt' => [
                'Dt' => '2026-01-01',
            ],
            'NtryDtls' => [
                'TxDtls' => [
                    'RltdPties' => [
                        'Dbtr' => [
                            'Nm' => 'PONS ROSETTE',
                        ],
                        'Cdtr' => [
                            'Nm' => 'ITIC PARIS',
                        ],
                    ],
                ]
            ]
        ];

        $this->assertEquals(['PONS ROSETTE'], Transaction::fromBankTransaction($bankTrx)->related_parties);
    }

    public function test_instanciate_from_camt053_for_2_related_parties()
    {
        $bankTrx = [
            'Amt' => '2530.12',
            'CdtDbtInd' => 'DBIT',
            'Sts' => 'BOOK',
            'ValDt' => [
                'Dt' => '2026-01-01',
            ],
            'NtryDtls' => [
                'TxDtls' => [
                    'RltdPties' => [
                        'Dbtr' => [
                            'Nm' => 'MAWAMBA TEIKING FABIOLA VIA KEUFACK KENANG BAMEKA',
                        ],
                        'Cdtr' => [
                            'Nm' => 'ITIC PARIS',
                        ],
                    ],
                ]
            ]
        ];

        $this->assertEquals(['MAWAMBA TEIKING FABIOLA'], Transaction::fromBankTransaction($bankTrx)->related_parties);
    }

    public function test_instanciate_from_camt053_detects_1_related_parties_as_student()
    {
        $student = User::factory()->create([
            'firstname' => 'ROSETTE',
            'lastname' => 'PONS',
        ]);
        $bankTrx = [
            'Amt' => '2530.12',
            'CdtDbtInd' => 'DBIT',
            'Sts' => 'BOOK',
            'ValDt' => [
                'Dt' => '2026-01-01',
            ],
            'NtryDtls' => [
                'TxDtls' => [
                    'RltdPties' => [
                        'Dbtr' => [
                            'Nm' => 'PONS ROSETTE',
                        ],
                        'Cdtr' => [
                            'Nm' => 'ITIC PARIS',
                        ],
                    ],
                ]
            ]
        ];

        $this->assertEquals($student->id, Transaction::fromBankTransaction($bankTrx)->user_id);
    }

    public function test_instanciate_from_camt053_detects_1_related_parties_as_student_multiple_names_at_beginning()
    {
        $student = User::factory()->create([
            'firstname' => 'ROSETTE EVELYNE',
            'lastname' => 'PONS DURAND',
        ]);

        $bankTrx = [
            'Amt' => '2530.12',
            'CdtDbtInd' => 'DBIT',
            'Sts' => 'BOOK',
            'ValDt' => [
                'Dt' => '2026-01-01',
            ],
            'NtryDtls' => [
                'TxDtls' => [
                    'RltdPties' => [
                        'Dbtr' => [
                            'Nm' => 'PONS ROSETTE',
                        ],
                        'Cdtr' => [
                            'Nm' => 'ITIC PARIS',
                        ],
                    ],
                ]
            ]
        ];

        $this->assertEquals($student->id, Transaction::fromBankTransaction($bankTrx)->user_id);
    }

    public function test_instanciate_from_camt053_detects_1_related_parties_as_student_multiple_names_at_end()
    {
        $student = User::factory()->create([
            'firstname' => 'ROSETTE EVELYNE',
            'lastname' => 'PONS DURAND',
        ]);

        $bankTrx = [
            'Amt' => '2530.12',
            'CdtDbtInd' => 'DBIT',
            'Sts' => 'BOOK',
            'ValDt' => [
                'Dt' => '2026-01-01',
            ],
            'NtryDtls' => [
                'TxDtls' => [
                    'RltdPties' => [
                        'Dbtr' => [
                            'Nm' => 'DURAND EVELYNE',
                        ],
                        'Cdtr' => [
                            'Nm' => 'ITIC PARIS',
                        ],
                    ],
                ]
            ]
        ];

        $this->assertEquals($student->id, Transaction::fromBankTransaction($bankTrx)->user_id);
    }

    public function test_instanciate_from_camt053_detects_1_related_parties_multiple_names_as_student()
    {
        $student = User::factory()->create([
            'firstname' => 'ROSETTE EVELYNE',
            'lastname' => 'PONS DURAND',
        ]);

        $bankTrx = [
            'Amt' => '2530.12',
            'CdtDbtInd' => 'DBIT',
            'Sts' => 'BOOK',
            'ValDt' => [
                'Dt' => '2026-01-01',
            ],
            'NtryDtls' => [
                'TxDtls' => [
                    'RltdPties' => [
                        'Dbtr' => [
                            'Nm' => 'ROSETTE PONS',
                        ],
                        'Cdtr' => [
                            'Nm' => 'ITIC PARIS',
                        ],
                    ],
                ]
            ]
        ];

        $this->assertEquals($student->id, Transaction::fromBankTransaction($bankTrx)->user_id);
    }

    public function test_instanciate_from_camt053_detects_2_related_parties_as_1_student()
    {
        $student = User::factory()->create([
            'firstname' => 'FABIOLA',
            'lastname' => 'MAWAMBA TEIKING',
        ]);
        $bankTrx = [
            'Amt' => '2530.12',
            'CdtDbtInd' => 'DBIT',
            'Sts' => 'BOOK',
            'ValDt' => [
                'Dt' => '2026-01-01',
            ],
            'NtryDtls' => [
                'TxDtls' => [
                    'RltdPties' => [
                        'Dbtr' => [
                            'Nm' => 'MAWAMBA TEIKING FABIOLA VIA KEUFACK KENANG BAMEKA',
                        ],
                        'Cdtr' => [
                            'Nm' => 'ITIC PARIS',
                        ],
                    ],
                ]
            ]
        ];

        $this->assertEquals($student->id, Transaction::fromBankTransaction($bankTrx)->user_id);
    }

    public function test_instanciate_from_camt053_detects_2_related_parties_as_no_student()
    {
        $bankTrx = [
            'Amt' => '2530.12',
            'CdtDbtInd' => 'DBIT',
            'Sts' => 'BOOK',
            'ValDt' => [
                'Dt' => '2026-01-01',
            ],
            'NtryDtls' => [
                'TxDtls' => [
                    'RltdPties' => [
                        'Dbtr' => [
                            'Nm' => 'MAWAMBA TEIKING FABIOLA VIA KEUFACK KENANG BAMEKA',
                        ],
                        'Cdtr' => [
                            'Nm' => 'ITIC PARIS',
                        ],
                    ],
                ]
            ]
        ];

        $this->assertNull(Transaction::fromBankTransaction($bankTrx)->user_id);
    }
}
