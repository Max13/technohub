<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Training\CustomData;
use App\Models\User;
use App\Services\Ypareo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AdministrativeController extends Controller
{
    /**
     * Get trainings data for the trainings table.
     *
     * @return array{
     *     trainings: \Illuminate\Support\Collection,
     *     trainers: \Illuminate\Support\Collection
     * }
     * @throws \Illuminate\Http\Client\RequestException
     */
    protected function getTrainingsData()
    {
        /** @var \Illuminate\Http\Request $request */
        $request = request();
        $data = $request->validate([
            'filters' => 'nullable|array',
            'filters.show-archived' => 'boolean',
            'filters.show-last-year' => 'boolean',
        ]);

        /** @var \App\Services\Ypareo $ypareo */
        $ypareo = app(Ypareo::class);

        $currentPeriod = $ypareo->getCurrentPeriod();
        $currentPeriod = [
            'from' => Carbon::createFromFormat('d/m/Y', $currentPeriod['dateDeb']),
            'to' => Carbon::createFromFormat('d/m/Y', $currentPeriod['dateFin'])->addYear(),
        ];

        if (($data['filters']['show-last-year'] ?? '0') === '1') {
            $currentPeriod['from']->subYear();
        }

        $ypareoTrainings = $ypareo->getTrainings()
                                  ->when(($data['filters']['show-archived'] ?? '0') === '0', function (Collection $c) {
                                      return $c->reject(function ($training) {
                                          return $training['plusUtilise'] === 1;
                                      });
                                  })
                                  ->when(($data['filters']['show-last-year'] ?? '0') === '0', function (Collection $c) use ($currentPeriod) {
                                      return $c->reject(function ($training) use ($currentPeriod) {
                                          $trainingPeriods = substr($training['abregeFormation'], 0, 4);

                                          if (!is_numeric($trainingPeriods)) {
                                              return false;
                                          }

                                          $trainingPeriods = [
                                              'from' => Carbon::createFromFormat('Y', substr($trainingPeriods, 0, 2)),
                                              'to' => Carbon::createFromFormat('Y', substr($trainingPeriods, 2)),
                                          ];

                                          return $trainingPeriods['from']->between(
                                                  $currentPeriod['from'], $currentPeriod['to']
                                              )
                                                 || $trainingPeriods['to']->between(
                                                  $currentPeriod['from'], $currentPeriod['to']
                                              );
                                      });
                                  })
                                  ->sortBy([
                                      ['plusUtilise', 'asc'],
                                      ['diplome.nomenclNiveau', 'asc'],
                                  ])
                                  ->keyBy('codeFormation');

        $trainers = User::whereIn(
                            'ypareo_id',
                            $ypareoTrainings->pluck('codePersonnel')->unique()
                        )
                        ->get()
                        ->keyBy('ypareo_id');

        $trainingsCustomDataList = collect(CustomData::cases())->pluck('value')->toArray();
        $ypareoTrainings->transform(function ($training) use ($ypareo, $trainers, $trainingsCustomDataList) {
            $training['headTeacher'] = optional($trainers[$training['codePersonnel']] ?? null)->fullname;
            $ypareo->getTrainingCustomData($training['codeFormation'], $trainingsCustomDataList)
                   ->each(function (array $customData) use (&$training) {
                       $training['customData'][$customData['nomRubrique']] = CustomData::value($customData);
                   });
            return $training;
        });

        return [
            'trainings' => $ypareoTrainings->values(),
            'trainers' => $trainers,
        ];
    }

    /**
     * Get the trainings table view.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getTrainingsTable()
    {
        return view('administrative.trainings', $this->getTrainingsData());
    }

    /**
     * Export the trainings table.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function exportTrainingsTable()
    {
        $today = Carbon::today();

        return response()->streamDownload(function () {
            echo view('administrative.trainings-csv', $this->getTrainingsData())->render();
        }, "Tableau-de-synthèse-des-formations_{$today->toDateString()}.csv");
    }
}
