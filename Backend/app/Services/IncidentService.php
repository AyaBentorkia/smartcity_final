<?php

namespace App\Services;

use App\Enums\IncidentStatus;
use App\Services\FcmService;
use App\Models\Category;
use App\Models\Incident;
use App\Models\Municipality;
use App\Models\City;
use App\Models\User;
use App\Models\Zone;
use App\Services\NotificationService;
use App\Models\Notification;
use App\Events\IncidentResolved;
use App\Services\MediaService;
use App\Repositories\Contracts\IncidentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use App\Enums\UserRole;
use App\Jobs\SendFcmNotification;



/**
 * IncidentService
 *
 * Service centralisant la logique métier liée aux incidents :
 * - lecture (listes, filtres, pagination)
 * - création (avec ou sans détection automatique de zone)
 * - mise à jour du statut et suppression
 *
 * Ce service orchestre les appels vers le repository d'incidents et
 * utilise d'autres services (MediaService, NotificationService, jobs FCM).
 */
class IncidentService
{
    public function __construct(
        private IncidentRepositoryInterface $incidentRepository,
    private MediaService $mediaService,
    // private FcmService $fcmService,
    ) {}

    /**
     * Lister tous les incidents
     */
    /**
     * Retrieve all incidents.
     *
     * Called by: IncidentController@index (when non-paginated)
     * Purpose: return a collection of incidents with relations for
     * internal admin tools or exports.
     *
     * @return Collection<App\Models\Incident>
     */
    public function getAll(): Collection
    {
        return $this->incidentRepository->getAll();
    }
    /**
     * Retrieve incidents with pagination and optional filters.
     *
     * Called by: IncidentController@index
     * Endpoint: GET /incidents
     * Purpose: return paginated incidents for lists shown in the UI.
     *
     * @param int $perPage
     * @param int $page
     * @param int|null $municipalityId Optional municipality filter
     * @param string|null $search Optional text search
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    public function getAllPaginated(int $perPage = 10, int $page = 1, ?int $municipalityId = null, ?string $search = null): array
{
    return $this->incidentRepository->getAllPaginated($perPage, $page, $municipalityId, $search);
}

    /**
     * Find an incident by id.
     *
     * Called by: IncidentController@show
     * Endpoint: GET /incidents/{id}
     * Purpose: return detailed incident with relations.
     *
     * @param int $id
     * @return Incident
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(int $id): Incident
    {
        // Log::info('Finding incident by ID', ['incident_id' => $this->incidentRepository->findByIdOrFail($id)]);
return $this->incidentRepository->findByIdOrFail($id);
    }

    /**
     * Create an incident (citizen flow, without auto zone detection).
     *
     * Called by: IncidentController@store
     * Endpoint: POST /citizen/incidents/{category_id}
     * Allowed users: authenticated citizens
     * Purpose: persist a new incident and return the model.
     *
     * @param array $data Validated incident payload
     * @param int $citizenId
     * @param int $categoryId
     * @return Incident
     */
    public function create(array $data, int $citizenId, int $categoryId): Incident
    {
        if (empty($data['address_text']) && empty($data['latitude']) && empty($data['longitude'])) {
            throw new \Exception('Localisation requise (coordonnées ou adresse texte)', 422);
        }

        $data['reported_at']  = now();
        $data['category_id']  = $categoryId;
        $data['citizen_id']   = $citizenId;

        $incident = $this->incidentRepository->create($data);

        Log::info('Incident created successfully', ['incident_id' => $incident->id]);

        return $incident;
    }

    /**
     * Create an incident using GPS coordinates to detect the zone.
     *
     * Called by: IncidentController@storeWithZone
     * Endpoint: POST /citizen/incidents/{category_id}/zone
     * Allowed users: authenticated citizens
     * Purpose: upload media (optional), detect zone from lat/lng and
     * notify the municipal admin if any.
     *
     * @param array $data
     * @param int $citizenId
     * @param int $categoryId
     * @param UploadedFile|null $image
     * @return Incident
     * @throws \Exception on invalid coordinates or zone not found
     */
    public function createWithZone(array $data, int $citizenId, int $categoryId, ?UploadedFile $image = null ): Incident
    {
                    \Log::info('GPS Coordinates', ['long' => $data]);
                                        \Log::info('Image', ['long' => $image]);


    //      if (!isset($data['latitude']) || !isset($data['longitude']) ||
    //     !is_numeric($data['latitude']) || !is_numeric($data['longitude'])) {
    //     throw new \Exception('Coordonnées GPS requises (latitude et longitude)', 422);
    // }
$mediaId = null;
// $city=null;
    if ($image) {
        $media   = $this->mediaService->uploadIncidentImage($image);
        $mediaId = $media->id;
    }
    $zone = null;
            $municipality = null;

    if (isset($data['latitude']) && isset($data['longitude'])) {
        $zone = Zone::findByCoordinates($data['latitude'], $data['longitude']);
        if (!$zone) {
            throw new \Exception('Aucune zone trouvée pour ces coordonnées', 422);
        }
        $data['zone_id']      = $zone->id;
        // $municipality = Municipality::find($zone->municipality_id)->first();
    // if ($municipality?->city_id) {
    //     $data['city_id'] = $municipality->city_id;
    // }


    } 
    // else if (isset($data['city_id'])) {
    //     // $city = City::findByName($data['city'] ?? '');
    //     $data['city_id'] = $data['city_id'];
    //     // unset($data['city']);
    // }

         $data['reported_at']  = now();
        $data['category_id']  = $categoryId;
        $data['citizen_id']   = $citizenId;
        $data['status']      = IncidentStatus::REPORTED->value;
 $data['media_id']    = $mediaId;
        // Log::info('Zone détectée automatiquement', ['zone_id' => $zone->id, 'zone_name' => $zone->name]);

        $incident = $this->incidentRepository->create($data);
        $admin = null;
        // if($zone!==null){
            $admin = User::where('municipality_id', $zone->municipality_id)
    ->where('role', UserRole::ADMIN_MUNICIPAL->value)
    ->first();
        // } 
//         else {
// // $municipality = Municipality::where('city_id', $city->id)->first();
//             $municipality = $city->municipalities()->first();
//             $admin = User::where('municipality_id', $municipality->id)
//             ->where('role', UserRole::ADMIN_MUNICIPAL->value)
//             ->first();
//         }

            if ($admin) {
    // ✅ Persister en base
    $notification = Notification::create([
        'user_id'     => $admin->id,
        'incident_id' => $incident->id,
        'title'       => 'Nouvel incident signalé',
        'body'        => ($incident->category?->name ?? '') . ' — zone : ' . ($incident->zone?->name ?? ''),
        'type'        => 'incident_created',
    ]);
\Log::info('Broadcasting to admin', [
    'admin_id'        => $admin->id,
    'notification_id' => $notification->id,
    'incident_id'     => $incident->id,
]);
    // ✅ Broadcaster avec l'id de la notification
    broadcast(new \App\Events\IncidentCreated($incident, $admin->id, $notification->id));
}

        return $incident->load('zone', 'category','medias');
    }


    /**
     * Update an incident belonging to a citizen.
     *
     * Called by: IncidentController@update
     * Endpoint: PUT /citizen/incidents/{id}
     * Allowed users: the citizen who reported the incident
     * Purpose: allow modification only when incident is in `REPORTED` state.
     *
     * @param int $id
     * @param int $citizenId
     * @param array $data
     * @return Incident
     */
    public function updateByCitizen(int $id, int $citizenId, array $data): Incident
    {
        $incident = $this->incidentRepository->findByIdAndCitizen($id, $citizenId);

        if (!$incident) {
            throw new \Exception('Incident non trouvé', 404);
        }

        if ($incident->status != IncidentStatus::REPORTED->value) {
            throw new \Exception('Seuls les incidents avec le statut "signalé" peuvent être mis à jour', 400);
        }

        return $this->incidentRepository->update($incident, $data);
    }

    /**
     * Delete an incident.
     *
     * Called by: IncidentController@destroy
     * Endpoint: DELETE /incidents/{id}
     * Allowed users: depends on controller authorization (admins or owners)
     * Purpose: remove the incident from persistence.
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        $incident = $this->incidentRepository->findByIdOrFail($id);
        $this->incidentRepository->delete($incident);
    }

    /**
     * Récupérer les incidents proches de la municipalité de l'admin connecté
     */
    // public function getNearbyForAdmin(User $user): Collection
    // {
    //     //  Log::info('in incident nearby');
    //     // $manager = User::find($userId);

    //     // if (!$manager) {
    //     //     throw new \Exception('Admin municipal non trouvé', 404);
    //     // }
    //     $manager = $user;

    //     $zoneIds = Zone::where('municipality_id', $manager->municipality_id)
    //         ->pluck('id')
    //         ->toArray();
    //     return ;

    //     // $municipality = Municipality::find($manager->municipality_id)->first();

    //     // return $this->incidentRepository->getByZoneIds($municipality->city_id);
    // }
    /**
     * Récupérer les incidents de la municipalité de l'admin connecté paginés
     *
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    // public function getNearbyForAdminPaginated(User $user, int $perPage = 10, int $page = 1): array
    // {
    //     $manager = $user;
 
    //     if (!$manager) {
    //         throw new \Exception('Admin municipal non trouvé', 404);
    //     }
    //     $municipality = Municipality::find($manager->municipality_id)->first();

  
    //     return $this->incidentRepository->getByCityIdPaginated($municipality->city_id, $perPage, $page);
    // }
    /******* */
    /**
     * Retrieve incidents close to the authenticated admin's municipality.
     *
     * Called by: IncidentController@getNearby (admin manager)
     * Allowed users: admin municipal
     * Purpose: return incidents filtered by the admin's municipality zones.
     *
     * @param User $user
     * @return Collection
     */
    public function getNearbyForAdmin(User $user): Collection
    {
        //  Log::info('in incident nearby');
        // $manager = User::find($userId);

        // if (!$manager) {
        //     throw new \Exception('Admin municipal non trouvé', 404);
        // }
        $manager = $user;

        $zoneIds = Zone::where('municipality_id', $manager->municipality_id)
            ->pluck('id')
            ->toArray();

        return $this->incidentRepository->getByZoneIds($zoneIds);
    }
    /**
     * Retrieve paginated incidents for the admin's municipality.
     *
     * Called by: IncidentController@getNearby
     * Endpoint: GET /admin_manager/incidents/nearby
     * Allowed users: admin municipal
     *
     * @param User $user
     * @param int $perPage
     * @param int $page
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    public function getNearbyForAdminPaginated(User $user, int $perPage = 10, int $page = 1): array
    {
        $manager = $user;
 
        if (!$manager) {
            throw new \Exception('Admin municipal non trouvé', 404);
        }
 
        $zoneIds = Zone::where('municipality_id', $manager->municipality_id)
            ->pluck('id')
            ->toArray();
 
        return $this->incidentRepository->getByZoneIdsPaginated($zoneIds, $perPage, $page);
    }

    /**
     * Get incidents filtered by category name.
     *
     * Called by: IncidentController@getByType
     * Endpoint: GET /incidents/type/{type} (or similar)
     *
     * @param string $type Category name
     * @return Collection
     */
    public function getByType(string $type): Collection
    {
        $category = Category::where('name', $type)->first();

        if (!$category) {
            throw new \Exception('Catégorie non trouvée', 404);
        }

        return $this->incidentRepository->getByCategoryId($category->id);
    }

    /**
     * Get incidents by status (reported, in_progress, resolved...).
     *
     * Called by: IncidentController@getByStatus
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection
    {
        return $this->incidentRepository->getByStatus($status);
    }

    /**
     * Get incidents for a specific zone.
     *
     * Called by: IncidentController@getByZone
     * Endpoint: GET /incidents/zone/{zoneId}
     *
     * @param int $zoneId
     * @return Collection
     */
    public function getByZone(int $zoneId): Collection
    {
        $zone = Zone::find($zoneId);

        if (!$zone) {
            throw new \Exception('Zone non trouvée', 404);
        }

        return $this->incidentRepository->getByZoneId($zoneId);
    }

    /**
     * Get incidents submitted by a specific citizen (admin use).
     *
     * Called by: IncidentController@getByCitizen
     *
     * @param int $citizenId
     * @return Collection
     */
    public function getByCitizen(int $citizenId): Collection
    {
        return $this->incidentRepository->getByCitizenId($citizenId);
    }

        /**
         * Get incidents for the currently authenticated citizen.
         *
         * Called by: controller endpoints for citizen (e.g. /citizen/incidents)
         * Allowed users: authenticated citizens
         *
         * @param int $citizenId
         * @return Collection
         */
     public function getMyIncidents(int $citizenId): Collection
{
    \Log::info('my incidents');

    $incidents =  $this->incidentRepository->getBycitizenIdWithRelations($citizenId);
        \Log::info('my incidents', ['incidents' => $incidents]);

    return $incidents;
    // supprime le Log::info et l'accès direct au modèle
}
    /**
     * Get paginated incidents for the authenticated citizen.
     *
     * Called by: citizen endpoints returning paginated results
     *
     * @param int $citizenId
     * @param int $perPage
     * @param int $page
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    public function getMyIncidentsPaginated(int $citizenId, int $perPage = 10, int $page = 1): array
    {
        return $this->incidentRepository->getByCitizenIdPaginated($citizenId, $perPage, $page);
    }

    /**
     * Update the status of an incident (admin action).
     *
     * Called by: controller endpoints used by municipal admins
     * Allowed users: admin municipal
     * Purpose: change status, set resolved_at when appropriate and notify citizen.
     *
     * @param int $id
     * @param string $status
     * @return Incident
     */
    public function updateStatus(int $id, string $status): Incident
    {
        $incident = $this->incidentRepository->findById($id);

    if (!$incident) {
        throw new \Exception('Incident non trouvé', 404);
    }

    $data = ['status' => $status];

    // ✅ Si résolu, enregistrer la date
    if ($status === IncidentStatus::RESOLVED->value) {
        $data['resolved_at'] = now();
    }

    $updated = $this->incidentRepository->update($incident, $data);

    // ✅ Notifier le citoyen si résolu
    if ($status === IncidentStatus::RESOLVED->value) {
        $notification = Notification::create([
            'user_id'     => $updated->citizen_id,
            'incident_id' => $updated->id,
            'title'       => 'Votre incident a été résolu',
            'body'        => ($updated->category?->name ?? '') . ' — zone : ' . ($updated->zone?->name ?? ''),
            'type'        => 'incident_resolved',
        ]);

        broadcast(new IncidentResolved(
            $updated->load('zone', 'category'),
            $updated->citizen_id,
            $notification->id
        ));
       // ✅ Push FCM (même si app fermée)
        $citizen = $updated->citizen;
if ($citizen && $citizen->fcm_token) {
    SendFcmNotification::dispatch(
        $citizen->fcm_token,
        'Votre incident a été résolu ✅',
        ($updated->category?->name ?? '') . ' — zone : ' . ($updated->zone?->name ?? ''),
        [
            'type'        => 'incident_resolved',
            'incident_id' => (string) $updated->id,
        ]
    );
}
    }

    return $updated;
    }
}