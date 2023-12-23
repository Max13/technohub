<?php

namespace App\Services\Mikrotik;

use Illuminate\Support\Facades\Http;

/**
 * MikroTik hotspot service
 *
 * TODO: Group everything related to MikroTik ?
 */
class Hotspot
{
    /**
     * @var string
     */
    protected $baseUrl = null;

    /**
     * @var string
     */
    protected $username = null;

    /**
     * @var string
     */
    protected $password = null;

    /**
     * Construct Ypareo service
     *
     * @param string $baseUrl
     * @param string $username
     * @param string $password
     */
    public function __construct($baseUrl, $username, $password)
    {
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param  string $hs
     * @param  string $username
     * @param  string $password
     * @param  string $comment
     * @return bool
     */
    public function createUser($hs, $username, $password, $comment = null): bool
    {
        $response = Http::withBasicAuth(
            $this->username,
            $this->password,
        )->put(
            $this->baseUrl . '/ip/hotspot/user',
            [
                'server' => $hs,
                'name' => $username,
                'password' => $password,
                'comment' => $comment,
            ],
        );

        return $response->successful();
    }

    /**
     * Get a hotspot's users
     *
     * @param  string $hs
     * @return \Illuminate\Support\Collection
     */
    public function getUsers($hs): \Illuminate\Support\Collection
    {
        $response = Http::withBasicAuth(
            $this->username,
            $this->password,
        )->get($this->baseUrl . '/ip/hotspot/user?server=' . $hs);

        return $response->collect();
    }

    /**
     * Remove a hotspot's user
     *
     * @param  string $userId
     * @return bool
     */
    public function removeUser($userId): bool
    {
        $response = Http::withBasicAuth(
            $this->username,
            $this->password,
        )->delete($this->baseUrl . '/ip/hotspot/user/' . $userId);

        return $response->successful();
    }
}
