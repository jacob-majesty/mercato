# Projeto Prático - Sistema de Compra de Ingressos ou Produtos

## 📄 Visão Geral

Este projeto consiste em um sistema simples de **compra e venda de ingressos ou produtos**, com controle de usuários, clientes e estoque. A aplicação é desenvolvida em **PHP puro**, com banco de dados **SQLite** e formulários HTML.

O código deve seguir o paradigma de orientação a objetos (POO).

---

## 🏗️ Estrutura Geral do Sistema

### Módulos do sistema

- **Usuários**
- **Produtos / Ingressos**
- **Clientes**
- **Compras**

---

## 🧰 Tecnologias Utilizadas

| Tecnologia | Finalidade |
|------------|------------|
| PHP        | Lógica de backend e sessões via cookies |
| HTML       | Formulários de entrada                  |
| SQLite     | Banco de dados local `.db`              |
| CSS        | Estilização básica (opcional)           |
| JavaScript | Opcional (não entra na avaliação)       |

---

## 🔐 Regras de Autenticação

- Após o login, os dados do usuário são armazenados via `$_SESSION`.
- Páginas protegidas devem validar a sessão do usuário.
- A sessão deve conter ao menos `id_usuario` e `email`.
- Informações sensíveis não devem ser exibidas no navegador.

---

## 🧠 Regras de Negócio

### Produtos / Ingressos

- Só podem ser comprados se `quantidade - reservado > 0`.
- Se houver **1 unidade restante**:
  - Ela será **reservada por 2 minutos**.
  - Se a compra não for concluída nesse tempo, a reserva expira.
  - A reserva pode ser controlada via `data_reserva` + `time()`.

### Usuários

- Só podem visualizar clientes que compraram seus próprios produtos.
- Só podem cadastrar produtos via formulários com `method="POST"`.
- Não podem editar/excluir produtos de outros usuários.

### Clientes

- Não podem ver dados de outros clientes.
- São cadastrados manualmente ou durante a compra.

---

## 📄 Funcionalidades por Módulo

### Usuários

- [ ] Criar (formulário HTML)
- [ ] Editar e deletar (somente os próprios dados)
- [ ] Visualizar lista (restrita)

### Produtos / Ingressos

- [ ] Criar, editar, deletar, visualizar
- [ ] Reserva com `data_reserva`
- [ ] Bloqueio de 2 minutos para o último item

### Clientes

- [ ] Criar, editar, deletar (restrito)
- [ ] Visualização restrita por usuário

### Compras

- [ ] Comprar produto (com controle de estoque)
- [ ] Cancelar reserva após 2 minutos
- [ ] Exibir "Produto indisponível" se esgotado

---

## 📥 Fluxo de Compra

1. Cliente acessa produto com estoque.
2. Se for o último item:
   - O sistema realiza a reserva.
3. Cliente deve confirmar a compra em até 2 minutos:
   - Se confirmar: o estoque é reduzido.
   - Caso contrário: o item volta para o estoque.

---

## 🧪 Segurança

- Todos os dados dos formulários devem ser **validados e sanitizados** (`htmlspecialchars`, `filter_var` etc.).
- A autenticação por sessão deve ser verificada em todas as páginas protegidas.
- Ações como editar/deletar devem conferir **permissão do usuário**.

> Se optar por um sistema de ingressos, é necessário mostrar o evento ao qual o ingresso se refere.

---

## ✨ Bônus (opcionais)

1. **Docker Compose**: separar aplicação PHP e banco em containers distintos.
2. **Sistema de logs** de compras de ingressos/produtos.
3. **Histórico de compras**: mostrar todas as compras do usuário.
4. **Carrinho de compras**: adicionar múltiplos itens antes da finalização.
5. **Geração de comprovante em PDF**:
   - Exemplo: `mpdf/mpdf` ou `dompdf/dompdf`.
6. **Códigos de desconto / cupons**: campo promocional com desconto.

---

## 📘 Documentação (README.md)

O projeto deve conter um arquivo `README.md` com:

- ✅ Instruções para rodar o sistema (**obrigatório**)
- ✅ Diagrama simples do funcionamento
- ✅ Explicação dos bônus implementados (opcional)
- ✅ Checklist do que foi feito (**obrigatório**)
