<?php

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class MediaService
{

    /**
     * Uploader une image (liée à un incident) sans cloudinary
     */
    public function uploadIncidentImage(UploadedFile $file): Media
    {
         $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Stocke le fichier dans storage/app/public/incidents/
        $path = $file->storeAs('incidents', $filename, 'public');

        // Générer l'URL via la route API (plus robuste que l'accès direct)
        $baseUrl = env('APP_URL', 'http://127.0.0.1:8000');
    $fullUrl = rtrim($baseUrl, '/') . '/storage/incidents/' . $filename;
        $media = Media::create([
            'url' => $fullUrl,
        ]);
//         $media = Media::create([
//     'url' => 'incidents/' . $filename,  // ← chemin relatif uniquement
// ]);

        return $media;
    }

    /**
     * Supprimer une image  de la BDD
     */
    public function deleteImage(string $publicId): void
    {
       $media = Media::findOrFail($publicId);

    // Extraire le chemin relatif depuis l'URL complète
    // ex: "https://app.test/storage/incidents/uuid.jpg" → "incidents/uuid.jpg"
    $relativePath = str_replace(asset('storage/'), '', $media->url);

    // Supprimer le fichier physique si il existe
    if (Storage::disk('public')->exists($relativePath)) {
        Storage::disk('public')->delete($relativePath);
    }

    // Supprimer l'entrée en base
    $media->delete();
    }

    /**
     * Uploader / remplacer la photo de profil d'un utilisateur
     */
    public function uploadProfilePhoto(User $user, UploadedFile $file): array
    {
        // Supprimer l'ancienne photo Cloudinary si elle existe
        if (!empty($user->photo['publicId'])) {
            $this->deleteFromCloudinary($user->photo['publicId']);
        }

        $uploaded = $this->uploadToCloudinary($file, 'users');

        // Mettre à jour le champ photo de l'utilisateur
        $user->photo = [
            'url'      => $uploaded['url'],
            'publicId' => $uploaded['publicId'],
        ];
        $user->save();

        Log::info('Profile photo updated for user: ' . $user->id);

        return $user->photo;
    }
}