<?php

namespace App\Repositories\Contracts;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

interface NotificationRepositoryInterface
{
    public function getByUserId(int $userId): Collection;
    public function getUnreadByUserId(int $userId): Collection;
    public function findById(int $id): ?Notification;
    public function create(array $data): Notification;
    public function markAsRead(Notification $notification): Notification;
    public function markAllAsRead(int $userId): void;
    public function delete(Notification $notification): void;
    public function deleteAllByUserId(int $userId): void;
    public function countUnread(int $userId): int;
}