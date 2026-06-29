<?php

namespace App\Repositories\Contracts;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

interface CommentRepositoryInterface
{
    public function getByIncidentId(int $incidentId): Collection;
    public function findById(int $id): ?Comment;
    public function create(array $data): Comment;
    public function update(Comment $comment, array $data): Comment;
    public function delete(Comment $comment): void;
}