<?php

namespace App\Model;

use DateTime;

/**
 * Class User
 * @package App\Model
 *
 * Representa um usuário genérico no sistema.
 * Contém atributos e métodos comuns a todas as funções de usuário (Admin, Seller, Client).
 */
class User
{
    protected ?int $id;
    protected string $email;
    protected string $firstName;
    protected string $lastName;
    protected string $role; // 'admin', 'seller', 'client'
    protected string $pswd; // Senha criptografada
    protected DateTime $createdAt;

    /**
     * Construtor da classe User.
     *
     * @param string $email O email do usuário.
     * @param string $firstName O primeiro nome do usuário.
     * @param string $lastName O sobrenome do usuário.
     * @param string $role O papel do usuário (ex: 'admin', 'seller', 'client').
     * @param string $pswd A senha (já criptografada) do usuário.
     * @param int|null $id O ID do usuário (opcional, para usuários existentes).
     * @param DateTime|null $createdAt A data de criação do usuário (opcional, para usuários existentes).
     */
    public function __construct(
        string $email,
        string $firstName,
        string $lastName,
        string $role,
        string $pswd,
        ?int $id = null,
        ?DateTime $createdAt = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->role = $role;
        $this->pswd = $pswd;
        $this->createdAt = $createdAt ?? new DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getPswd(): string
    {
        return $this->pswd;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    // Setters (para atributos que podem ser alterados após a criação)
    public function setId(int $id): void
    {
        // Geralmente, o ID só é setado uma vez (após a inserção no DB)
        if ($this->id === null) {
            $this->id = $id;
        } else {
            throw new \LogicException("ID já definido e não pode ser alterado.");
        }
    }
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * Define a senha do usuário. A senha deve ser criptografada antes de ser passada.
     * @param string $pswd A senha criptografada.
     */
    public function setPswd(string $pswd): void
    {
        $this->pswd = $pswd;
    }

    /**
     * Cria um novo usuário.
     * Esta função representaria a lógica de persistência para um novo usuário no banco de dados.
     *
     * @param array $userData Dados do usuário para criação.
     * @return User Retorna a instância do usuário criado.
     */
    public static function createUser(array $userData): User
    {
      
        return new User(
            $userData['email'],
            $userData['firstName'],
            $userData['lastName'],
            $userData['role'],
            $userData['pswd'] // Assumindo que a senha já está hasheada aqui
        );
    }

    /**
     * Edita o perfil do usuário.
     * Esta função representaria a lógica de atualização no banco de dados.
     *
     * @param array $data Os dados a serem atualizados (ex: ['firstName' => 'NovoNome']).
     * @return bool Retorna true se a edição for bem-sucedida, false caso contrário.
     */
    public function editProfile(array $data): bool
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
            
                $this->$key = $value;
            }
        }
       
        return true; 
    }

    /**
     * Deleta um usuário.
     * Esta função representaria a lógica de remoção no banco de dados.
     *
     * @return bool Retorna true se a exclusão for bem-sucedida, false caso contrário.
     */
    public function deleteUser(): bool
    {
        return true; 
    }

    /**
     * Realiza o login do usuário.
     *
     * @param string $email O email fornecido para login.
     * @param string $password A senha fornecida para login.
     * @return User|null Retorna a instância do usuário logado se as credenciais forem válidas, null caso contrário.
     */
    public static function login(string $email, string $password): ?User
    {
    
        if ($email === "teste@example.com" && $password === "senha123") {
            // Supondo que você recuperaria os dados do DB aqui e os passaria para o construtor.
            $user = new User($email, "Nome", "Sobrenome", "client", password_hash("senha123", PASSWORD_DEFAULT), 1);
            $_SESSION['id_usuario'] = $user->getId(); 
            $_SESSION['email'] = $user->getEmail(); 
         
            return $user;
        }
        return null;
    }

    /**
     * Realiza o logout do usuário, destruindo a sessão.
     * @return void
     */
    public function logout(): void
    {
        // Lógica para destruir a sessão (ex: session_unset(), session_destroy()).
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
    }
}