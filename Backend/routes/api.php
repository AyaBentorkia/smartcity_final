<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MessagingController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\GovernorateController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\IncidentPredictionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\StatisticsController;


// =============================================
// ROUTES PUBLIQUES
// =============================================
// Broadcast::routes(['middleware' => [\App\Http\Middleware\AuthMiddleware::class]]);
// Route::post('/fcm-token', [NotificationController::class, 'updateFcmToken']);

// Authentification
Route::post('auth/register', [AuthController::class, 'register']);

Route::post('auth/login', [AuthController::class, 'login']);
// Étape 1 : demande de lien par email
Route::post('auth/forgot-password', [PasswordResetController::class, 'forgotPassword'])
    ->middleware('throttle:5,1'); // max 5 tentatives par minute
 
// Étape 2 : soumission du nouveau mot de passe
Route::post('auth/reset-password', [PasswordResetController::class, 'resetPassword']);

// ─── Google OAuth ───────────────────────────────────────────────────────────
// Étape 1 : Le frontend appelle cette route pour obtenir l'URL Google
Route::get('auth/google',          [GoogleAuthController::class, 'redirect']);
// Étape 2 : Google rappelle cette route après authentification
Route::get('auth/google/callback', [GoogleAuthController::class, 'callback']);
// ────────────────────────────────────────────────────────────────────────────
 // ── Google OAuth — MOBILE (Flutter) ──────────────────────────────────────────
// Flutter ouvre cette URL dans le navigateur système
Route::get('auth/google/mobile',            [GoogleAuthController::class, 'mobileRedirect']);
// Google rappelle cette route après auth → redirige vers deep link Flutter
Route::get('auth/google/mobile/callback',   [GoogleAuthController::class, 'mobileCallback']);

// =============================================
    // GESTION DES MUNICIPALITÉS
    // =============================================
    
    // Liste des municipalités
    // Voir une municipalité spécifique
    Route::get('municipalities/{municipality}', [MunicipalityController::class, 'show']);
    Route::get('municipalities', [MunicipalityController::class, 'index']);
    
// Routes publiques — lecture des villes, gouvernorats et pays (pour les formulaires)
Route::get('cities', [CityController::class, 'index']);
Route::get('cities/{city}', [CityController::class, 'show']);
Route::get('cities/governorate/{governorate}', [CityController::class, 'getByGovernorate']);

Route::get('governorates', [GovernorateController::class, 'index']);
Route::get('governorates/{governorate}', [GovernorateController::class, 'show']);
Route::get('governorates/{governorateId}/cities', [CityController::class, 'getByGovernorate']);

Route::get('countries', [CountryController::class, 'index']);
Route::get('countries/{countryId}/governorates', [GovernorateController::class, 'getByCountry']);

// Liste publique des catégories d'incidents (services)
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);

// Email verification (signed link)
Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->name('verification.verify')
    ->middleware('signed');

// ─── Route pour servir les fichiers médias (images incidents) ───
Route::get('media/{type}/{filename}', function ($type, $filename) {
    $path = storage_path("app/public/{$type}/{$filename}");
    
    // Sécurité: vérifier que le chemin n'essaie pas de sortir du dossier (path traversal)
    if (!str_starts_with(realpath($path) ?: '', realpath(storage_path('app/public')))) {
        abort(403, 'Accès refusé');
    }
    
    if (!file_exists($path)) {
        abort(404, 'Fichier non trouvé');
    }
    
    return response()->file($path);
})->where('filename', '[a-f0-9\-\.]+');

// =============================================
// ROUTES PROTÉGÉES PAR AUTHENTIFICATION
// =============================================

Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    $user = auth('api')->user();

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $pusher = new \Pusher\Pusher(
        config('broadcasting.connections.reverb.key'),
        config('broadcasting.connections.reverb.secret'),
        config('broadcasting.connections.reverb.app_id'),
        [
            'host'   => config('broadcasting.connections.reverb.options.host', '127.0.0.1'),
            'port'   => config('broadcasting.connections.reverb.options.port', 6001),
            'scheme' => config('broadcasting.connections.reverb.options.scheme', 'http'),
            'curl_options' => [
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
            ],
        ]
    );

    $channelName = $request->channel_name;
    $socketId    = $request->socket_id;

    if (str_starts_with($channelName, 'private-user.')) {
        $userId = (int) str_replace('private-user.', '', $channelName);
        if ($user->id !== $userId) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
    }

    $auth = $pusher->authorizeChannel($channelName, $socketId);
    return response($auth, 200)->header('Content-Type', 'application/json');

})->middleware(\App\Http\Middleware\AuthMiddleware::class);
Route::middleware([\App\Http\Middleware\AuthMiddleware::class])->group(function () {
 Route::post('/fcm-token', [NotificationController::class, 'updateFcmToken']);
    Route::post('/devices',   [NotificationController::class, 'updateFcmToken']);
Route::patch('users/{user}/status', [UserController::class, 'updateStatus']);


    // =============================================
    // AUTHENTIFICATION
    // =============================================
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    
    // Resend verification
    Route::post('email/resend', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1');

    // =============================================
    // GESTION DES UTILISATEURS
    // =============================================

    // Mettre à jour son propre profil
    Route::match(['put', 'patch'], 'me', [UserController::class, 'updateProfile']);
    // Upload photo de profil
    Route::post('users/upload-profile-photo', [MediaController::class, 'uploadProfilePhoto']);

    // =============================================
    // GESTION DES INCIDENTS
    // =============================================
    
    // Liste des incidents
    Route::get('incidents', [IncidentController::class, 'index']);
    // Voir un incident spécifique
        // Route::get('incidents/nearby', [IncidentController::class, 'getNearby']);
    Route::get('incidents/{id}', [IncidentController::class, 'show']);
    // Supprimer un incident (citizen/Admin) (tant que pas traité)
    Route::delete('incidents/{id}', [IncidentController::class, 'destroy']);
    
    // Routes spécifiques pour les incidents 
    Route::get('incidents/type/{type}', [IncidentController::class, 'getByType']);
    Route::get('incidents/status/{status}', [IncidentController::class, 'getByStatus']);
    Route::get('incidents/citizen/{citizenId}', [IncidentController::class, 'getByCitizen']);
    Route::get('incidents/zone/{zoneId}', [IncidentController::class, 'getByZone']);
    Route::post('/fcm-token', [NotificationController::class, 'updateFcmToken']); // ← ici

  Route::get('/notifications',              [NotificationController::class, 'index']);
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']); 
Route::patch('/notifications/read-all',   [NotificationController::class, 'markAllAsRead']); 
Route::patch('/notifications/{id}/read',  [NotificationController::class, 'markAsRead']); 
    
    
    // =============================================
    // GESTION DES MÉDIAS D'INCIDENTS
    // =============================================
    
    // Voir un média spécifique
    Route::get('incident-media/{incidentMedia}', [MediaController::class, 'show']);
    
    // Upload d'une image
    Route::post('incident-media/upload', [MediaController::class, 'uploadImage']);
    
    // Mettre à jour un média
    Route::match(['put', 'patch'], 'incident-media/{incidentMedia}', [MediaController::class, 'update']);
    
    // Supprimer un média
    Route::delete('incident-media/{incidentMedia}', [MediaController::class, 'destroy']);
    
    // Supprimer une image Cloudinary
    Route::delete('incident-media/delete-image/{publicId}', [MediaController::class, 'deleteImage']);
     
   

});

  
// =============================================
// ROUTES AVEC MIDDLEWARE DE ROLE (Admin)
// =============================================

    Route::middleware([\App\Http\Middleware\AuthMiddleware::class,'is_admin'])->prefix('admin')->group(function () {
         Route::get('statistics', [StatisticsController::class, 'globalStats']); 
  
         // Countries (super admin)
Route::get('/countries', [CountryController::class, 'index']);
Route::get('/countries/{country}', [CountryController::class, 'show']);
Route::post('/admin/countries', [CountryController::class, 'store']);
Route::put('/admin/countries/{country}', [CountryController::class, 'update']);
Route::delete('/admin/countries/{country}', [CountryController::class, 'destroy']);

// Governorates
Route::get('/governorates', [GovernorateController::class, 'index']);
Route::get('/governorates/{governorate}', [GovernorateController::class, 'show']);
Route::get('/countries/{countryId}/governorates', [GovernorateController::class, 'getByCountry']);
Route::post('/admin/governorates', [GovernorateController::class, 'store']);
Route::put('/admin/governorates/{governorate}', [GovernorateController::class, 'update']);
Route::delete('/admin/governorates/{governorate}', [GovernorateController::class, 'destroy']);

// Cities — mettre à jour la route getByGovernorate
Route::get('/governorates/{governorateId}/cities', [CityController::class, 'getByGovernorate']);
// (supprimer l'ancienne route /cities/governorate/{governorate})
    //=============================================
    // GESTION DES UTILISATEURS
    // =============================================
    Route::get('assignments', [AssignmentController::class, 'index']);

        Route::get('me', [AuthController::class, 'me']);

    // Consulter les utilisateurs
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{user}', [UserController::class, 'show']);
    // Créer un utilisateur (Admin/Manager)
    Route::post('users', [UserController::class, 'store']);
    // Mettre à jour un utilisateur
    
    Route::delete('users/{user}', [UserController::class, 'destroy']);
    
    // =============================================
    // GESTION DES INCIDENTS
    // =============================================
    Route::get('incidents-per-municipality', [IncidentController::class, 'getByMunicipality']);
     // Supprimer un incident (citizen/Admin) (tant que pas traité)
    Route::delete('incidents/{id}', [IncidentController::class, 'destroy']);

     // =============================================
    // GESTION DES CATÉGORIES  
    // =============================================
    // Créer une catégorie 
    Route::post('categories', [CategoryController::class, 'store']);
    // Mettre à jour une catégorie (Admin)
    Route::match(['put', 'patch'], 'categories/{category}', [CategoryController::class, 'update']);
    // Supprimer une catégorie (Admin)
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

    // =============================================
    // GESTION DES MUNICIPALITÉS
    // =============================================

    // Créer une municipalité 
        Route::get('municipalities', [MunicipalityController::class, 'index']);

    Route::post('municipalities', [MunicipalityController::class, 'store']);
    // Mettre à jour une municipalité 
    Route::match(['put', 'patch'], 'municipalities/{municipality}', [MunicipalityController::class, 'update']);
    // Supprimer une municipalité 
    Route::delete('municipalities/{municipality}', [MunicipalityController::class, 'destroy']);

    // =============================================
    // GESTION DES ZONES
    // =============================================

    // get all zones 
    Route::get('/zones', [ZoneController::class, 'index']);
//delete comment
    Route::patch('incidents/{incident_id}/comments', [CommentController::class, 'destroy']);
    
});


// =============================================
// ROUTES AVEC MIDDLEWARE DE ROLE (Municipal Manager)
// ============================================= 

Route::middleware([\App\Http\Middleware\AuthMiddleware::class, 'is_municipal_admin'])->prefix('admin_manager')->group(function () {
        Route::get('statistics', [StatisticsController::class, 'municipalStats']); // ✅ ajouter

Route::post('incidents/{incident}/assign', [AssignmentController::class, 'assign']);
    //=============================================
    // GESTION DES UTILISATEURS
    // =============================================

    //get users of my municipality
    Route::get('users',[UserController::class,'myMunicipalityUsers']);
    //get users by category
    Route::get('agents/{categoryId}',[UserController::class,'myMunicipalityUsersByCategory']);
    //Create use
    // my municipality 
    Route::post('users',[UserController::class,'storeEmployee']);

    // =============================================
    // GESTION DES INCIDENTS
    // =============================================
    Route::get('incidents/{id}', [IncidentController::class, 'show']);

    //get incidents of my municipality (nearby)
    // Route::get('incidents-nearby', [IncidentController::class, 'getNearby']);
    // Mettre à jour le statut d'un incident (Manager)
    Route::patch('incidents/{id}/status', [IncidentController::class, 'updateStatus']);


    //get my municipality
    Route::get('my-municipality', [MunicipalityController::class, 'GetMyMunicipality']);
    // Mettre à jour municipal
    Route::match(['put', 'patch'], 'municipalities/my-municipality', [MunicipalityController::class, 'updateByMunicipalAdmin']);

    //get incidents nearby (my municipality incidents) 
    Route::get('incidents-nearby', [IncidentController::class, 'getNearby']);

    //get my municipality zones (zones nearby)
    Route::get('zones', [ZoneController::class, 'ZonesNearBy']);
    Route::post('zones', [ZoneController::class, 'store']);

    //get my assignments
    Route::get('my-assignments',[AssignmentController::class,'getMyAssignmentsByAdmin']);
    //get an assignment
    Route::get('assignment/{assignment_id}',[AssignmentController::class,'show']);
    //Add end time 
Route::match(['put', 'patch'], 'assignments/{assignment}', [AssignmentController::class, 'update']);
    //delete an assignment
    Route::delete('assignment/{assignment_id}',[AssignmentController::class,'destroy']);
    
     // get all zones 
    Route::get('/zones', [ZoneController::class, 'index']);
    Route::get('/zones/nearby', [ZoneController::class, 'ZonesNearBy']);
    // Créer une zone 
    Route::post('/zones', [ZoneController::class, 'store']);
    // Mettre à jour une zone 
    Route::match(['put', 'patch'], 'zones/{zone}', [ZoneController::class, 'update']);
    // Supprimer une zone 
    Route::delete('zones/{zone}', [ZoneController::class, 'destroy']);
        Route::get('incidents/{incident_id}/comments',[CommentController::class, 'index']);
// IA — Incident Prediction
Route::post('zones/{zone_id}/predict',              [IncidentPredictionController::class, 'predict']);
Route::get('zones/{zone_id}/predictions',           [IncidentPredictionController::class, 'index']);
Route::get('zones/{zone_id}/predictions/latest',    [IncidentPredictionController::class, 'latest']);
Route::get('zones/{zone_id}/predictions/category',  [IncidentPredictionController::class, 'byCategory']);
});


// =============================================
// ROUTES AVEC MIDDLEWARE DE ROLE (Citizen)
// =============================================


    Route::post('incident-media', [MediaController::class, 'uploadImage']);
Route::middleware([\App\Http\Middleware\AuthMiddleware::class,'is_citizen'])->prefix('citizen')->group(function () {
    
      Route::post('incidents/{category_id}/zone', [IncidentController::class, 'storeWithZone']);

    // Upload d'une image (spécifique au citizen)
    Route::post('incident-media/upload', [MediaController::class, 'uploadImage']);
  
    // Créer un incident 
    Route::post('incidents/{category_id}', [IncidentController::class, 'storee']);
    Route::post('incidents/{category_id}/zone', [IncidentController::class, 'storeWithZone']);
    
    // Mettre à jour un incident citizen (tant que pas traité)
    Route::match(['put', 'patch'], 'incidents/{id}', [IncidentController::class, 'update']);
    
    // Récupérer mes incidents
    Route::get('my-incidents', [IncidentController::class, 'getMyIncidents']);
    Route::get('my-incidents/{id}', [IncidentController::class, 'show']);

    //delete incident
        Route::delete('incidents/{id}', [IncidentController::class, 'destroy']);

});
// =============================================
// ROUTES AVEC MIDDLEWARE DE ROLE (agent)
// =============================================
Route::middleware([\App\Http\Middleware\AuthMiddleware::class,'is_agent'])->prefix('agent')->group(function () {
        Route::get('statistics', [StatisticsController::class, 'agentStats']); // ✅ ajouter

    //get my assignments
    Route::get('my-assignments',[AssignmentController::class,'getMyAssignmentsByAgent']);
    //get an assignment
    Route::get('assignment/{assignment_id}',[AssignmentController::class,'show']);
    //Add end time 
    Route::patch('assignments/{assignment}',[AssignmentController::class,'close']);
     // =============================================
    // GESTION DES COMMENTS
    // =============================================
   Route::post('incidents/{incident_id}/comments', [CommentController::class, 'store']);
    // edit comment
    Route::patch('comments/{id}', [CommentController::class, 'update']);
    //delete
    Route::delete('comments/{id}', [CommentController::class, 'destroy']);
            Route::get('incidents/{incident_id}/comments',[CommentController::class, 'index']);

});
//messaging (citizen/municipal-admin)
Route::middleware([\App\Http\Middleware\AuthMiddleware::class])->group(function () {
    Route::get('conversations',                  [MessagingController::class, 'index']);
    Route::get('conversations/{id}',             [MessagingController::class, 'show']);
    Route::post('conversations/{id}/messages',   [MessagingController::class, 'sendMessage']);
    Route::delete('messages/{id}',               [MessagingController::class, 'destroyMessage']);
});

Route::middleware([\App\Http\Middleware\AuthMiddleware::class, 'is_citizen'])->prefix('citizen')->group(function () {
    Route::post('conversations',                 [MessagingController::class, 'store']);
});

Route::middleware([\App\Http\Middleware\AuthMiddleware::class, 'is_municipal_admin'])->prefix('admin_manager')->group(function () {
    Route::patch('conversations/{id}/close',     [MessagingController::class, 'close']);
});