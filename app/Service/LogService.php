<?php

namespace App\Service;

use App\Repository\LogRepositoryInterface;
use App\Model\Log;
use DateTime;
use Exception;

class LogService
{
    private LogRepositoryInterface $logRepository;

    public function __construct(LogRepositoryInterface $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * Registra um evento no sistema.
     * @param string $type Tipo do log (INFO, WARNING, ERROR, PURCHASE, etc.).
     * @param string $action Descrição da ação.
     * @param int|null $userId ID do usuário relacionado (opcional).
     * @param array|null $details Detalhes adicionais (serão convertidos para JSON).
     * @return Log O objeto Log salvo.
     */
    public function log(string $type, string $action, ?int $userId = null, ?array $details = null): Log
    {
        $logDetails = $details ? json_encode($details) : null;
        $log = new Log($type, $action, $userId, $logDetails, null, new DateTime());
        return $this->logRepository->save($log);
    }

    public function getAllLogs(): array
    {
        return $this->logRepository->findAll();
    }

    public function getUserLogs(int $userId): array
    {
        return $this->logRepository->findByUserId($userId);
    }
}