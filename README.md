# Plataforma Fitness

Sistema de gestão de redes de academias, preparado para operar como SaaS
multiempresa e multiunidade.

**Stack:** PHP 8.2+ · MariaDB · HTML5 · CSS3 · JavaScript (ES modules)

## Requisitos

- PHP 8.2 ou superior com `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `curl`,
  `fileinfo`, `json`, `session`, `filter` e `ctype`.
- Composer 2.
- MariaDB/MySQL.
- DocumentRoot do servidor apontando para `public/`.

## Instalação

```bash
composer install
cp .env.example .env
```

Edite o `.env`:

```dotenv
APP_KEY=<segredo forte gerado>
DB_DATABASE=academia
DB_USERNAME=<usuario>
DB_PASSWORD=<senha>
```

Gere a chave com:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Crie o banco:

```sql
CREATE DATABASE academia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Aplique migrations e seeds:

```bash
composer migrate
composer seed
```

## Execução local

```bash
composer serve
# ou: php -S localhost:8000 -t public
```

Acesse `http://localhost:8000`.

- Página pública: `/`
- Login: `/login`
- Painel: `/dashboard`
- Contexto: `/contexto`
- Configurações: `/configuracoes`
- Administração: `/admin`, `/admin/redes`, `/admin/regioes`,
  `/admin/unidades`, `/admin/usuarios`, `/admin/perfis`,
  `/admin/permissoes`, `/admin/vinculos` e `/admin/auditoria`
- API: `GET /api/v1/health`

## Acesso de demonstração

- **Superadministrador:** `admin@local.test` / `Admin@123`
- Demais perfis (regional, local, diretor, gerente, contabilidade, recepção e
  funcionário): consulte `Manual/03-banco-de-dados.md`.

O seed de demonstração redefine as senhas dos usuários de teste. Não utilize
essas credenciais em produção.

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
- Deny by default, bloqueio de escalada e auditoria de ações administrativas.
- CRUD administrativo de redes, regiões, unidades, usuários, perfis e vínculos.

## Testes e formatação

```bash
composer test
composer format
```

Os testes de integração usam o banco configurado no `.env`.
Situação atual: 49 testes e 318 asserções aprovados.

## Estrutura

```text
app/             Controllers, Models, Repositories, Services, Middleware,
                 Validators, DTOs, Exceptions e Support
bootstrap/       Inicialização da aplicação
config/          Configuração por ambiente (sem segredos)
database/        Migrations, seeds e schema
public/          Única pasta exposta (Front Controller e assets)
resources/views/ Templates PHP
routes/          Rotas web e API
storage/         Logs, cache, sessões, temporários e arquivos privados
tests/           PHPUnit (Unit e Integration)
Manual/          Documentação funcional e técnica
```

## Segurança

- `.env` fora do controle de versão.
- Senhas com `password_hash()` e `password_verify()`.
- Sessões com cookies `HttpOnly`, `SameSite` e `Secure` em HTTPS.
- Proteção CSRF em requisições que alteram estado.
- Escape de saída HTML com `e()` / `htmlspecialchars()`.
- PDO com prepared statements e sem emulação.
- Erros sem stack trace quando `APP_DEBUG=false`.

## Documentação

Consulte [`Manual/00-visao-geral.md`](Manual/00-visao-geral.md),
[`Manual/03-banco-de-dados.md`](Manual/03-banco-de-dados.md) e os registros em
[`Manual/decisoes-tecnicas/`](Manual/decisoes-tecnicas/).
