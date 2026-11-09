# Visão geral

**Projeto:** Plataforma Fitness — sistema de gestão de redes de academias
**Versão:** 1.3 (fundação + Fases 0–6 + correções de escopo 1–3)
**Stack:** PHP 8.2+ · MariaDB · HTML5 · CSS3 · JavaScript (ES modules)

## Objetivo

Estabelecer a fundação técnica de uma plataforma SaaS para redes de academias,
com arquitetura MVC, separação de responsabilidades, segurança por padrão e
identidade visual configurável.

Sobre a fundação foram implementadas as Fases 0–6 do documento 02: níveis,
perfis, permissões, redes, unidades, regiões, vínculos, contexto, auditoria e
administração. Os módulos funcionais de alunos, financeiro, treinos e comunidade
ainda não foram implementados.

## Escopo entregue

- Estrutura de diretórios e autoload PSR-4 via Composer.
- Front Controller e inicialização centralizada.
- Roteamento web e API versionada com middleware.
- Configuração por ambiente com `.env`.
- Camada de dados com PDO, Repository e migrations.
- Design System com tokens CSS, temas escuro e claro e layout responsivo.
- Sessões seguras, CSRF e autenticação real em `usuarios`.
- RBAC híbrido com perfis, permissões explícitas e escopo.
- Redes, regiões, unidades, vínculos e contexto validado no backend.
- Administração de redes, regiões, unidades, usuários, perfis e vínculos.
- Auditoria de ações administrativas relevantes.
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
   ├── Middleware (CSRF, Auth, Scope, Permission, Guest)
   └── Controller
          ├── Service (regras de negócio e autorização)
          ├── Repository (persistência com escopo)
          └── View (HTML) ou JSON (API)
```

### Camadas

| Camada | Responsabilidade |
| --- | --- |
| Controllers | Recebem a requisição, acionam serviços e devolvem respostas |
| Services | Regras de negócio, autorização, contexto e auditoria |
| Repositories | Consultas e persistência |
| Models | Entidades e conceitos do domínio |
| Middleware | CSRF, autenticação, escopo, permissão e verificações transversais |
| Validators | Validação de entrada no backend |
| DTOs | Transporte explícito de dados entre camadas |
| Support | Configuração, container, sessão, resposta, view e utilitários |
| Exceptions | Erros da aplicação e mapeamento HTTP |

## Autorização

- RBAC híbrido: perfil + permissões explícitas + escopo.
- Níveis numéricos por perfil.
- `usuario_perfil` com escopo de rede/unidade e região opcional.
- `usuario_permissao` para concessões e negações explícitas.
- Perfis personalizados com escopo de rede; perfis de sistema/globais somente
  leitura para não Superadmin.
- Matriz de concessão estrita: nível inferior ao ator, exceto Superadmin.
- Contexto de rede/unidade/região validado no backend.
- Vínculos e opções de formulário filtrados pelo escopo.
- Deny by default.
- Bloqueio de escalada de privilégio.
- Auditoria de login, logout e troca de contexto.

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

## Estado da validação

- Banco `academia` criado no MariaDB, migrations aplicadas e persistência
  validada.
- API `GET /api/v1/health` retorna `database: ok`.
- Suíte com 49 testes e 318 asserções aprovados.
- Fluxo web validado: login, contexto, permissões, administração e auditoria.
- CRUD administrativo e anti-escalada validados em testes e no fluxo web.
- Prioridades 2–3 validadas: perfis com escopo de rede, matriz de concessão,
  contexto regional, filtragem de vínculos e limpeza dos dados de teste.

## Pendências conhecidas

- Política formal de retenção e mascaramento da auditoria (LGPD).
- Interface para permissões explícitas em `usuario_permissao`.
- Módulos funcionais de alunos, financeiro, treinos e comunidade.
- Testes externos de carga, segurança e acessibilidade.

## Documentos relacionados

- `01-estrutura-inicial.md` — registro da implementação da fundação e Fases 0–6
- `02-padroes-de-desenvolvimento.md` — convenções de código
- `03-banco-de-dados.md` — schema, seeds, perfis, permissões e administração
- `decisoes-tecnicas/` — registros de decisões arquiteturais
