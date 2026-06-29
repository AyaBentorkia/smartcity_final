<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

// Conversations — participants seulement
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    return $conversation &&
           ($user->id === $conversation->citizen_id ||
            $user->id === $conversation->municipal_admin_id);
});

// Incidents — tous les utilisateurs authentifiés
Broadcast::channel('incident.{incidentId}', function ($user) {
    return auth('api')->check();
});

// User — lui-même seulement
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return $user->id === (int) $userId;
});