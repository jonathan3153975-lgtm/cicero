# Visão geral

**Projeto:** Plataforma Fitness — sistema de gestão de redes de academias
**Versão:** 1.0 (fundação)
**Stack:** PHP 8.2+ · MariaDB · HTML5 · CSS3 · JavaScript (ES modules)

## Objetivo

Estabelecer a fundação técnica de uma plataforma SaaS para redes de academias,
com arquitetura MVC, separação de responsabilidades, segurança por padrão e
identidade visual configurável.

Esta etapa **não implementa** os módulos de alunos, financeiro, treinos ou
comunidade. Ela entrega a base sobre a qual esses módulos serão construídos.

## Escopo entregue

- Estrutura de diretórios e autoload PSR-4 via Composer.
- Front Controller e inicialização centralizada.
- Roteamento web e API versionada com middleware.
- Configuração por ambiente com `.env`.
- Camada de dados com PDO, Repository e migrations.
- Design System com tokens CSS, temas escuro e claro e layout responsivo.
- Sessões seguras, CSRF, autenticação e autorização de demonstração.
- Tratamento centralizado de erros e logs com Monolog.
- Testes unitários e de integração com PHPUnit.
- Documentação funcional e técnica.

## Arquitetura

```text
Requisição
   │
   ▼
public/index.php  (Front Controller)
   │
   ▼
bootstrap/app.php (Config, Container, Logger, Router, ExceptionHandler)
   │
   ▼
Application::handle()
   │
   ├── Router (FastRoute)
   ├── Middleware (CSRF, Auth, Guest, Role)
   └── Controller
          ├── Service (regras de negócio)
          ├── Repository (persistência)
          └── View (HTML) ou JSON (API)
```

### Camadas

| Camada | Responsabilidade |
| --- | --- |
| Controllers | Recebem a requisição, acionam serviços e devolvem respostas |
| Services | Regras de negócio e orquestração |
| Repositories | Consultas e persistência |
| Models | Entidades e conceitos do domínio |
| Middleware | CSRF, autenticação, autorização e verificações transversais |
| Validators | Validação de entrada no backend |
| DTOs | Transporte explícito de dados entre camadas |
| Support | Configuração, container, sessão, resposta, view e utilitários |
| Exceptions | Erros da aplicação e mapeamento HTTP |

## Segurança

- Credenciais apenas em `.env`, fora do Git.
- Senhas com `password_hash()` e `password_verify()`.
- Sessões com cookies `HttpOnly`, `SameSite` e `Secure` em HTTPS.
- Regeneração do ID de sessão após autenticação.
- Proteção CSRF em `POST`, `PUT`, `PATCH` e `DELETE` das rotas web.
- Escape de saída com `e()` / `htmlspecialchars()`.
- PDO com prepared statements e `ATTR_EMULATE_PREPARES = false`.
- Erros sem stack trace quando `APP_DEBUG=false`.
- Apenas `public/` deve ser exposto pelo servidor.

## Decisões e pendências

As decisões arquiteturais estão em [`decisoes-tecnicas/`](decisoes-tecnicas/).

Estado da validação:

- Banco `academia` criado no MariaDB, migrations aplicadas e persistência
  validada pelos testes de integração.
- API `GET /api/v1/health` retorna `database: ok`.

Pendências conhecidas:

- Autenticação atual é uma demonstração isolada; será substituída pelo módulo
  real descrito em `04-autenticacao-e-permissoes.md`.
- Multiempresa e multiunidade serão modeladas no próximo documento.

## Documentos relacionados

- `01-estrutura-inicial.md` — registro da implementação da fundação
- `02-padroes-de-desenvolvimento.md` — convenções de código
- `decisoes-tecnicas/` — registros de decisões arquiteturais
