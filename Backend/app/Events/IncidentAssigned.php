<?php

namespace App\Events;

use App\Models\Assignment;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncidentAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Assignment $assignment,
        public int $agentId,
        public int $notificationId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->agentId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'incident.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'notification_id' => $this->notificationId,
            'assignment_id'   => $this->assignment->id,
            'incident_id'     => $this->assignment->incident_id,
            'incident_title'  => $this->assignment->incident?->title,
            'zone'            => $this->assignment->incident?->zone?->name,
            'category'        => $this->assignment->incident?->category?->name,
            'assigned_at'     => $this->assignment->start_time,
        ];
    }
}