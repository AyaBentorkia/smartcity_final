<?php

namespace App\Repositories\Contracts;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

interface MessageRepositoryInterface
{
    public function getByConversationId(int $conversationId): Collection;
    public function findById(int $id): ?Message;
    public function create(array $data): Message;
    public function markAsRead(int $conversationId, int $userId): void;
    public function countUnread(int $conversationId, int $userId): int;
    public function delete(Message $message): void;
}