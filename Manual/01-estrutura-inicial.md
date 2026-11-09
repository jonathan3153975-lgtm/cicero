# Estrutura inicial — registro da implementação

**Documento de referência:** `projeto/1 - Estrutura inicial.md`
**Status:** fundação implementada; Fases 0–6 do documento 02 implementadas
**Data:** 2026-11-08

## 1. Resumo

A fundação foi implementada sobre a raiz do repositório. O documento de
especificação permanece em `projeto/`, e a aplicação ocupa os diretórios
descritos na seção 3 da especificação.

Sobre essa base foram implementadas as Fases 0–6 do documento 02: níveis,
perfis, permissões, redes, unidades, regiões, vínculos, contexto, auditoria e
administração.

## 2. Andamento por etapa da fundação

### Etapa 1 — Preparar o ambiente

- [x] Repositório Git
- [x] Estrutura de pastas
- [x] PHP 8.4 e Composer 2.8 verificados
- [x] `composer.json`
- [x] Dependências instaladas (`composer.lock` versionado)
- [x] `.gitignore`
- [x] `.env.example` e `.env`
- [x] Banco MariaDB `academia` criado e validado (`utf8mb4`)

### Etapa 2 — Inicialização MVC

- [x] `public/index.php`
- [x] `bootstrap/app.php`
- [x] Dotenv
- [x] Roteador (FastRoute)
- [x] Carregamento de `routes/web.php` e `routes/api.php`
- [x] Tratamento centralizado de exceções
- [x] Logger (Monolog)
- [x] Rota de saúde: `GET /api/v1/health`

### Etapa 3 — Camada de dados

- [x] Conexão PDO
- [x] Transações
- [x] Padrão de Repository
- [x] Padrão de migrations
- [x] Primeira migration de teste (`health_checks`)
- [x] Conexão e persistência validadas

### Etapa 4 — Design System

- [x] Reset e estilos base
- [x] Tokens CSS
- [x] Tema escuro e tema claro
- [x] Tipografia e espaçamentos
- [x] Botões e formulários
- [x] Cards e tabelas
- [x] Sidebar e header
- [x] Layout responsivo
- [x] Tela inicial de demonstração

### Etapa 5 — Segurança e qualidade

- [x] Sessões seguras
- [x] Autenticação real em `usuarios`
- [x] Middleware de autenticação
- [x] Autorização por permissão (`PermissionMiddleware`)
- [x] Validação de contexto (`ScopeMiddleware`)
- [x] Proteção CSRF
- [x] Escape de saída
- [x] PHPUnit configurado
- [x] Padrão de formatação
- [x] Arquivos privados fora de `public/`
- [x] Documentação atualizada

## 3. Dependências instaladas

| Pacote | Versão |
| --- | --- |
| `vlucas/phpdotenv` | v5.8.0 |
| `monolog/monolog` | 3.12.1 |
| `nikic/fast-route` | 1.3.1 |
| `phpunit/phpunit` (dev) | 11.5.57 |
| `friendsofphp/php-cs-fixer` (dev) | v3.95.29 |

## 4. Rotas

### Web

| Método | Caminho | Ação | Middleware |
| --- | --- | --- | --- |
| GET | `/` | `HomeController@index` | — |
| GET | `/login` | `AuthController@showLogin` | Guest |
| POST | `/login` | `AuthController@login` | Guest + CSRF |
| POST | `/logout` | `AuthController@logout` | Auth + CSRF |
| GET | `/contexto` | `ContextController@index` | Auth |
| POST | `/contexto` | `ContextController@switch` | Auth + CSRF |
| GET | `/dashboard` | `DashboardController@index` | Auth + Scope + `dashboard.visualizar` |
| GET | `/configuracoes` | `SettingsController@index` | Auth + Scope + `configuracao.visualizar` |

### Administração

| Método | Caminho | Permissão |
| --- | --- | --- |
| GET | `/admin` | `usuario.visualizar` |
| GET/POST | `/admin/redes` e `/admin/redes/{id}` | `rede.visualizar`, `rede.criar`, `rede.editar` |
| GET/POST | `/admin/regioes` e `/admin/regioes/{id}` | `regiao.visualizar`, `regiao.criar`, `regiao.editar` |
| GET/POST | `/admin/unidades` e `/admin/unidades/{id}` | `unidade.visualizar`, `unidade.criar`, `unidade.editar` |
| GET/POST | `/admin/usuarios` e `/admin/usuarios/{id}` | `usuario.visualizar`, `usuario.criar`, `usuario.editar` |
| GET/POST | `/admin/perfis` e `/admin/perfis/{id}` | `perfil.visualizar`, `perfil.criar`, `perfil.editar` |
| GET | `/admin/permissoes` | `permissao.visualizar` |
| GET/POST | `/admin/vinculos` | `vinculo.visualizar`, `vinculo.criar`, `vinculo.editar` |
| GET | `/admin/auditoria` | `auditoria.visualizar` |

Todas as rotas administrativas usam Auth + Scope + Permission + CSRF.

### API

| Método | Caminho | Ação |
| --- | --- | --- |
| GET | `/api/v1/health` | `HealthController@index` |

Respostas da API seguem o envelope:

```json
{ "success": true, "message": "...", "data": {} }
{ "success": false, "message": "...", "errors": {} }
```

## 5. Configuração

Configurações lidas de `.env` pelos arquivos em `config/`:

- `app.php`: nome, ambiente, depuração, URL, fuso, chave, views e logs.
- `database.php`: conexão MariaDB.
- `session.php`: nome, duração, cookies seguros e `SameSite`.
- `security.php`: CSRF e limites de tentativas de login.
- `mail.php`: parâmetros de e-mail (reservado).
- `theme.php`: identidade visual opcional validada por `ThemeCompiler`.

## 6. Como executar

```bash
composer install
composer migrate
composer seed
composer serve
```

Acesse `http://localhost:8000`.

### Credenciais de demonstração

- Superadministrador: `admin@local.test` / `Admin@123`.
- Demais perfis: consulte `Manual/03-banco-de-dados.md`.

O seed de demonstração redefine as senhas dos usuários de teste. Não utilize em
produção.

## 7. Fases 0–6 do documento 02

### Banco

- 13 novas tabelas: redes, regiões, unidades, usuários, perfis, permissões,
  vínculos, permissões explícitas e auditoria.
- 36 permissões e 8 perfis de sistema.
- Seeds idempotentes em `database/seeds/data/`.

### Autorização

- RBAC híbrido: perfil + permissões explícitas + escopo.
- `usuario_perfil` com escopo de rede/unidade e região opcional.
- `usuario_permissao` para concessões e negações explícitas.
- `AuthorizationService` calcula permissões por contexto.
- `TenantContextService` resolve contextos válidos.
- `PermissionMiddleware` e `ScopeMiddleware`.
- Deny by default e bloqueio de escalada de privilégio.
- Auditoria de login, logout e troca de contexto.

### Interfaces

- Login real em `usuarios`.
- Tela de seleção/troca de contexto.
- Sidebar e header com contexto e permissões.
- Configurações protegidas por permissão.

### Administração

- CRUD de redes, regiões, unidades, usuários e perfis.
- Perfis personalizados com escopo de rede; perfis de sistema/globais somente
  leitura para não Superadmin.
- Matriz de concessão estrita: nível inferior ao ator, exceto Superadmin.
- Vínculos com escopo de rede/unidade/região e validação anti-escalada.
- Contexto regional com escopo em unidades, usuários, vínculos, regiões e
  auditoria.
- Catálogo de permissões somente leitura.
- Auditoria de ações administrativas por contexto.
- Permissões recalculadas a cada requisição.

## 8. Critérios de aceite da fundação

| # | Critério | Situação |
| --- | --- | --- |
| 1 | Projeto inicia localmente sem erros | Atendido |
| 2 | Autoload PSR-4 do namespace `App\` | Atendido |
| 3 | `.env` carregado corretamente | Atendido |
| 4 | Conexão MariaDB via PDO | Atendido |
| 5 | Rota web responde | Atendido |
| 6 | Rota da API retorna JSON padronizado | Atendido |
| 7 | Exceções sem detalhes em produção | Atendido (`APP_DEBUG`) |
| 8 | Somente `public/` exposto | Atendido |
| 9 | Tema funciona com CSS Custom Properties | Atendido |
| 10 | Troca de cores sem alterar componentes | Atendido |
| 11 | Funciona em desktop e mobile | Atendido (CSS responsivo) |
| 12 | Arquivos sensíveis fora do Git | Atendido |
| 13 | Documentação atualizada | Atendido |

## 9. Testes

Suíte atual: **49 testes, 318 asserções, todos aprovados**.

Cobrem fundação, conexão, persistência, autenticação real, permissões por
perfil, isolamento por unidade, permissões regionais, não escalada, contextos,
CRUD administrativo, recálculo de permissões e auditoria.

## 10. Pendências

1. Política formal de retenção/mascaramento da auditoria (LGPD).
2. Interface para permissões explícitas em `usuario_permissao`.
3. Módulos funcionais (alunos, financeiro, treinos e comunidade).
4. Testes externos de carga, segurança e acessibilidade.
