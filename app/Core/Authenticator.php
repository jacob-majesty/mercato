<?php

namespace App\Core;

use App\Model\User; 

/**
 * Gerenciamento de Autenticação/Sessão
 * Gerencia o estado de autenticação do usuário.
 */

class Authenticator
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(User $user): void
    {
        self::startSession();
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_role'] = $user->getRole();
        $_SESSION['logged_in'] = true;
        // Não armazene informações sensíveis como senha aqui
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = []; // Limpa todas as variáveis de sessão
        session_destroy(); // Destrói a sessão
        session_unset(); // Remove variáveis da sessão
    }

    public static function check(): bool
    {
        self::startSession();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public static function user(): ?User
    {
        self::startSession();
        if (self::check()) {
            // Idealmente, buscar o usuário completo do banco de dados para ter certeza dos dados mais recentes
            // Por simplicidade, retornamos um User com base nos dados da sessão
            // Você precisaria de um UserRepository e UserService aqui para buscar o User real
            // Ex: (new UserService(new UserRepository(Database::getConnection())))->getUserById($_SESSION['user_id']);
            return new User(
                $_SESSION['user_email'],
                'Desconhecido', // firstName placeholder
                'Desconhecido', // lastName placeholder
                $_SESSION['user_role'],
                'hashed_password_placeholder', // pswd placeholder
                $_SESSION['user_id']
            );
        }
        return null;
    }

    public static function isAdmin(): bool
    {
        return self::check() && self::user()->getRole() === 'admin';
    }

    public static function isSeller(): bool
    {
        return self::check() && self::user()->getRole() === 'seller';
    }

    public static function isClient(): bool
    {
        return self::check() && self::user()->getRole() === 'client';
    }

    public static function getUserId(): ?int
    {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUserRole(): ?string
    {
        self::startSession();
        return $_SESSION['user_role'] ?? null;
    }
}