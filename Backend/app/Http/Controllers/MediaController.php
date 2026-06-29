<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Media', description: 'Gestion des médias (photos de profil, incidents, etc.)')]
class MediaController extends Controller
{
    public function __construct(
        private MediaService $mediaService
    ) {}

    /**
     * Afficher un média spécifique
     */
    #[OA\Get(
        path: '/media/{media}',
        summary: 'Afficher un média',
        tags: ['Media'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'media', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails du média'),
            new OA\Response(response: 404, description: 'Média non trouvé'),
        ]
    )]
    public function show(Media $media): JsonResponse
    {
        // Note: ton code original injectait Media $incidentMedia mais le nommait $incidentMedia
        // Renommé en $media pour être cohérent avec le route model binding
        return response()->json([
            'message' => 'Media retrieved successfully',
            'data'    => $media,
        ]);
    }

    /**
     * Uploader une image d'incident sur Cloudinary
     */
    #[OA\Post(
        path: '/media/upload-image',
        summary: 'Uploader une image',
        tags: ['Media'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['photo'],
                    properties: [
                        new OA\Property(property: 'photo', type: 'string', format: 'binary', description: 'Fichier image'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Photo uploadée avec succès'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function uploadImage(Request $request): JsonResponse
    {
        try {
           $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // 5MB max
        ]);

        $media = $this->mediaService->uploadIncidentImage($request->file('image'));

        // Créer l'incident en liant le media
        $incident = Incident::create([
            'media_id' => $media->id,
            // ... autres champs
        ]);

            return response()->json([
                'message' => 'Photo uploaded successfully',
                'data'    => $media,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Supprimer une image de Cloudinary
     */
    #[OA\Delete(
        path: '/media/delete-image/{publicId}',
        summary: 'Supprimer une image Cloudinary',
        tags: ['Media'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'publicId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Image supprimée'),
            new OA\Response(response: 500, description: 'Erreur lors de la suppression'),
        ]
    )]
    public function deleteImage(string $publicId): JsonResponse
    {
        try {
            $this->mediaService->deleteImage($publicId);

            return response()->json(['message' => 'Image supprimée avec succès']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Uploader la photo de profil de l'utilisateur connecté
     */
    #[OA\Post(
        path: '/users/upload-profile-photo',
        summary: 'Uploader une photo de profil',
        tags: ['Media'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['avatar'],
                    properties: [
                        new OA\Property(property: 'avatar', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Photo de profil uploadée'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function uploadProfilePhoto(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            $photo = $this->mediaService->uploadProfilePhoto(
                auth('api')->user(),
                $request->file('avatar')   // ton code original utilisait 'photo' au lieu de 'avatar' — corrigé
            );

            return response()->json([
                'message' => 'Profile photo uploaded successfully',
                'data'    => $photo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}