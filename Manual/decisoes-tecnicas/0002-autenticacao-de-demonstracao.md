# 0002 — Autenticação de demonstração na fundação

**Data:** 2026-11-08
**Status:** provisória

## Contexto

A Etapa 5 exige middleware de autenticação, autorização e sessões seguras. A
modelagem de usuários, vínculos e permissões pertence ao documento seguinte
(`02-modelagem-banco-de-dados.md` / `04-autenticacao-e-permissoes.md`).

## Decisão

- Implementar o fluxo completo de login/logout com sessão e CSRF.
- Validar credenciais contra `DEMO_EMAIL` e `DEMO_PASSWORD_HASH` definidos no
  `.env`, usando `password_verify()`.
- Registrar o usuário autenticado na sessão (`auth.user_id`, `auth.user`).
- Aplicar `AuthMiddleware`, `GuestMiddleware` e `RoleMiddleware`.
- Manter um controle simples de tentativas de login por sessão.
- Não criar tabela de usuários nesta etapa.

## Consequências

- O fluxo demonstra sessões seguras, regeneração de ID e autorização por papel.
- O usuário autenticado é fictício (`admin`) e não deve ser usado em produção.
- O módulo real deve substituir `AuthController`, remover as variáveis `DEMO_*`
  e buscar usuários/vínculos no banco.
- A interface de login e os middlewares podem ser reaproveitados.
