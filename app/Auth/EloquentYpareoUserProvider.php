<?php

namespace App\Auth;

use App\Services\Ypareo;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class EloquentYpareoUserProvider extends EloquentUserProvider
{
    /** @var \App\Services\Ypareo */
    protected Ypareo $ypareo;

    /**
     * Construct the provider, adding Ypareo service.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param                                       $model
     * @param  \App\Services\Ypareo                 $ypareo
     * @return void
     *
     * @overrides
     */
    public function __construct(HasherContract $hasher, $model, Ypareo $ypareo)
    {
        $this->ypareo = $ypareo;

        parent::__construct($hasher, $model);
    }

    /** @inheritDoc */
    public function validateCredentials(UserContract $user, array $credentials): bool
    {
        if (parent::validateCredentials($user, $credentials)) {
            return true;
        }

        $ypareoUser = $this->ypareo->retrieve($credentials['ypareo_login']);
        $user->forceFill([
                 'password' => $ypareoUser['PASSWORD_UTILISATEUR_CRYPTE'],
             ])
             ->save();

        return parent::validateCredentials($user, $credentials);
    }
}
