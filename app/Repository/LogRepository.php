<?php

namespace App\Repository;

use App\Model\Log;
use PDO;
use DateTime;

class LogRepository implements LogRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function mapLog(array $data): Log
    {
        return new Log(
            $data['tipo'],
            $data['acao'],
            (int)$data['id_usuario'] ?? null,
            $data['detalhes'],
            $data['id_log'],
            new DateTime($data['timestamp'])
        );
    }

    public function save(Log $log): Log
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO logs (tipo, id_usuario, acao, timestamp, detalhes)
             VALUES (:type, :userId, :action, :timestamp, :details)"
        );
        $stmt->execute([
            ':type' => $log->getType(),
            ':userId' => $log->getUserId(),
            ':action' => $log->getAction(),
            ':timestamp' => $log->getTimestamp()->format('Y-m-d H:i:s'),
            ':details' => $log->getDetails()
        ]);
        $reflection = new \ReflectionProperty($log, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($log, (int)$this->pdo->lastInsertId());
        return $log;
    }

    public function findById(int $id): ?Log
    {
        $stmt = $this->pdo->prepare("SELECT * FROM logs WHERE id_log = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->mapLog($data) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM logs ORDER BY timestamp DESC");
        $logs = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $logs[] = $this->mapLog($data);
        }
        return $logs;
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM logs WHERE id_usuario = :userId ORDER BY timestamp DESC");
        $stmt->execute([':userId' => $userId]);
        $logs = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $logs[] = $this->mapLog($data);
        }
        return $logs;
    }

    public function findByType(string $type): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM logs WHERE tipo = :type ORDER BY timestamp DESC");
        $stmt->execute([':type' => $type]);
        $logs = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $logs[] = $this->mapLog($data);
        }
        return $logs;
    }
}