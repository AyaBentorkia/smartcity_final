<?php

namespace App\Repositories\Contracts;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Collection;

interface ConversationRepositoryInterface
{
    public function findById(int $id): ?Conversation;
    public function getByCitizenId(int $citizenId): Collection;
    public function getByMunicipalAdminId(int $adminId): Collection;
    public function findExisting(int $citizenId, int $adminId, ?int $incidentId): ?Conversation;
    public function create(array $data): Conversation;
    public function update(Conversation $conversation, array $data): Conversation;
    public function delete(Conversation $conversation): void;
}