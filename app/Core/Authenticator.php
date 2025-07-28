<?php

namespace App\Core;

use App\Model\User; // Importa a classe User

/**
 * Class Authenticator
 * @package App\Core
 *
 * Gerencia a autenticação e a sessão do usuário.
 * Utiliza as sessões PHP para armazenar o estado de login.
 */
class Authenticator
{
    private const SESSION_KEY = 'user_id';
    private const SESSION_ROLE_KEY = 'user_role';
    private const SESSION_FIRST_NAME_KEY = 'user_first_name';

    /**
     * Inicia a sessão PHP se ainda não estiver ativa.
     * Deve ser chamada no início do script (ex: public/index.php).
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Tenta logar um usuário.
     * @param User $user O objeto User a ser logado.
     */
    public static function login(User $user): void
    {
        self::startSession(); // Garante que a sessão está ativa
        $_SESSION[self::SESSION_KEY] = $user->getId();
        $_SESSION[self::SESSION_ROLE_KEY] = $user->getRole();
        $_SESSION[self::SESSION_FIRST_NAME_KEY] = $user->getFirstName();
    }

    /**
     * Verifica se há um usuário logado.
     * @return bool True se houver um usuário logado, false caso contrário.
     */
    public static function check(): bool
    {
        self::startSession(); // Garante que a sessão está ativa
        return isset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Obtém o ID do usuário logado.
     * @return int|null O ID do usuário logado, ou null se não houver.
     */
    public static function getUserId(): ?int
    {
        self::startSession(); // Garante que a sessão está ativa
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    /**
     * Obtém o papel (role) do usuário logado.
     * @return string|null O papel do usuário logado, ou null se não houver.
     */
    public static function getUserRole(): ?string
    {
        self::startSession(); // Garante que a sessão está ativa
        return $_SESSION[self::SESSION_ROLE_KEY] ?? null;
    }

    /**
     * Obtém o primeiro nome do usuário logado.
     * @return string|null O primeiro nome do usuário logado, ou null se não houver.
     */
    public static function getFirstName(): ?string
    {
        self::startSession(); // Garante que a sessão está ativa
        return $_SESSION[self::SESSION_FIRST_NAME_KEY] ?? null;
    }

    /**
     * Obtém o objeto User completo do usuário logado.
     * Nota: Isso requer uma instância do UserService ou UserRepository.
     * Para simplicidade, este método retorna um User "mock" ou nulo.
     * Em uma aplicação real, você buscaria o usuário do banco de dados.
     * @return User|null O objeto User, ou null se não houver usuário logado.
     */
    public static function user(): ?User
    {
        self::startSession(); // Garante que a sessão está ativa
        if (self::check()) {
            // Em um cenário real, você injetaria um UserService aqui
            // ou buscaria o usuário do banco de dados usando o ID.
            // Por enquanto, retorna um objeto User com base nos dados da sessão.
            // ISSO É UMA SIMPLIFICAÇÃO. Idealmente, você buscaria do DB.
            $userId = self::getUserId();
            $userRole = self::getUserRole();
            $userFirstName = self::getFirstName();

            // Retorna um objeto User básico para evitar erros de tipo
            // Se precisar de mais dados do usuário, você precisará de um UserRepository aqui.
            return new User('session@user.com', $userFirstName, 'Sobrenome', $userRole, 'hashed_password', $userId);

        }
        return null;
    }

    /**
     * Verifica se o usuário logado é um administrador.
     * @return bool
     */
    public static function isAdmin(): bool
    {
        return self::check() && self::getUserRole() === 'admin';
    }

    /**
     * Verifica se o usuário logado é um vendedor.
     * @return bool
     */
    public static function isSeller(): bool
    {
        return self::check() && self::getUserRole() === 'seller';
    }

    /**
     * Verifica se o usuário logado é um cliente.
     * @return bool
     */
    public static function isClient(): bool
    {
        return self::check() && self::getUserRole() === 'client';
    }

    /**
     * Desloga o usuário, destruindo a sessão.
     */
    public static function logout(): void
    {
        self::startSession(); // Garante que a sessão está ativa
        session_unset(); // Remove todas as variáveis de sessão
        session_destroy(); // Destrói a sessão
        $_SESSION = []; // Limpa o array $_SESSION
    }
}
