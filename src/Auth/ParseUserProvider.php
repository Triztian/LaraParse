<?php

namespace LaraParse\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Parse\ParseException;
use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;

class ParseUserProvider implements UserProvider
{

    const PEX_OBJECT_NOT_FOUND = 101;

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|\LaraParse\Subclasses\User|null
     */
    public function retrieveById($identifier)
    {
        $query = new ParseQuery('_User');

        try {
            return $query->get($identifier, true);
        } catch( ParseException $pex) {
            // Only return null if it's the object not found exception
            if ( $pex->getCode() == self::PEX_OBJECT_NOT_FOUND ) {
                return null;
            } else {
                throw $pex;
            }
        }
    }

    /**
     * Retrieve a user by by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string $token
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|\LaraParse\Subclasses\User|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string                                     $token
     *
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        // Nothing
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|\LaraParse\Subclasses\User|null
     * @throws \Exception
     * @throws \Parse\ParseException
     */
    public function retrieveByCredentials(array $credentials)
    {
        $username = $this->getUsernameFromCredentials($credentials);

        $query = new ParseQuery('_User');
        $query->equalTo('username', $username);

        $user = $query->first(true);

        return empty($user) ? null : $user;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array                                      $credentials
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        try {
            $username = $this->getUsernameFromCredentials($credentials);

            /** @var ParseUser $userClass */
            $userClass = ParseObject::getRegisteredSubclass('_User');
            $userClass::logIn($username, $credentials['password']);

            return true;
        } catch (ParseException $error) {
            return false;
        }
    }

    /**
     * @param array $credentials
     *
     * @return mixed
     * @throws \Parse\ParseException
     */
    private function getUsernameFromCredentials(array $credentials)
    {
        if (array_key_exists('username', $credentials)) {
            return $credentials['username'];
        } elseif (array_key_exists('email', $credentials)) {
            return $credentials['email'];
        } else {
            throw new ParseException('$credentials must contain either a "username" or "email" key');
        }
    }
}
