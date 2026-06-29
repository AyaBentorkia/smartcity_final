<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    // ─── WEB (Vue.js) ────────────────────────────────────────────────────────

    /**
     * Retourner l'URL de redirection Google pour le SPA Vue
     * GET /api/auth/google
     */
    public function redirect(): JsonResponse
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    /**
     * Callback Google pour le web → JWT → redirect frontend Vue
     * GET /api/auth/google/callback
     */
    public function callback(): RedirectResponse
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = $this->findOrCreateUser($googleUser);
            $token = JWTAuth::fromUser($user);

            return redirect("{$frontendUrl}/auth/google/callback?token={$token}");
        } catch (\Exception $e) {
            Log::error('Google OAuth Web error: ' . $e->getMessage());
            return redirect("{$frontendUrl}/login?error=google_auth_failed");
        }
    }

    // ─── MOBILE (Flutter) ────────────────────────────────────────────────────

    /**
     * Initier OAuth Google pour l'app mobile Flutter.
     * Le redirect_uri pointe vers le deep link de l'app mobile.
     *
     * GET /api/auth/google/mobile
     *
     * Flutter ouvre cette URL dans le navigateur système.
     * Google → callback Laravel → Laravel redirige vers le deep link Flutter.
     */
    public function mobileRedirect(): RedirectResponse
    {
        // On surcharge le redirect_uri pour pointer vers notre callback mobile
        return Socialite::driver('google')
            ->stateless()
            ->with(['redirect_uri' => config('services.google.mobile_redirect')])
            ->redirect();
    }

    /**
     * Callback Google pour l'app mobile Flutter.
     * Après auth Google → génère un JWT → redirige vers le deep link Flutter.
     *
     * GET /api/auth/google/mobile/callback
     *
     * Flutter intercepte : com.smartcitymobile://auth/callback?token=JWT
     */
    public function mobileCallback(): RedirectResponse
    {
        // Scheme deep link de l'app Flutter (configuré dans AndroidManifest + Info.plist)
        $mobileScheme = config('app.mobile_deep_link', 'com.smartcitymobile://auth/callback');

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->with(['redirect_uri' => config('services.google.mobile_redirect')])
                ->user();

            $user = $this->findOrCreateUser($googleUser);
            $token = JWTAuth::fromUser($user);

            // Rediriger vers le deep link Flutter avec le JWT
            return redirect("{$mobileScheme}?token={$token}");
        } catch (\Exception $e) {
            Log::error('Google OAuth Mobile error: ' . $e->getMessage());
            // En cas d'erreur, rediriger vers le deep link avec un code d'erreur
            return redirect("{$mobileScheme}?error=google_auth_failed");
        }
    }

    // ─── Logique partagée ─────────────────────────────────────────────────────

    /**
     * Trouve un utilisateur existant par google_id ou email,
     * ou en crée un nouveau citoyen vérifié via Google.
     */
    private function findOrCreateUser($googleUser): User
    {
        $user = User::where('google_id', $googleUser->getId())
                    ->orWhere('email', $googleUser->getEmail())
                    ->first();

        if ($user) {
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'password'          => bcrypt(Str::random(32)),
                'role'              => UserRole::CITIZEN->value,
                'status'            => 'active',
                'email_verified_at' => now(),
            ]);
        }

        return $user;
    }
}