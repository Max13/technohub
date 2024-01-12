<?php

namespace App\Services;

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
}
