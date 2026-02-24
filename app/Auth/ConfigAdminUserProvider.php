<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Arr;

class ConfigAdminUserProvider implements UserProvider
{
    /** @var array<int, string> */
    protected array $usernamesById = [];

    public function __construct(
        protected array $admins,
    ) {
        $counter = 1;

        foreach (array_keys($this->admins) as $username) {
            $this->usernamesById[$counter] = (string) $username;
            $counter++;
        }
    }

    public function retrieveById($identifier): ?Authenticatable
    {
        $adminId = (int) $identifier;
        $username = $this->usernamesById[$adminId] ?? null;

        if (! $username) {
            return null;
        }

        return $this->makeUser($username);
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token): ?Authenticatable
    {
        return $this->retrieveById($identifier);
    }

    public function updateRememberToken(Authenticatable $user, #[\SensitiveParameter] $token): void
    {
    }

    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials): ?Authenticatable
    {
        $username = (string) Arr::get($credentials, 'username');

        if (($username === '') || (! array_key_exists($username, $this->admins))) {
            return null;
        }

        return $this->makeUser($username);
    }

    public function validateCredentials(Authenticatable $user, #[\SensitiveParameter] array $credentials): bool
    {
        $username = (string) Arr::get($credentials, 'username');
        $password = (string) Arr::get($credentials, 'password');

        if (($username === '') || ($password === '') || (! array_key_exists($username, $this->admins))) {
            return false;
        }

        return hash_equals((string) $this->admins[$username], $password);
    }

    public function rehashPasswordIfRequired(Authenticatable $user, #[\SensitiveParameter] array $credentials, bool $force = false): void
    {
    }

    protected function makeUser(string $username): AdminUser
    {
        return new AdminUser([
            'id' => $this->getAdminIdByUsername($username),
            'username' => $username,
            'name' => $username,
            'email' => "{$username}@admin.local",
            'password' => $this->admins[$username],
            'remember_token' => null,
        ]);
    }

    protected function getAdminIdByUsername(string $username): int
    {
        foreach ($this->usernamesById as $adminId => $adminUsername) {
            if ($adminUsername === $username) {
                return $adminId;
            }
        }

        return 0;
    }
}