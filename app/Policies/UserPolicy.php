<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function view(User $user, User $model): bool // Renommé $User en $model pour éviter le conflit de nom
    {
        // Admin peut voir n'importe quel utilisateur.
        // Tout utilisateur peut voir son propre profil.
        return $user->hasRole('Admin') || ($user->id === $model->id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function update(User $user, User $model): bool
    {
        // Admin peut modifier n'importe quel utilisateur.
        // Un utilisateur peut modifier son propre profil (via Fortify/Jetstream ou MyProfile page).
        return $user->hasRole('Admin') || ($user->id === $model->id);
    }

    public function delete(User $user, User $model): bool
    {
        // Opération très restreinte, l'archivage est souvent préféré.
        return $user->hasRole('Admin');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('Admin');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasRole('Admin');
    }
}
