<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class CustomUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $model = $this->createModel();

        return $this->newModelQuery($model)
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
           (count($credentials) === 1 &&
            str_contains($this->firstCredentialKey($credentials), 'password'))) {
            return;
        }

        // Convertir las credenciales a los nombres de columna correctos
        $mappedCredentials = [];
        foreach ($credentials as $key => $value) {
            if ($key === 'email') {
                $mappedCredentials['user_email'] = $value;
            } elseif ($key === 'password') {
                $mappedCredentials['user_password'] = $value;
            } else {
                $mappedCredentials[$key] = $value;
            }
        }

        $query = $this->newModelQuery();

        foreach ($mappedCredentials as $key => $value) {
            if (str_contains($key, 'password')) {
                continue;
            }

            $query->where($key, $value);
        }

        return $query->first();
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        if (! isset($credentials['password'])) {
            return false;
        }

        $plain = $credentials['password'];

        return $this->hasher->check($plain, $user->getAuthPassword());
    }
}
