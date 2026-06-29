<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Comment $comment
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('incident.' . $this->comment->incident_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->comment->id,
            'incident_id' => $this->comment->incident_id,
            'user_id'     => $this->comment->user_id,
            'content'     => $this->comment->content,
            'created_at'  => $this->comment->created_at?->toDateTimeString(),
            'user'        => $this->comment->relationLoaded('user')
                                ? $this->comment->user
                                : null,
        ];
    }

    public function broadcastAs(): string
    {
        return 'comment.added';
    }
}