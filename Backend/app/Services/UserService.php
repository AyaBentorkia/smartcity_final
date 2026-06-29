<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Models\Category;
use App\Models\Role;
use App\Models\Municipality;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Return all users.
     *
     * Called by: admin endpoints to list users.
     * Endpoint: GET /admin/users (or similar)
     * Allowed users: super admin
     * Purpose: return a collection of users with basic relations.
     *
     * @return Collection<App\Models\User>
     */
    public function getAllUsers(): Collection
    {
        return $this->userRepository->getAll();
    }
    /**
     * Return paginated users for admin lists.
     *
     * Called by: UserController@index
     * Endpoint: GET /admin/users
     * Allowed users: super admin
     *
     * @param int $perPage
     * @param int $page
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    public function getAllPaginated(int $perPage = 10, int $page = 1): array
    {
        return $this->userRepository->getAllPaginated($perPage, $page);
    }

    /**
     * Return all agents for the admin's municipality.
     *
     * Called by: UserController@myMunicipalityUsers
     * Endpoint: GET /admin_manager/users
     * Allowed users: admin municipal
     *
     * @param User $user Authenticated admin user
     * @return Collection<App\Models\User>
     */
    public function getMunicipalityUsers(User $user): Collection
    {
        $manager = $user;

        return User::where('municipality_id', $manager->municipality_id)
        ->where('role', 'agent')
        ->with(['category:id,name'])
        ->get();
    
}
    /**
     * Return paginated agents for admin's municipality.
     *
     * Called by: UserController@myMunicipalityUsers
     * Endpoint: GET /admin_manager/users (with pagination params)
     * Allowed users: admin municipal
     *
     * @param User $user
     * @param int $perPage
     * @param int $page
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    public function getMunicipalityUsersPaginated(User $user, int $perPage = 10, int $page = 1): array
    {
        $manager = $user;

        return $this->userRepository->getMunicipalityUsersPaginated($manager->municipality_id, $perPage, $page);
        
    
}
    /**
    * Return agents of admin's municipality filtered by category.
    *
    * Called by: UserController@myMunicipalityUsersByCategory
    * Endpoint: GET /admin_manager/users/:categoryId
    * Allowed users: admin municipal
    *
    * @param User $user
    * @param int $categoryId
    * @return Collection<App\Models\User>
    */
    public function getMunicipalityUsersByCategory(User $user,int $categoryId): Collection
    {
        $manager = $user;

      return User::where('municipality_id', $manager->municipality_id)
    ->where('category_id', $categoryId)
    ->where('status', UserStatus::ACTIVE)
    // ->where('role', 'agent')
    ->with(['category:id,name'])
    ->get();
    }
    /**
     * Create a new user (super admin flow).
     *
     * Called by: UserController@store
     * Endpoint: POST /admin/users
     * Allowed users: super admin
     * Purpose: create user, resolve municipality/service and return loaded model.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        // Hash du mot de passe
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // // Résoudre le rôle
        // if (!empty($data['role'])) {
        //     $role = Role::where('name', $data['role'])->first();
        //     if (!$role) {
        //         throw new \Exception('Role not found', 404);
        //     }
        //     $data['role_id'] = $role->id;
        //     unset($data['role']);
        // }

        // Résoudre la municipalité si fournie
        $municipality = null;
        if (!empty($data['municipality'])) {
            $municipality = Municipality::where('name', $data['municipality'])->first();
            $data['municipality_id']=$municipality->id;
            unset($data['municipality']);
            if (!$municipality) {
                throw new \Exception('Municipality not found', 404);
            }
        }

        // Résoudre le service (catégorie) si fourni
        $service = null;
        if (!empty($data['service'])) {
            $service = Category::where('name', $data['service'])->first();
             $data['category_id']=$service->id;
            unset($data['service']);
            if (!$service) {
                throw new \Exception('Service not found', 404);
            }
        }

        // Créer l'utilisateur
        $user = $this->userRepository->create($data);       

        Log::info('User created: ' . $user->id);
         return match($user->role) {
        UserRole::AGENT          => $user->load('municipality', 'category'),
        UserRole::ADMIN_MUNICIPAL => $user->load('municipality'),
        default                  => $user,
    };



    }

    /**
     * Create an employee inside the admin's municipality.
     *
     * Called by: UserController@store (when admin_manager creates an employee)
     * Endpoint: POST /admin_manager/users
     * Allowed users: admin municipal
     *
     * @param array $data
     * @param User $user Authenticated admin
     * @return User
     */
    public function createEmployee(array $data, User $user): User
    {
                    Log::info("Enterred Service");

        // $manager = $user;

        $municipalityId = $user->municipality_id;

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
                    \Log::info('Service donnée : ', ["service"=>$data['category']]);        

            // $role = Role::where('name', UserRole::AGENT->value)->first();
            // if (!$role) {
            //     throw new \Exception('Role not found', 404);
            // }
            $service=null;
             if (!empty($data['category'])) {
            Log::info('Service donnée : ', ['service' => $data['category']]);
            $service = Category::where('name', $data['category'])->first();
            if (!$service) {
                throw new \Exception('Service not found', 404);
            }
            $data['category_id'] = $service->id;
            unset($data['category']);
        }
            // $data['role_id'] = $role->id;
            $data['municipality_id'] = $municipalityId;
            $data['role'] = 'agent';

        $user = $this->userRepository->create($data);

        return $user->load( 'municipality', 'category');
    }

    /**
     * Update a user (admin flow).
     *
     * Called by: UserController@update
     * Endpoint: PUT /admin/users/{id}
     * Allowed users: admin / super admin depending on controller policies
     *
     * @param User $user The user to update
     * @param array $data
     * @return User
     */
    public function updateUser(User $user, array $data): User
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Résoudre la municipalité
        if (!empty($data['municipality'])) {
            $municipality = Municipality::where('name', $data['municipality'])->first();
            if (!$municipality) {
                throw new \Exception('Municipality not found', 404);
            }
            $data['municipality_id'] = $municipality->id;
        }

        // Résoudre le service
        if (!empty($data['service'])) {
            $service = Category::where('name', $data['service'])->first();
            if (!$service) {
                throw new \Exception('Municipal service (category) not found', 404);
            }
            $data['category_id'] = $service->id;
        }

        $updatedUser = $this->userRepository->update($user, $data);

        // Mettre à jour le profil selon le rôle
        // if ($user->role === UserRole::ADMIN_MUNICIPAL->value) {
        //     $this->userRepository->updateMunicipalAdmin($user->id, $data);
        // } elseif ($user->role === UserRole::AGENT->value) {
        //     $this->userRepository->updateAgent($user->id, $data);
        // }

        Log::info('User updated: ' . $user->id);

        return $updatedUser;
    }

    /**
     * Update the authenticated user's profile.
     *
     * Called by: profile endpoints (e.g. PUT /me)
     * Allowed users: authenticated users
     * Purpose: allow users to update personal info and service category.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        Log::info('Updating profile for user: ' . $user->id);
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        if (!empty($data['email'])) {
    $exists = User::where('email', $data['email'])
                      ->where('id', '!=', $user->id)
                  ->exists();
    if ($exists) {
        throw new \Exception('Cet email deja utilisé', 409); 
    }
}

        if (!empty($data['service'])) {
            $service = Category::where('name', $data['service'])->first();
            if (!$service) {
                throw new \Exception('Municipal service not found', 404);
            }
            $data['category_id'] = $service->id;
        }

        $updatedUser = $this->userRepository->update($user, $data);

        if ($user->role === UserRole::AGENT->value) {
            $this->userRepository->updateAgent($user->id, $data);
        }

        Log::info('Profile updated: ' . $user->id);

        return $updatedUser;
    }

    /**
     * Update user's status (active/inactive).
     *
     * Called by: admin endpoints to enable/disable accounts
     * Allowed users: admin/super admin
     *
     * @param User $user
     * @param string $status
     * @return User
     */
    public function updateStatus(User $user, string $status): User
    {
        $updatedUser = $this->userRepository->update($user, ['status' => $status]);
        // Log::info('User status updated: ' . $user->id);
        return $updatedUser;
    }

    /**
     * Delete a user account.
     *
     * Called by: admin endpoints
     * Endpoint: DELETE /admin/users/{id}
     * Allowed users: super admin (or controller policy)
     *
     * @param User $user
     * @return void
     */
    public function deleteUser(User $user): void
    {
        $this->userRepository->delete($user);
        Log::info('User deleted: ' . $user->id);
    }
}