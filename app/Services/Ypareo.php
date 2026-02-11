<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use TypeError;

/**
 * Ypareo service
 */
class Ypareo
{
    /**
     * @var string
     */
    protected $apiKey = null;

    /**
     * @var string
     */
    protected $baseUrl = null;

    /**
     * Construct Ypareo service
     *
     * @param string $apiKey
     * @param string $baseUrl
     */
    public function __construct($apiKey, $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Check a user credentials against Ypareo
     *
     * @param  string $username
     * @param  string $password
     * @param  string $userAgent
     * @return bool
     */
    public function auth($username, $password, $userAgent, $returnInDev = true): bool
    {
        if (!app()->environment('production') && !class_exists(\Mx\Ypareo\Auth::class)) {
            return $returnInDev;
        }

        return \Mx\Ypareo\Auth::check($this->baseUrl, $username, $password, $userAgent);
    }

    /**
     * Get active employee users
     *
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    protected function getEmployeeUsers()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->apiKey,
        ])->get($this->baseUrl . '/r/v1/utilisateur/personnels');

        $response->throw();

        return $response->collect();
    }

    /**
     * Get active student users
     *
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    protected function getStudentUsers()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->apiKey,
        ])->get($this->baseUrl . '/r/v1/utilisateur/apprenants');

        $response->throw();

        return $response->collect();
    }

    /**
     * Get active users
     *
     * @param  bool  $cached  Return cached response. Defaults to true.
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getUsers($cached = true)
    {
        $cacheKey = 'ypareo:'.__FUNCTION__;
        if (!$cached) {
            cache()->forget($cacheKey);
        }

        $users = cache()->remember($cacheKey, config('services.ypareo.cache.expiration'), function () {
            return $this->getEmployeeUsers()->merge($this->getStudentUsers());
        });

        return $users->map(function ($user) {
            return [
                'is_staff' => ($user['isAdministratif'] ?? false) == 1,
                'is_student' => ($user['typeUtilisateur'] ?? null) == 'APPRENANT',
                'is_trainer' => ($user['isFormateur'] ?? false) == 1,
                'ypareo_id' => $user['codePersonnel'] ?? $user['codeApprenant'],
                'ypareo_login' => $user['login'],
                'ypareo_uuid' => $user['uuidNetUtilisateur'],
                'ypareo_sso' => $user['identifiantSso'],
                'lastname' => $user['nom'],
                'firstname' => $user['prenom'],
                'email' => $user['email'],
                'birthdate' => $user['dateNaissance'] ? Carbon::createFromFormat('d/m/Y', $user['dateNaissance'])->startOfDay() : null,
                'last_logged_in_at' => $user['dateDerniereConnexion'] ? Carbon::createFromFormat('d/m/Y', $user['dateDerniereConnexion'])->startOfDay() : null,
            ];
        });
    }

    /**
     * Get school periods
     *
     * @param  bool  $cached  Return cached response. Defaults to true.
     * @return \Illuminate\Support\Collection|array{codePeriode: int, nomPeriode: string, dateDeb: string, dateFin: string}[]
     * @throws \Illuminate\Http\Client\RequestException
     */
    protected function getPeriods($cached = true)
    {
        $cacheKey = 'ypareo:'.__FUNCTION__;
        if (!$cached) {
            cache()->forget($cacheKey);
        }

        return cache()->remember($cacheKey, config('services.ypareo.cache.expiration'), function () {
            return Http::withHeaders([
                           'Accept' => 'application/json',
                           'Content-Type' => 'application/json',
                           'X-Auth-Token' => $this->apiKey,
                       ])
                       ->get($this->baseUrl . '/r/v1/periodes')
                       ->throw()
                       ->collect()
                       ->sortBy('codePeriode')
                       ->values();
        });
    }

    /**
     * Get current school period
     *
     * @return array{
     *             codePeriode: int,
     *             nomPeriode: string,
     *             dateDeb: string,    // Format: dd/mm/yyyy
     *             dateFin: string,    // Format: dd/mm/yyyy
     *         }
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getCurrentPeriod()
    {
        $today = today();

        $currentPeriod = $this->getPeriods()->firstOrFail(function ($p) use ($today) {
            return $today->betweenIncluded(
                Carbon::createFromFormat('d/m/Y', $p['dateDeb']),
                Carbon::createFromFormat('d/m/Y', $p['dateFin'])
            );
        });

        return $currentPeriod;
    }

    /**
     * Get all classrooms for the current period
     *
     * @param  bool  $cached  Return cached response. Defaults to true.
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getAllClassrooms($cached = true)
    {
        $cacheKey = 'ypareo:'.__FUNCTION__;
        if (!$cached) {
            cache()->forget($cacheKey);
        }

        return cache()->remember($cacheKey, config('services.ypareo.cache.expiration'), function () {
            return Http::withHeaders([
                           'Accept' => 'application/json',
                           'Content-Type' => 'application/json',
                           'X-Auth-Token' => $this->apiKey,
                       ])
                       ->get($this->baseUrl . '/r/v1/formation-longue/groupes', [
                           'codesPeriode' => $this->getCurrentPeriod()['codePeriode'],
                       ])
                       ->throw()
                       ->collect();
        });
    }

    /**
     * Get students in a given classroom
     *
     * @param  \App\Models\Classroom  $classroom
     * @param bool                    $cached    Return cached response.
     *                                           Defaults to true.
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getClassroomsStudents(Classroom $classroom, $cached = true)
    {
        $cacheKey = 'ypareo:'.__FUNCTION__.':'.$classroom->ypareo_id;
        if (!$cached) {
            cache()->forget($cacheKey);
        }

        return cache()->remember($cacheKey, config('services.ypareo.cache.expiration'), function () use ($classroom) {
            return Http::withHeaders([
                           'Accept' => 'application/json',
                           'Content-Type' => 'application/json',
                           'X-Auth-Token' => $this->apiKey,
                       ])
                       ->get($this->baseUrl . "/r/v1/groupes/{$classroom->ypareo_id}/apprenants")
                       ->throw()
                       ->collect();
        });
    }

    /**
     * Get classrooms for a given trainer
     *
     * @param \App\Models\User  $trainer
     * @param bool              $cached  Return cached response.
     *                                   Defaults to true.
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getTrainersClassrooms(User $trainer, $cached = true)
    {
        throw_if($trainer->is_trainer == false, TypeError::class, 'First argument ($trainer) must be a trainer');

        $cacheKey = 'ypareo:'.__FUNCTION__.':'.$trainer->ypareo_id;
        if (!$cached) {
            cache()->forget($cacheKey);
        }

        $classrooms = cache()->remember($cacheKey, config('services.ypareo.cache.expiration'), function () use ($trainer) {
            return Http::withHeaders([
                           'Accept' => 'application/json',
                           'Content-Type' => 'application/json',
                           'X-Auth-Token' => $this->apiKey,
                       ])
                       ->get($this->baseUrl . '/r/v1/groupes-personnels/from-planning', [
                           'codesPeriode'   => $this->getCurrentPeriod()['codePeriode'],
                           'codesPersonnel' => $trainer->ypareo_id,
                       ])
                       ->throw()
                       ->collect();
        });

        return $this->getAllClassrooms()->whereIn('codeGroupe', $classrooms->pluck('codeGroupe'))->values();
    }

    /**
     * Get absences for a given classroom
     *
     * @param  \Illuminate\Support\Carbon $startDate
     * @param  \Illuminate\Support\Carbon $endDate
     * @param  bool                       $cached    Return cached response.
     *                                               Defaults to true.
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getAllAbsences(Carbon $startDate = null, Carbon $endDate = null, $cached = true)
    {
        $currentPeriod = $this->getCurrentPeriod();
        $startDate = $startDate ?? Carbon::createFromFormat('d/m/Y', $currentPeriod['dateDeb']);
        $endDate = $endDate ?? Carbon::createFromFormat('d/m/Y', $currentPeriod['dateFin']);

        $cacheKey = 'ypareo:'.__FUNCTION__.':'.$startDate->toDateString().':'.$endDate->toDateString();
        if (!$cached) {
            cache()->forget($cacheKey);
        }

        return cache()->remember($cacheKey, config('services.ypareo.cache.expiration'), function () use ($startDate, $endDate) {
            return Http::withHeaders([
                           'Accept' => 'application/json',
                           'Content-Type' => 'application/json',
                           'X-Auth-Token' => $this->apiKey,
                       ])
                       ->get($this->baseUrl . '/r/v1/absences/' . $startDate->format('d-m-Y') . '/' . $endDate->format('d-m-Y'))
                       ->throw()
                       ->collect();
        });
    }

    /**
     * Get absences for a given classroom
     *
     * @param  \App\Models\Classroom           $classroom
     * @param  \Illuminate\Support\Carbon|null $startDate
     * @param  \Illuminate\Support\Carbon|null $endDate
     * @param  bool                            $cached    Return cached response.
     *                                                    Defaults to true.
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getClassroomsAbsences(Classroom $classroom, Carbon $startDate = null, Carbon $endDate = null, $cached = true)
    {
        $currentPeriod = $this->getCurrentPeriod();
        $startDate = $startDate ?? Carbon::createFromFormat('d/m/Y', $currentPeriod['dateDeb']);
        $endDate = $endDate ?? Carbon::createFromFormat('d/m/Y', $currentPeriod['dateFin']);

        $cacheKey = 'ypareo:'.__FUNCTION__.':'.$classroom->ypareo_id.':'.$startDate->toDateString().':'.$endDate->toDateString();
        if (!$cached) {
            cache()->forget($cacheKey);
        }

        return cache()->remember($cacheKey, config('services.ypareo.cache.expiration'), function () use ($classroom, $startDate, $endDate) {
            return Http::withHeaders([
                           'Accept' => 'application/json',
                           'Content-Type' => 'application/json',
                           'X-Auth-Token' => $this->apiKey,
                       ])
                       ->get($this->baseUrl . "/r/v1/groupes/{$classroom->ypareo_id}/absences", [
                           'dateDebut' => $startDate->format('d-m-Y'),
                           'dateFin' => $endDate->format('d-m-Y'),
                       ])
                       ->throw()
                       ->collect();
        });
    }

    /**
     * Get all courses, optionally filtered by classroom
     *
     * @param  \App\Models\Classroom           $classroom
     * @param  \Illuminate\Support\Carbon|null $startDate
     * @param  \Illuminate\Support\Carbon|null $endDate
     * @param  bool                            $cached    Return cached response.
     *                                                    Defaults to true.
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     *
     * @note   This method will timeout without a classroom
     */
    public function getCourses(Classroom $classroom = null, Carbon $startDate = null, Carbon $endDate = null, $cached = true)
    {
        $currentPeriod = $this->getCurrentPeriod();
        $startDate = $startDate ?? Carbon::createFromFormat('d/m/Y', $currentPeriod['dateDeb']);
        $endDate = $endDate ?? Carbon::createFromFormat('d/m/Y', $currentPeriod['dateFin']);

        $cacheKey = 'ypareo:'.__FUNCTION__.':'.optional($classroom)->ypareo_id.':'.$startDate->toDateString().':'.$endDate->toDateString();
        if (!$cached) {
            cache()->forget($cacheKey);
        }

        return cache()->remember($cacheKey, config('services.ypareo.cache.expiration'), function () use ($classroom, $startDate, $endDate) {
            $url = $this->baseUrl . '/r/v1/planning/'.$startDate->format('d-m-Y').'/'.$endDate->format('d-m-Y').'/groupes';

            if (!is_null($classroom)) {
                $url .= "/{$classroom->ypareo_id}";
            }

            return Http::withHeaders([
                           'Accept' => 'application/json',
                           'Content-Type' => 'application/json',
                           'X-Auth-Token' => $this->apiKey,
                       ])
                       ->get($url)
                       ->throw()
                       ->collect('cours');
        });
    }

    /**
     * Retrieve trainers custom data, optionally filtered by trainers and data id.
     *
     * @param  int[] $trainerIds If no ids are specified, returns all custom data for every trainer.
     * @param  int[] $dataIds    If no ids are specified, returns every custom data.
     *
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getTrainerCustomData(array $trainerIds = [], array $dataIds = [])
    {
        $httpRequst = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->apiKey,
        ]);

        if ($trainerIds === []) {
            $httpRequst = $httpRequst->get($this->baseUrl . '/r/v1/renseignements-parametres/structure/personnel');
        } else {
            $httpRequst = $httpRequst->get($this->baseUrl . '/r/v1/renseignements-parametres/personnels', [
                'codesPersonnel' => $trainerIds,
                'codesRubrique' => $dataIds,
            ]);
        }

        return $httpRequst->throw()
                          ->collect();
    }

    /**
     * Set trainer's custom data
     *
     * @param  int    $trainerId
     * @param  int    $dataId
     * @param  string $dataName
     * @param  int    $entityId
     * @param  int    $valueId
     * @param  string $valueName
     * @param  string $valueType One of: date, montant, nombre, observation, texteLibre
     * @param         $value
     *
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function setTrainerCustomData(
        int $trainerId,
        int $dataId,
        string $dataName,
        int $entityId,
        int $valueId,
        string $valueName,
        string $valueType,
        $value
    ) {
        $response = Http::withHeaders([
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'X-Auth-Token' => $this->apiKey,
                        ])
                        ->put($this->baseUrl . '/r/v1/renseignements-parametres/personnels', [
                            'codePersonnel' => $trainerId,
                            'codeRubrique' => $dataId,
                            'nomRubrique' => $dataName,
                            'codeRubDetailEntite' => $entityId,
                            'valeur' => [
                                'codeValeur' => $valueId,
                                'nomValeur' => $valueName,
                            ],
                            $valueType => $value,
                        ])
                        ->throw();

        return $response->successful();
    }

    /**
     * Get all trainings for the current period
     *
     * @param  bool $cached  Return cached response. Defaults to true.
     *
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getTrainings($cached = true)
    {
        $cacheKey = 'ypareo:'.__FUNCTION__;
        if (!$cached) {
            cache()->forget($cacheKey);
        }

        return cache()->remember($cacheKey, config('services.ypareo.cache.expiration'), function () {
            return Http::withHeaders([
                       'Accept' => 'application/json',
                       'Content-Type' => 'application/json',
                       'X-Auth-Token' => $this->apiKey,
                   ])
                   ->get($this->baseUrl . '/r/v1/formations')
                   ->throw()
                   ->collect();
        });
    }

    /**
     * Retrieve trainings custom data, filtered by trainings and optionnaly data id.
     *
     * @param  int[] $trainingIds Filter by training ids.
     * @param  int[] $dataIds     If no ids are specified, returns every custom data.
     * @param  bool  $cached      Return cached response. Defaults to true.
     *
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getTrainingsCustomData(array $trainingIds, array $dataIds = [], $cached = true)
    {
        sort($trainingIds);
        sort($dataIds);

        $cacheKey = 'ypareo:'.__FUNCTION__.':'.implode(',', $trainingIds).':'.implode(',', $dataIds);
        if (!$cached) {
            cache()->forget($cacheKey);
        }

        return cache()->remember($cacheKey, config('services.ypareo.cache.expiration'), function () use ($trainingIds, $dataIds) {
            $query = [
                'codesFormation' => $trainingIds,
            ];

            if ($dataIds !== []) {
                $query['codesRubrique'] = $dataIds;
            }

            return Http::withHeaders([
                           'Accept' => 'application/json',
                           'Content-Type' => 'application/json',
                           'X-Auth-Token' => $this->apiKey,
                       ])
                       ->get($this->baseUrl . '/r/v1/renseignements-parametres/formations', $query)
                       ->throw()
                       ->collect();
        });
    }

    /**
     * Retrieve a specific training's custom data, optionnaly filtered by data ids.
     *
     * @param  int[] $id      Training ids.
     * @param  int[] $dataIds If no ids are specified, returns every custom data.
     * @param  bool  $cached  Return cached response. Defaults to true.
     *
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getTrainingCustomData(int $id, array $dataIds = [], $cached = true)
    {
        sort($dataIds);

        $cacheKey = 'ypareo:'.__FUNCTION__.":$id:".implode(',', $dataIds);
        if (!$cached) {
            cache()->forget($cacheKey);
        }

        return cache()->remember($cacheKey, config('services.ypareo.cache.expiration'), function () use ($id, $dataIds) {
            return Http::withHeaders([
                           'Accept' => 'application/json',
                           'Content-Type' => 'application/json',
                           'X-Auth-Token' => $this->apiKey,
                       ])
                       ->get(
                           $this->baseUrl . "/r/v1/renseignements-parametres/formations/$id",
                           $dataIds === [] ?: ['codesRubrique' => $dataIds],
                       )
                       ->throw()
                       ->collect();
        });
    }
}
