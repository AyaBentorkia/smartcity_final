<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * NotificationRepository
 *
 * Fournit l'accès aux notifications en base : lecture, création,
 * mise à jour du statut de lecture et suppression. Les méthodes
 * retournent des collections prêtes à être renvoyées par les controllers.
 */
class NotificationRepository implements NotificationRepositoryInterface
{
    /**
 * Retrieve all notifications for a user with related incident and assignment, ordered by most recent.
 *
 * @param int $userId
 * @return Collection
 */
    public function getByUserId(int $userId): Collection
    {
        return Notification::where('user_id', $userId)
            ->with(['incident', 'assignment'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
/**
 * Retrieve all unread notifications for a user, ordered by most recent.
 *
 * @param int $userId
 * @return Collection
 */
    public function getUnreadByUserId(int $userId): Collection
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->with(['incident', 'assignment'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
/**
 * Find a notification by its ID.
 *
 * @param int $id
 * @return Notification|null
 */
    public function findById(int $id): ?Notification
    {
        return Notification::find($id);
    }
/**
 * Create a new notification record.
 *
 * @param array $data
 * @return Notification
 */
    public function create(array $data): Notification
    {
        return Notification::create($data);
    }
/**
 * Mark a notification as read by setting its read_at timestamp.
 *
 * @param Notification $notification
 * @return Notification
 */
    public function markAsRead(Notification $notification): Notification
    {
        $notification->update(['read_at' => now()]);
        // $fresh = $notification->fresh();

        // if (!$fresh) {
        //     throw new \Exception('Notification introuvable après mise à jour', 500);
        // }

        // return $fresh;
        return $notification;
    }
/**
 * Mark all unread notifications of a user as read.
 *
 * @param int $userId
 * @return void
 */
    public function markAllAsRead(int $userId): void
    {
        Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]); 
    }
/**
 * Delete a single notification.
 *
 * @param Notification $notification
 * @return void
 */
    public function delete(Notification $notification): void
    {
        $notification->delete();
    }
/**
 * Delete all notifications belonging to a user.
 *
 * @param int $userId
 * @return void
 */
    public function deleteAllByUserId(int $userId): void
    {
        Notification::where('user_id', $userId)->delete();
    }
/**
 * Count the unread notifications for a user.
 *
 * @param int $userId
 * @return int
 */
    public function countUnread(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
}