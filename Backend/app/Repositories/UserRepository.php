<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Agent;
use App\Models\MunicipalAdmin;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Enums\UserStatus;
use App\Traits\Paginatable;



class UserRepository implements UserRepositoryInterface
{
        use Paginatable;

    /**
     * Récupérer tous les utilisateurs
     */
    public function getAll(): Collection
    {
        return User::with(['category:id,name', 'municipality:id, name'])->get();
    }
     /**
     * Récupérer tous les utilisateurs paginés
     *
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    public function getAllPaginated(int $perPage = 10, int $page = 1): array
    {
        $query = User::with(['category:id,name', 'municipality:id,name']);
 
        return $this->paginateQuery($query, $perPage, $page);
    }

    /**
     * Trouver un utilisateur par son ID
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Créer un utilisateur
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(User $user, array $data): User
    {
        // $user->update($data);
        // // return $user->fresh()->load('category', 'municipality');
        // return $user->refresh()->load('category', 'municipality');

         $user->update($data);
    $user->loadMissing(['category:id,name','municipality:id,name']); // charge seulement si pas déjà chargé
    return $user;

    }

    /**
     * Supprimer un utilisateur (avec son avatar si existant)
     */
    public function delete(User $user): void
    {
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();
    }

    /**
     * Récupérer les agents d'une municipalité
     */
    public function getMunicipalityUsers(int $municipalityId): Collection
    {
        return User::where('municipality_id', $municipalityId)
            ->with([ 'category'])
            ->get();
    }

  /**
     * Récupérer les agents d'une municipalité paginés
     *
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    public function getMunicipalityUsersPaginated(int $municipalityId, int $perPage = 10, int $page = 1): array
    {
        $query = User::where('municipality_id', $municipalityId)
            ->where('role', 'agent')
            ->with(['category:id,name']);

        return $this->paginateQuery($query, $perPage, $page);
    }
    

    /**
     * Return the municipal admin related to a given user id.
     *
     * Usage: helper used by services that need to resolve the admin
     * responsible for a given user (e.g. assignments or notifications).
     *
     * @param int $userId
     * @return MunicipalAdmin|null
     */
    public function getMunicipalAdminByUserId(int $userId): ?MunicipalAdmin
    {
        return User::find($userId);
    }
}