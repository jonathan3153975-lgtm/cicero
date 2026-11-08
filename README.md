# Plataforma Fitness

Fundação de um sistema de gestão de redes de academias, preparado para operar
como SaaS multiempresa e multiunidade.

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

Gere chaves e hashes com:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
php -r "echo password_hash('sua-senha-de-demo', PASSWORD_DEFAULT), PHP_EOL;"
```

Crie o banco e rode as migrations:

```sql
CREATE DATABASE academia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
composer migrate
```

## Execução local

```bash
composer serve
# ou: php -S localhost:8000 -t public
```

Acesse `http://localhost:8000`.

- Página pública: `/`
- Login de demonstração: `/login`
- Área protegida: `/dashboard` e `/configuracoes`
- API: `GET /api/v1/health`

As credenciais de demonstração são definidas por `DEMO_EMAIL` e
`DEMO_PASSWORD_HASH` no `.env`. Este fluxo é temporário e será substituído pelo
módulo real de autenticação e permissões.

## Testes e formatação

```bash
composer test
composer format
```

Os testes de integração são ignorados automaticamente quando o banco de dados
não está acessível.

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

Consulte [`Manual/00-visao-geral.md`](Manual/00-visao-geral.md) e os registros em
[`Manual/decisoes-tecnicas/`](Manual/decisoes-tecnicas/).
