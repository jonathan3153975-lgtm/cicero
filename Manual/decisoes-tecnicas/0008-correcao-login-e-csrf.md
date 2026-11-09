# 0008 — Correção de login e recuperação de CSRF

**Data:** 2026-11-09
**Status:** aceita
**Relacionados:** `0002-autenticacao-de-demonstracao.md`, `0004-rbac-perfis-niveis-escopo.md`

## Contexto

O usuário relatou não conseguir entrar no sistema. A investigação encontrou:

1. A mensagem de credenciais inválidas era gravada como string, mas a view
   acessava o índice `[0]`, exibindo apenas a primeira letra (“C”).
2. Falhas de CSRF no login retornavam uma página 403, sem oferecer recuperação.
3. Falhas de autenticação e CSRF não eram registradas no log da aplicação.

Também foi confirmado que o login com as credenciais corretas funciona em
`http://localhost` e no servidor embutido.

## Decisões

1. `AuthController::failedAttempt()` normaliza todas as mensagens para arrays
   antes de gravá-las no flash.
2. A view de login aceita erros em formato string ou array, exibindo sempre a
   mensagem completa.
3. `CsrfMiddleware` detecta falha de CSRF em `POST /login` e redireciona para
   `/login` com mensagem “Sua sessão expirou. Recarregue a página e tente
   novamente.”, em vez de retornar 403.
4. Falhas de autenticação e CSRF passam a ser registradas no log, sem senhas.
5. O token CSRF continua vinculado à sessão; a página de login já é enviada com
   `Cache-Control: no-store, no-cache, must-revalidate`.

## Consequências

- Erros de credenciais agora são legíveis.
- Sessão expirada no login permite nova tentativa imediata.
- Logs permitem diagnosticar tentativas inválidas.
- Credenciais de demonstração: `admin@local.test` / `Admin@123`.
- Suíte atual: 65 testes e 493 asserções aprovados.
