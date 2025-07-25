<?php

namespace App\Repository;

use App\Model\Log;

interface LogRepositoryInterface
{
    public function save(Log $log): Log;
    public function findById(int $id): ?Log;
    public function findAll(): array;
    public function findByUserId(int $userId): array;
    public function findByType(string $type): array;
}