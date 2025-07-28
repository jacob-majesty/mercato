<?php

namespace App\Interfaces;

use App\Model\Log;

interface LogRepositoryInterface
{
    public function findById(int $id): ?Log;
    public function findAll(): array;
    public function save(Log $log): Log;
    public function delete(int $id): bool;
    public function findByUserId(int $userId): array;
}
