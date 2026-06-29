<?php

namespace App\Traits;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;

trait UploadsToCloudinary
{
    /**
     * Uploader un fichier sur Cloudinary et retourner url + publicId
     */
    public function uploadToCloudinary(UploadedFile $file, string $folder = 'uploads'): array
    {
        $result = Cloudinary::uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder,
        ]);

        return [
            'url'      => $result['secure_url'],
            'publicId' => $result['public_id'],
        ];
    }

    /**
     * Supprimer un fichier de Cloudinary via son publicId
     */
    public function deleteFromCloudinary(string $publicId): void
    {
        Cloudinary::uploadApi()->destroy($publicId);
    }
}