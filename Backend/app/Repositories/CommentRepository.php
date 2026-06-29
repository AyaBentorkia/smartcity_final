<?php

namespace App\Repositories;

use App\Models\Comment;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CommentRepository implements CommentRepositoryInterface
{
    /**
 * Retrieve all comments for a given incident, ordered by most recent.
 *
 * @param int $incidentId
 * @return Collection
 */
    public function getByIncidentId(int $incidentId): Collection
    {
        return Comment::where('incident_id', $incidentId)
            ->with('user:id,name,role')
            ->orderBy('created_at', 'desc')
            ->get();
    }
/**
 * Find a comment by its ID with its author relation.
 *
 * @param int $id
 * @return Comment|null
 */
    public function findById(int $id): ?Comment
    {
        return Comment::with(['user'])->find($id);
    }
/**
 * Create a new comment and return it with its author.
 *
 * @param array $data
 * @return Comment
 */
    public function create(array $data): Comment
    {
        $comment = Comment::create($data);
    return $comment->load('user:id,name,role');  
    }

    /**
 * Update an existing comment.
 *
 * @param Comment $comment
 * @param array $content
 * @return Comment
 */
    public function update(Comment $comment, array $content): Comment
    {
        $comment->update(['content' => $content]);
        $fresh = $comment->fresh();

        if (!$fresh) {
            throw new \Exception('Commentaire introuvable après mise à jour', 500);
        }

        return $fresh->load('user:id,name,role');
    }

    /**
 * Delete a comment.
 *
 * @param Comment $comment
 * @return void
 */
    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}