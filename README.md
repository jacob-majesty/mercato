
# Mercato

**Mercato** é um sistema simples e funcional de **compra e venda de ingressos ou produtos**, com controle de usuários, clientes e estoque. O projeto foi desenvolvido com foco em simplicidade, organização e boas práticas de desenvolvimento backend em **PHP puro**, utilizando **SQLite** como banco de dados e **Bootstrap** no frontend para uma interface responsiva.

## Funcionalidades

* Cadastro e gerenciamento de produtos
* Controle de estoque
* Venda de ingressos ou produtos
* Cadastro e gestão de clientes
* Controle de usuários do sistema (login e autenticação)
* Relatórios básicos de vendas

## Tecnologias Utilizadas

* **PHP 8+ (POO)**
* **SQLite** (banco de dados leve e embutido)
* **Bootstrap** (interface responsiva com HTML/CSS)
* **HTML5 e CSS3**
* **Arquitetura MVC simplificada**

## Estrutura do Projeto

```
mercato/
├── app/
│   ├── controllers/
│   ├── models/
│   └── views/
├── public/
│   └── index.php (ponto de entrada)
├── config/
│   └── database.php
├── database/
│   └── mercato.sqlite
├── composer.json
├── README.md
```

## Requisitos

* PHP 8.0 ou superior
* Extensão **PDO SQLite** habilitada
* Navegador moderno
* Servidor local (Apache, Nginx ou built-in do PHP)

## Como Rodar o Projeto

1. **Clone o repositório**

```
git clone https://github.com/seuusuario/mercato.git
```

2. **Acesse a pasta do projeto**

```
cd mercato
```

3. **Suba o servidor local**

Com PHP embutido:

```
php -S localhost:8000 -t public
```

Ou configure em um servidor Apache/Nginx apontando para a pasta `public/`.

4. **Acesse no navegador**

```
http://localhost:8000
```

## Banco de Dados

* O projeto já acompanha o arquivo `mercato.sqlite` na pasta `database/`.
* Caso queira reiniciar ou criar um novo banco, você pode editar ou substituir esse arquivo.

## Observações

* Este projeto é didático, com foco em aprender conceitos de backend puro e estruturação de aplicações web.
* Por ser um sistema básico, não possui recursos avançados como APIs REST ou frameworks.

