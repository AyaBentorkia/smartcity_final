<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function createUser(array $data): User;

    public function createToken(User $user, string $tokenName = 'app-token'): string;

    public function deleteCurrentToken(User $user): void;

    public function deleteAllTokens(User $user): void;
}