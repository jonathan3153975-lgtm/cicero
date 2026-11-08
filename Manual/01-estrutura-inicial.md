# Estrutura inicial — registro da implementação

**Documento de referência:** `projeto/1 - Estrutura inicial.md`
**Status:** implementado e validado
**Data:** 2026-11-08

## 1. Resumo

A fundação foi implementada sobre a raiz do repositório. O documento de
especificação permanece em `projeto/`, e a aplicação ocupa os diretórios
descritos na seção 3 da especificação.

## 2. Andamento por etapa

### Etapa 1 — Preparar o ambiente

- [x] Repositório Git (já existente)
- [x] Estrutura de pastas
- [x] PHP 8.4 e Composer 2.8 verificados
- [x] `composer.json`
- [x] Dependências instaladas (`composer.lock` versionado)
- [x] `.gitignore`
- [x] `.env.example` e `.env`
- [x] Banco MariaDB local criado e validado (`academia`, `utf8mb4`)

### Etapa 2 — Inicialização MVC

- [x] `public/index.php`
- [x] `bootstrap/app.php`
- [x] Dotenv
- [x] Roteador (FastRoute)
- [x] Carregamento de `routes/web.php` e `routes/api.php`
- [x] Tratamento centralizado de exceções
- [x] Logger (Monolog)
- [x] Rota de saúde da aplicação: `GET /api/v1/health`

### Etapa 3 — Camada de dados

- [x] Conexão PDO (`App\Support\Database`)
- [x] Transações (`Database::transaction`)
- [x] Padrão de Repository (`App\Repositories\Repository`)
- [x] Padrão de migrations (`MigrationRunner` + `bin/migrate.php`)
- [x] Primeira migration de teste (`health_checks`)
- [x] Validar conexão e persistência — confirmado por `DatabaseTest` e
      `HealthRepositoryTest`; tabelas `schema_migrations` e `health_checks`
      criadas e persistência verificada.

### Etapa 4 — Design System

- [x] Reset e estilos base
- [x] Tokens CSS
- [x] Tema escuro
- [x] Tema claro
- [x] Tipografia e espaçamentos
- [x] Botões e formulários
- [x] Cards e tabelas
- [x] Sidebar e header
- [x] Layout responsivo
- [x] Tela inicial de demonstração

### Etapa 5 — Segurança e qualidade

- [x] Sessões seguras
- [x] Middleware de autenticação
- [x] Middleware de autorização (papel `admin`)
- [x] Proteção CSRF
- [x] Escape de saída
- [x] PHPUnit configurado
- [x] Padrão de formatação (PHP CS Fixer)
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
| GET | `/dashboard` | `DashboardController@index` | Auth + Role(admin) |
| GET | `/configuracoes` | `SettingsController@index` | Auth + Role(admin) |

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
- `security.php`: CSRF, tentativas de login e credenciais de demonstração.
- `mail.php`: parâmetros de e-mail (reservado).
- `theme.php`: identidade visual opcional validada por `ThemeCompiler`.

## 6. Como executar

```bash
composer install
composer serve
```

Depois de configurar o banco:

```bash
composer migrate
composer migrate:rollback
composer test
```

Credenciais locais de demonstração (definidas no `.env`): `admin@local.test` /
`admin123`. Este fluxo é provisório.

## 7. Critérios de aceite

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

## 8. Banco e validação

- Banco `academia` criado no MariaDB 12 com `utf8mb4`.
- Migrations aplicadas: `schema_migrations` e `health_checks`.
- Persistência validada pelo teste de integração `HealthRepositoryTest`.
- API `GET /api/v1/health` retorna `database: ok`.
- Suíte completa: 15 testes, 30 asserções, todos aprovados.

## 9. Pendências conhecidas

1. Substituir a autenticação de demonstração pelo módulo real.
2. Modelar redes, unidades, usuários, vínculos e permissões no próximo documento.
3. Implementar a persistência do tema por rede (tabelas `redes` / `rede_temas`).
