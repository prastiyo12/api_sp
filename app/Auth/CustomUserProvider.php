<?php 

namespace App\Auth;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\Authenticatable as UserContract; 
use Illuminate\Auth\EloquentUserProvider as UserProvider;

class CustomUserProvider extends UserProvider
{
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
            array_key_exists('secret_key', $credentials))) {
            return;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->newModelQuery();
        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'secret_key')) {
                continue;
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['secret_key'];
       
        return $this->hasher->check($plain, $user->getAuthPassword());
    }
}