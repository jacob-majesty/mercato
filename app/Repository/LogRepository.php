<?php

namespace App\Repository;

use PDO;
use App\Model\Log;
use DateTime;
use Exception;
use App\Interfaces\LogRepositoryInterface;

/**
 * Class LogRepository
 * @package App\Repository
 *
 * Implementação concreta de LogRepositoryInterface para MySQL.
 */
class LogRepository implements LogRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Log
    {
        $stmt = $this->pdo->prepare("SELECT * FROM logs WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToLog($data);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM logs ORDER BY timestamp DESC");
        $logs = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $logs[] = $this->mapToLog($data);
        }
        return $logs;
    }

    public function save(Log $log): Log
    {
        // CORRIGIDO: Usando 'user_id' e 'type' na query SQL, conforme o schema do DB
        $sql = "INSERT INTO logs (type, action, user_id, details, timestamp) VALUES (:type, :action, :user_id, :details, :timestamp)";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([
                'type' => $log->getType(),
                'action' => $log->getAction(),
                'user_id' => $log->getUserId(), // Garante que o nome da coluna é 'user_id'
                'details' => $log->getDetails(),
                'timestamp' => $log->getTimestamp()->format('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            // Loga o erro original para depuração
            error_log("Erro no LogRepository->save(): " . $e->getMessage() . " - Tipo: {$log->getType()}, Ação: {$log->getAction()}");
            throw $e; // Relança a exceção para ser tratada pelo serviço/controlador
        }

        $log->setId((int)$this->pdo->lastInsertId());
        return $log;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM logs WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM logs WHERE user_id = :user_id ORDER BY timestamp DESC");
        $stmt->execute(['user_id' => $userId]);
        $logs = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $logs[] = $this->mapToLog($data);
        }
        return $logs;
    }

    /**
     * Mapeia um array de dados do banco de dados para um objeto Log.
     * @param array $data
     * @return Log
     */
    private function mapToLog(array $data): Log
    {
        return new Log(
            $data['type'],
            $data['action'],
            // Certifica-se de que 'user_id' é acessado como int, pode ser null
            isset($data['user_id']) ? (int)$data['user_id'] : null,
            $data['details'],
            (int)$data['id'],
            new DateTime($data['timestamp'])
        );
    }
}
