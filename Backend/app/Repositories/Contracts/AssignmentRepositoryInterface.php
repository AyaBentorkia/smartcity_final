<?php

namespace App\Repositories\Contracts;

use App\Models\Assignment;
use Illuminate\Database\Eloquent\Collection;

interface AssignmentRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?Assignment;

    public function getByAgentId(int $agentId): Collection;

    public function getByAssignedBy(int $adminId): Collection;

    public function create(array $data): Assignment;

    public function update(Assignment $assignment, array $data): Assignment;

    public function delete(Assignment $assignment): void;
}