<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * NotificationController
 *
 * Endpoints relatifs aux notifications utilisateur (historique, marquer
 * comme lu, compter non lus, mettre à jour le token FCM). Le controller
 * est volontairement léger et délègue la logique vers les repositories
 * ou services quand nécessaire.
 */
class NotificationController extends Controller
{
    /**
 * List all notifications for the authenticated user (paginated).
 * Endpoint: GET /notifications | Allowed users: authenticated
 * @return JsonResponse
 */
    public function index()
    {
        $notifications = Notification::where('user_id', auth('api')->id())
            ->with('incident:id,title,status,zone_id')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($notifications);
    }

    // PATCH /notifications/{id}/read — marquer une comme lue
    public function markAsRead(int $id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', auth('api')->id())
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marquée comme lue']);
    }

    /**
 * Mark all notifications as read for the authenticated user.
 * Endpoint: PATCH /notifications/read-all | Allowed users: authenticated
 * @return JsonResponse
 */
    public function markAllAsRead()
    {
        Notification::where('user_id', auth('api')->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Toutes les notifications marquées comme lues']);
    }

    /**
 * Get the unread notification count for the authenticated user.
 * Endpoint: GET /notifications/unread-count | Allowed users: authenticated
 * @return JsonResponse
 */
    public function unreadCount()
    {
        $count = Notification::where('user_id', auth('api')->id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }
    /**
 * Update the FCM push token for the authenticated user.
 * Endpoint: POST /fcm-token | Allowed users: authenticated
 * @param Request $request
 * @return JsonResponse
 */
public function updateFcmToken(Request $request)
{
    $request->validate([
        'fcm_token' => 'required|string',
    ]);

    Auth::user()->update(['fcm_token' => $request->fcm_token]);

    return response()->json(['message' => 'FCM token updated']);
}
}