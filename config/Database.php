<?php

namespace App\Config;

use PDO;
use PDOException;
use Exception;

/**
 * Class Database
 * @package App\Config
 *
 * Gerencia a conexão com o banco de dados usando PDO.
 */
class Database
{
    private static ?PDO $instance = null; // Instância única de PDO (Singleton)

    // Detalhes da conexão com o banco de dados
    private const DB_HOST = 'mysql';
    private const DB_NAME = 'mercato_db'; // Nome do banco de dados definido no schema.sql
    private const DB_USER = 'user';    // Usuário padrão do MySQL/MariaDB (ajuste se for diferente)
    private const DB_PASS = 'secret';        // Senha padrão do MySQL/MariaDB (ajuste se for diferente)
    private const DB_CHARSET = 'utf8mb4';

    /**
     * Obtém a instância da conexão PDO.
     * Implementa o padrão Singleton para garantir uma única conexão.
     *
     * @return PDO A instância da conexão PDO.
     * @throws Exception Se a conexão com o banco de dados falhar.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=" . self::DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lançar exceções em caso de erro
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retornar arrays associativos por padrão
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Desativar emulação de prepared statements para segurança
            ];

            try {
                self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
            } catch (PDOException $e) {
                // Em um ambiente de produção, você registraria o erro (log)
                // e não exporia a mensagem de erro diretamente ao usuário.
                // Para desenvolvimento, pode ser útil.
                throw new Exception("Erro de conexão com o banco de dados: " . $e->getMessage(), (int)$e->getCode(), $e);
            }
        }
        return self::$instance;
    }
}
