<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    /**
     * Étape 1 — Envoyer le lien de réinitialisation par email
     * POST /auth/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try{
            $request->validate([
            'email' => 'required|email',
        ]);

        // Laravel se charge de générer le token et d'envoyer le mail
        // Si l'email n'existe pas, il retourne quand même RESET_LINK_SENT
        // (pour ne pas révéler si un email est enregistré)
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Lien de réinitialisation envoyé.',
            ]);
        }

        // Throttle : trop de tentatives
        return response()->json([
            'message' => __($status),
        ], 429);

        }
        catch (\Exception $e) {
             Log::error('Erreur dans getNearby', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);

            return response()->json([
                'message' => $e->getMessage()
            ],  500);
        }
    }

    /**
     * Étape 2 — Réinitialiser le mot de passe avec le token reçu par mail
     * POST /auth/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try{
            $request->validate([
            'token'                 => 'required|string',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                Log::info('Password reset for user: ' . $user->id);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Mot de passe réinitialisé avec succès.',
            ]);
        }

        // Token invalide ou expiré
        return response()->json([
            'message' => __($status),
        ], 422);
    }
catch (\Exception $e) {
             Log::error('Erreur dans getNearby', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);

            return response()->json([
                'message' => $e->getMessage()
            ],  500);
        }
    }
        
}