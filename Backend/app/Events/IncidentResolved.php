<?php

namespace App\Events;

use App\Models\Incident;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncidentResolved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Incident $incident,
        public int $citizenId,
        public int $notificationId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->citizenId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'incident.resolved';
    }

    public function broadcastWith(): array
    {
        return [
            'notification_id' => $this->notificationId,
            'incident_id'     => $this->incident->id,
            'title'           => $this->incident->title,
            'zone'            => $this->incident->zone?->name,
            'category'        => $this->incident->category?->name,
            'resolved_at'     => $this->incident->resolved_at,
        ];
    }
}