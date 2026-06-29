<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Incident;
use App\Events\CommentAdded;  
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class CommentService
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository
    ) {}

    public function getByIncident(int $incidentId): Collection
    {
        return $this->commentRepository->getByIncidentId($incidentId);
    }

    public function create(int $incidentId, int $userId, array $data): Comment
    {
        $incident = Incident::find($incidentId);

        if (!$incident) {
            throw new \Exception('Incident not found', 404);
        }

        $data['incident_id'] = $incidentId;
        $data['user_id']     = $userId;

        $comment = $this->commentRepository->create($data);
        broadcast(new CommentAdded($comment->load('user')))->toOthers();

        Log::info('Comment created', ['comment_id' => $comment->id]);

        return $comment;
    }

    public function update(int $commentId, array $content): Comment
    {
        $comment = $this->commentRepository->findById($commentId);

        if (!$comment) {
            throw new \Exception('Comment not found', 404);
        }

        $updated = $this->commentRepository->update($comment,$content);

        // Log::info('Comment updated', ['comment_id' => $comment->id]);

        return $updated;
    }

    public function delete(int $commentId, int $userId): void
    {
        $comment = $this->commentRepository->findById($commentId);

        if (!$comment) {
            throw new \Exception('Comment not found', 404);
        }

        $this->commentRepository->delete($comment);

        Log::info('Comment deleted', ['comment_id' => $commentId]);
    }
}