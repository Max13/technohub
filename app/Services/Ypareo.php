<?php

namespace App\Services;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

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
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getUsers()
    {
        $users = [];

        $this->getEmployeeUsers()->each(function ($u) use (&$users) {
            $users[] = [
                'is_staff' => $u['isAdministratif'] == 1,
                'is_student' => false,
                'is_trainer' => $u['isFormateur'] == 1,
                'ypareo_id' => $u['codePersonnel'],
                'ypareo_login' => $u['login'],
                'lastname' => $u['nom'],
                'firstname' => $u['prenom'],
                'email' => $u['email'],
            ];
        });

        $this->getStudentUsers()->each(function ($u) use (&$users) {
            $users[] = [
                'is_staff' => false,
                'is_student' => true,
                'is_trainer' => false,
                'ypareo_id' => $u['codeApprenant'],
                'ypareo_login' => $u['login'],
                'lastname' => $u['nom'],
                'firstname' => $u['prenom'],
                'email' => $u['email'],
            ];
        });

        return collect($users);
    }

    /**
     * Get school periods
     *
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    protected function getPeriods()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->apiKey,
        ])->get($this->baseUrl . '/r/v1/periodes');

        $response->throw();

        return $response->collect();
    }

    /**
     * Get current school period
     *
     * @return \Illuminate\Support\Collection
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
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getAllClassrooms()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->apiKey,
        ])->get($this->baseUrl . '/r/v1/formation-longue/groupes', [
            'codesPeriode' => $this->getCurrentPeriod()['codePeriode'],
        ]);

        $response->throw();

        return $response->collect();
    }

    /**
     * Get students in a given classroom
     *
     * @param  $classroomId
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getClassroomsStudents($classroomId)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->apiKey,
        ])->get($this->baseUrl . "/r/v1/groupes/$classroomId/apprenants");

        $response->throw();

        return $response->collect();
    }

    /**
     * Get classrooms for a given employee
     *
     * @param  $employeeYpareoId
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getClassrooms($employeeYpareoId)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->apiKey,
        ])->get($this->baseUrl . '/r/v1/groupes-personnels/from-planning', [
            'codesPeriode' => $this->getCurrentPeriod()['codePeriode'],
            'codesPersonnel' => $employeeYpareoId,
        ]);

        $response->throw();

        return $this->getAllClassrooms()->whereIn('codeGroupe', $response->collect()->pluck('codeGroupe'))->values();
    }

    /**
     * Get absences for a given classroom
     *
     * @param  \Illuminate\Support\Carbon $startDate
     * @param  \Illuminate\Support\Carbon $endDate
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     *
     * @note Date filters doesn't work yet
     */
    public function getAllAbsences(Carbon $startDate = null, Carbon $endDate = null)
    {
        $currentPeriod = $this->getCurrentPeriod();
        $startDate = $startDate ?? Carbon::createFromFormat('d/m/Y', $currentPeriod['dateDeb']);
        $endDate = $endDate ?? Carbon::createFromFormat('d/m/Y', $currentPeriod['dateFin']);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->apiKey,
         ])->get($this->baseUrl . '/r/v1/absences/'.$startDate->format('d-m-Y').'/'.$endDate->format('d-m-Y'));

        $response->throw();

        return $response->collect();
    }

    /**
     * Get absences for a given classroom
     *
     * @param  int                        $classroomId
     * @param  \Illuminate\Support\Carbon $startDate
     * @param  \Illuminate\Support\Carbon $endDate
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     *
     * @note Date filters doesn't work yet
     */
    public function getClassroomsAbsences($classroomId, Carbon $startDate = null, Carbon $endDate = null)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->apiKey,
        ])->get($this->baseUrl . "/r/v1/groupes/$classroomId/absences", [
            'dateDeb' => ($startDate ?? today())->format('d-m-Y'),
            'dateFin' => ($endDate ?? today())->format('d-m-Y'),
        ]);

        $response->throw();

        return $response->collect();
    }
}
