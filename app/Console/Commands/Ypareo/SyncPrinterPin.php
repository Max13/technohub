<?php

namespace App\Console\Commands\Ypareo;

use App\Models\User;
use App\Services\ActiveDirectory;
use App\Services\Ypareo;
use Illuminate\Console\Command;

class SyncPrinterPin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ypareo:sync:printerpin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Up sync trainer\'s printer PIN to Ypareo';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ActiveDirectory $adService, Ypareo $ypareo)
    {
        // Ypareo trainers
        $trainers = User::whereRelation('roles', 'name', 'Trainer')
                        ->select([
                            'id',
                            'ypareo_id',
                            'email',
                        ])
                        ->get()
                        ->keyBy('email');

        // Prepare AD query
        $adTrainersQuery = $adService->connection()
                                     ->query()
                                     ->select(['mail', 'printerPin']);

        // Add Ypareo trainers to the AD query, by email
        $trainers->each(function ($trainer) use ($adTrainersQuery) {
            $adTrainersQuery->orWhere('mail', '=', $trainer->email);
        });

        foreach ($adTrainersQuery->get() as $adTrainer) {
            if (!isset($adTrainer['printerpin'])) {
                continue;
            }

            $trainers[$adTrainer['mail'][0]]->printerPin = $adTrainer['printerpin'][0];
        }

        // Retrieve trainers' custom data id from ypareo
        $trainers = $trainers->keyBy('ypareo_id');
        $ypareo->getTrainerCustomData($trainers->pluck('ypareo_id')->toArray())
               ->each(function ($customData) use ($trainers) {
                   if ($customData['nomRubrique'] === 'Code Imprimantes') {
                       $trainers[$customData['codePersonnel']]->printerDataId = $customData['codeRubrique'];
                       $trainers[$customData['codePersonnel']]->printerDataName = 'Code Imprimantes';
                       $trainers[$customData['codePersonnel']]->printerDataEntityId = $customData['codeRubDetailEntite'];
                       $trainers[$customData['codePersonnel']]->printerDataValueId = $customData['valeur']['codeValeur'];
                       $trainers[$customData['codePersonnel']]->printerDataValueName = $customData['valeur']['nomValeur'];
                   }
               });

        $this->withProgressBar($trainers, function ($trainer) use ($ypareo) {
            $ypareo->setTrainerCustomData(
                $trainer->ypareo_id,
                $trainer->printerDataId,
                $trainer->printerDataName,
                $trainer->printerDataEntityId,
                $trainer->printerDataValueId,
                $trainer->printerDataValueName,
                'observation',
                $trainer->printerPin,
            );
        });

        return 0;
    }
}
