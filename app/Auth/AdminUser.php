<?php

namespace App\Auth;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\GenericUser;

class AdminUser extends GenericUser implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin';
    }

    public function getKey(): mixed
    {
        return $this->getAuthIdentifier();
    }

    public function getKeyName(): string
    {
        return 'id';
    }

    public function getAttributeValue(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->getAttributeValue($key);
    }

    public function receivesBroadcastNotificationsOn(): string
    {
        $userClass = str_replace('\\', '.', static::class);

        return "{$userClass}.{$this->getKey()}";
    }
}