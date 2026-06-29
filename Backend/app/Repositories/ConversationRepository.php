<?php

namespace App\Repositories;

use App\Models\Conversation;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ConversationRepository implements ConversationRepositoryInterface
{
    public function findById(int $id): ?Conversation
    {
        return Conversation::with([
            'citizen',
            'municipalAdmin',
            'incident',
            'lastMessage',
        ])->find($id);
    }

    public function getByCitizenId(int $citizenId): Collection
    {
        return Conversation::where('citizen_id', $citizenId)
            ->with(['municipalAdmin', 'incident', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();
    }

    public function getByMunicipalAdminId(int $adminId): Collection
    {
        return Conversation::where('municipal_admin_id', $adminId)
            ->with(['citizen', 'incident', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();
    }

    public function findExisting(int $citizenId, int $adminId, ?int $incidentId): ?Conversation
    {
        return Conversation::where('citizen_id', $citizenId)
            ->where('municipal_admin_id', $adminId)
            ->where('incident_id', $incidentId)
            ->where('status', 'open')
            ->first();
    }

    public function create(array $data): Conversation
    {
        return Conversation::create($data);
    }

    public function update(Conversation $conversation, array $data): Conversation
    {
        $conversation->update($data);
        $fresh = $conversation->fresh();

        if (!$fresh) {
            throw new \Exception('Conversation introuvable après mise à jour', 500);
        }

        return $fresh;
    }

    public function delete(Conversation $conversation): void
    {
        $conversation->delete();
    }
}