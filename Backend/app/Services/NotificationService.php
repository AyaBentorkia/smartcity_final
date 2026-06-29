<?php

namespace App\Services;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * NotificationService
 *
 * Fournit des méthodes utilitaires pour lire, créer et modifier les
 * notifications utilisateur. Il délègue la persistance au
 * NotificationRepositoryInterface et centralise la logique métier liée
 * aux règles d'accès et aux validations simples.
 *
 * Note: la méthode `notify` est appelée par d'autres services pour
 * créer et journaliser des notifications (ex: incidents, assignments).
 */
class NotificationService
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository
    ) {}

    /**
     * Récupérer toutes les notifications de l'utilisateur connecté
     */
    /**
     * Return all notifications for a given user.
     *
     * Called by: NotificationController@index
     * Endpoint: GET /notifications
     * Allowed users: authenticated users
     *
     * @param int $userId
     * @return Collection<App\Models\Notification>
     */
    public function getMyNotifications(int $userId): Collection
    {
        return $this->notificationRepository->getByUserId($userId);
    }

    /**
     * Récupérer uniquement les notifications non lues
     */
    /**
     * Return unread notifications for a user.
     *
     * Called by: NotificationController@unreadCount or dedicated endpoint
     * Endpoint: GET /notifications/unread or similar
     *
     * @param int $userId
     * @return Collection
     */
    public function getUnread(int $userId): Collection
    {
        return $this->notificationRepository->getUnreadByUserId($userId);
    }

    /**
     * Compter les notifications non lues
     */
    /**
     * Count unread notifications for a user.
     *
     * Called by: NotificationController@unreadCount
     * Endpoint: GET /notifications/unread-count
     *
     * @param int $userId
     * @return int
     */
    public function countUnread(int $userId): int
    {
        return $this->notificationRepository->countUnread($userId);
    }

    /**
     * Marquer une notification comme lue
     */
    /**
     * Mark a notification as read.
     *
     * Called by: NotificationController@markAsRead
     * Endpoint: PATCH /notifications/{id}/read
     * Allowed users: owner of the notification
     *
     * @param int $notificationId
     * @param int $userId
     * @return Notification
     */
    public function markAsRead(int $notificationId, int $userId): Notification
    {
        $notification = $this->notificationRepository->findById($notificationId);

        if (!$notification) {
            throw new \Exception('Notification not found', 404);
        }

        if ($notification->user_id !== $userId) {
            throw new \Exception('Not allowed', 403);
        }

        $updated = $this->notificationRepository->markAsRead($notification);

        Log::info('Notification marked as read', ['notification_id' => $notificationId]);

        return $updated;
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    /**
     * Mark all notifications as read for a user.
     *
     * Called by: NotificationController@markAllAsRead
     * Endpoint: PATCH /notifications/read-all
     * Allowed users: authenticated user
     *
     * @param int $userId
     * @return void
     */
    public function markAllAsRead(int $userId): void
    {
        $this->notificationRepository->markAllAsRead($userId);

        Log::info('All notifications marked as read', ['user_id' => $userId]);
    }

    /**
     * Supprimer une notification
     */
    /**
     * Delete a notification.
     *
     * Called by: NotificationController@destroy (or equivalent)
     * Endpoint: DELETE /notifications/{id}
     * Allowed users: notification owner
     *
     * @param int $notificationId
     * @param int $userId
     * @return void
     */
    public function delete(int $notificationId, int $userId): void
    {
        $notification = $this->notificationRepository->findById($notificationId);

        if (!$notification) {
            throw new \Exception('Notification non trouvée', 404);
        }

        if ($notification->user_id !== $userId) {
            throw new \Exception('Not allowed', 403);
        }

        $this->notificationRepository->delete($notification);

        Log::info('Notification deleted', ['notification_id' => $notificationId]);
    }

    /**
     * Supprimer toutes les notifications de l'utilisateur
     */
    /**
     * Delete all notifications for a user.
     *
     * Called by: NotificationController@deleteAll (or equivalent)
     * Endpoint: DELETE /notifications (or /notifications/clear)
     * Allowed users: authenticated user
     *
     * @param int $userId
     * @return void
     */
    public function deleteAll(int $userId): void
    {
        $this->notificationRepository->deleteAllByUserId($userId);

        Log::info('All notifications deleted', ['user_id' => $userId]);
    }

    /**
     * Create a notification record.
     *
     * Usage: called by other services (IncidentService, AssignmentService)
     * to centralize notification creation logic and logging.
     *
     * @param int $userId
     * @param string $title
     * @param string $body
     * @param string $type
     * @param int|null $incidentId
     * @param int|null $assignmentId
     * @return Notification
     */
    public function notify(int $userId, string $title, string $body, string $type, ?int $incidentId = null, ?int $assignmentId = null): Notification
    {
        $notification = $this->notificationRepository->create([
            'user_id'       => $userId,
            'title'         => $body,
            'body'       => $content,
            'type'          => $type,
            'incident_id'   => $incidentId,
            'assignment_id' => $assignmentId,
            'read_at'       => null,
        ]);

        Log::info('Notification created', [
            'user_id' => $userId,
            'type'    => $type,
        ]);

        return $notification;
    }
}