<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Incident;

class IncidentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
         public Incident $incident,
    public int $adminId,
    public int $notificationId 
    )
    {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
       return [
            new PrivateChannel('user.' . $this->adminId),
        ];
    }
    public function broadcastAs(): string
    {
        return 'incident.created';
    }
    public function broadcastWith(): array
    {
        return [
            'notification_id' => $this->notificationId,
            'id'              => $this->incident->id,
            'title'           => $this->incident->title,
            'description'     => $this->incident->description,
            'status'          => $this->incident->status,
            'zone'            => $this->incident->zone?->name,
            'category'        => $this->incident->category?->name,
            'reported_at'     => $this->incident->reported_at,
        ];
    }
}
