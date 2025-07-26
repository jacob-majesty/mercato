<?php

namespace App\Service;

use App\Interfaces\LogRepositoryInterface;
use App\Model\Log; // Certifique-se de que o Model\Log está importado
use DateTime; // Para o timestamp do log
use Exception;

class LogService
{
    private LogRepositoryInterface $logRepository;

    public function __construct(LogRepositoryInterface $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * Registra uma entrada de log no sistema.
     *
     * @param string $type O tipo de log (ex: 'INFO', 'WARNING', 'ERROR', 'AUTH', 'PURCHASE').
     * @param string $action A descrição da ação que gerou o log.
     * @param int|null $userId O ID do usuário associado ao log (nulo se não houver).
     * @param array|null $details Detalhes adicionais do log (serão convertidos para JSON).
     * @return Log O objeto Log criado e salvo.
     * @throws Exception Se o log não puder ser salvo.
     */
    public function log(string $type, string $action, ?int $userId = null, ?array $details = null): Log
    {
        $logDetails = $details ? json_encode($details) : null;

        $logEntry = new Log(
            $type,
            $action,
            $userId,
            $logDetails,
            null, // ID será gerado pelo repositório
            new DateTime()
        );

        try {
            return $this->logRepository->save($logEntry);
        } catch (Exception $e) {
            // Em um ambiente de produção, você registraria isso em um log de sistema de baixo nível
            error_log("Erro ao salvar log: " . $e->getMessage() . " - Tipo: {$type}, Ação: {$action}");
            throw new Exception("Falha ao registrar log: " . $e->getMessage());
        }
    }

    /**
     * Obtém todos os logs do sistema.
     * @return Log[]
     */
    public function getAllLogs(): array
    {
        try {
            return $this->logRepository->findAll();
        } catch (Exception $e) {
            error_log("Erro ao buscar todos os logs: " . $e->getMessage());
            throw new Exception("Falha ao buscar logs: " . $e->getMessage());
        }
    }

    /**
     * Obtém logs associados a um usuário específico.
     * @param int $userId
     * @return Log[]
     */
    public function getUserLogs(int $userId): array
    {
        try {
            return $this->logRepository->findByUserId($userId);
        } catch (Exception $e) {
            error_log("Erro ao buscar logs do usuário {$userId}: " . $e->getMessage());
            throw new Exception("Falha ao buscar logs do usuário: " . $e->getMessage());
        }
    }
}
