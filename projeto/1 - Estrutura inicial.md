# Fundação do Projeto --- Sistema de Gestão de Academias

**Documento:** `01-estrutura-inicial.md`\
**Versão:** 1.0\
**Status:** Especificação inicial\
**Stack:** PHP 8.2+ · MariaDB · JavaScript · HTML5 · CSS3

------------------------------------------------------------------------

## 1. Objetivo

Estabelecer a estrutura técnica inicial de uma plataforma de
gerenciamento de redes de academias, preparada para operar como SaaS
multiempresa e multiunidade.

A aplicação deverá possuir:

-   Arquitetura MVC, com separação clara de responsabilidades.
-   Programação orientada a objetos e princípios Clean Code.
-   Identidade visual própria, responsiva e configurável por empresa.
-   Backend PHP com Composer e autoload PSR-4.
-   MariaDB acessado por PDO.
-   JavaScript modular, sem dependência obrigatória de framework
    frontend.
-   Configurações sensíveis fora do repositório, por meio de `.env`.
-   Rotas web e API organizadas.
-   Segurança, logs, tratamento centralizado de erros e documentação.
-   Estrutura preparada para integrações futuras com pagamentos,
    catracas, notificações e aplicativo.

Este documento define a fundação. Não implementa ainda os módulos de
alunos, financeiro, treinos ou comunidade.

## 2. Diretrizes e decisões técnicas

  Tema                Decisão inicial
  ------------------- -------------------------------------------------
  Linguagem backend   PHP 8.2 ou superior
  Banco de dados      MariaDB, versão estável suportada pelo servidor
  Acesso ao banco     PDO, prepared statements
  Arquitetura         MVC + Services + Repositories
  Autoload            Composer / PSR-4
  Frontend            HTML5, CSS3 próprio e JavaScript modular
  Layout              Responsivo, mobile-first
  Estilo              Design system próprio com CSS Custom Properties
  API                 REST, com versionamento `/api/v1`
  Sessão              Sessões PHP com cookies seguros
  Configuração        `.env` fora do controle de versão
  Versionamento       Git
  Documentação        Markdown na pasta `Manual/`
  Deploy              Ambiente Linux com Apache ou Nginx

### 2.1 Princípios de arquitetura

1.  **Responsabilidade única:** cada classe deve possuir uma
    responsabilidade clara.
2.  **Baixo acoplamento:** módulos não devem depender diretamente de
    detalhes internos de outros módulos.
3.  **Alta coesão:** funcionalidades relacionadas devem permanecer
    agrupadas.
4.  **Injeção de dependência:** dependências devem ser recebidas pelos
    construtores, evitando instanciação espalhada.
5.  **Regra de negócio fora do Controller:** Controllers recebem a
    requisição, acionam serviços e retornam respostas.
6.  **Acesso a dados isolado:** consultas SQL ficam em Repositories ou
    componentes específicos de persistência.
7.  **Configuração centralizada:** parâmetros de ambiente e
    configurações da aplicação não devem ficar espalhados.
8.  **Segurança por padrão:** toda entrada deve ser validada e toda
    operação protegida por autorização adequada.

Não criar abstrações sem necessidade. A arquitetura deve ser organizada,
mas continuar simples de compreender e manter.

## 3. Estrutura de diretórios

Estrutura inicial sugerida:

``` text
academia/
│
├── app/
│   ├── Controllers/
│   │   ├── Web/
│   │   └── Api/
│   │
│   ├── Models/
│   │
│   ├── Repositories/
│   │
│   ├── Services/
│   │
│   ├── Middleware/
│   │
│   ├── Validators/
│   │
│   ├── DTOs/
│   │
│   ├── Exceptions/
│   │
│   └── Support/
│
├── bootstrap/
│   └── app.php
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── session.php
│   ├── security.php
│   └── mail.php
│
├── database/
│   ├── migrations/
│   ├── seeds/
│   └── schema/
│
├── docs/
│
├── Manual/
│   ├── 00-visao-geral.md
│   ├── 01-estrutura-inicial.md
│   ├── 02-padroes-de-desenvolvimento.md
│   └── decisoes-tecnicas/
│
├── public/
│   ├── index.php
│   ├── .htaccess
│   ├── assets/
│   │   ├── css/
│   │   │   ├── base/
│   │   │   ├── components/
│   │   │   ├── layouts/
│   │   │   ├── themes/
│   │   │   └── app.css
│   │   │
│   │   ├── js/
│   │   │   ├── core/
│   │   │   ├── components/
│   │   │   ├── modules/
│   │   │   └── app.js
│   │   │
│   │   ├── images/
│   │   ├── icons/
│   │   └── fonts/
│   │
│   └── uploads/
│       └── .gitkeep
│
├── resources/
│   └── views/
│       ├── layouts/
│       ├── components/
│       ├── errors/
│       ├── auth/
│       └── dashboard/
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── storage/
│   ├── cache/
│   ├── logs/
│   ├── sessions/
│   ├── temp/
│   └── private/
│
├── tests/
│   ├── Unit/
│   └── Integration/
│
├── vendor/
│
├── .env
├── .env.example
├── .gitignore
├── composer.json
├── composer.lock
├── README.md
└── phpunit.xml
```

### 3.1 Responsabilidade das pastas

  -----------------------------------------------------------------------
  Pasta                               Responsabilidade
  ----------------------------------- -----------------------------------
  `app/Controllers`                   Receber requisições, coordenar
                                      serviços e devolver respostas

  `app/Models`                        Representar entidades e conceitos
                                      do domínio, sem concentrar toda a
                                      lógica

  `app/Repositories`                  Consultas e persistência de dados

  `app/Services`                      Regras de negócio e orquestração

  `app/Middleware`                    Autenticação, autorização, CSRF e
                                      verificações transversais

  `app/Validators`                    Validação de entradas

  `app/DTOs`                          Transporte explícito de dados entre
                                      camadas

  `app/Exceptions`                    Exceções específicas da aplicação

  `app/Support`                       Recursos auxiliares reutilizáveis

  `bootstrap`                         Inicialização da aplicação

  `config`                            Configurações não secretas e
                                      leitura das variáveis de ambiente

  `database/migrations`               Evolução versionada do banco

  `public`                            Única pasta exposta diretamente à
                                      internet

  `resources/views`                   Templates HTML/PHP

  `routes`                            Definição das rotas

  `storage`                           Logs, cache, arquivos privados e
                                      temporários

  `tests`                             Testes automatizados

  `Manual`                            Documentação funcional e técnica do
                                      projeto
  -----------------------------------------------------------------------

**Regra de segurança:** `app`, `config`, `database`, `storage`,
`vendor`, `tests` e `Manual` não devem ser acessíveis diretamente pela
internet. O DocumentRoot do servidor deve apontar exclusivamente para
`public/`.

## 4. Dependências PHP

Gerenciar dependências pelo Composer.

### 4.1 Dependências iniciais recomendadas

Instalar na raiz do projeto:

``` bash
composer init
composer require vlucas/phpdotenv
composer require monolog/monolog
composer require nikic/fast-route
```

Finalidades:

  Pacote               Finalidade
  -------------------- --------------------------------------
  `vlucas/phpdotenv`   Carregar variáveis do arquivo `.env`
  `monolog/monolog`    Registro estruturado de logs
  `nikic/fast-route`   Roteamento HTTP

### 4.2 Dependências de desenvolvimento

``` bash
composer require --dev phpunit/phpunit
composer require --dev friendsofphp/php-cs-fixer
```

  Pacote                        Finalidade
  ----------------------------- ----------------------------
  `phpunit/phpunit`             Testes automatizados
  `friendsofphp/php-cs-fixer`   Padronização de código PHP

Antes de instalar, verificar a compatibilidade das versões com a versão
do PHP utilizada no ambiente.

### 4.3 Extensões PHP

Verificar se estão habilitadas:

-   `pdo`
-   `pdo_mysql`
-   `mbstring`
-   `openssl`
-   `curl`
-   `fileinfo`
-   `json`
-   `session`
-   `filter`
-   `ctype`

Extensões adicionais poderão ser instaladas conforme os módulos futuros
exigirem.

### 4.4 Frontend

Não instalar um framework frontend neste primeiro momento.

Utilizar:

-   HTML5 semântico.
-   CSS3 com variáveis e componentes próprios.
-   JavaScript moderno (ES6+).
-   Fetch API para requisições assíncronas.
-   SVG para ícones e elementos vetoriais.
-   Fontes locais ou serviço de fontes confiável.

Bibliotecas como gráficos, máscaras e componentes adicionais devem ser
introduzidas somente quando houver necessidade concreta. Evitar
dependências redundantes.

## 5. Configuração do Composer

Exemplo de `composer.json`:

``` json
{
  "name": "academia/plataforma",
  "description": "Sistema de gestão de redes de academias",
  "type": "project",
  "require": {
    "php": "^8.2",
    "vlucas/phpdotenv": "^5.6",
    "monolog/monolog": "^3.0",
    "nikic/fast-route": "^1.3"
  },
  "require-dev": {
    "phpunit/phpunit": "^11.0",
    "friendsofphp/php-cs-fixer": "^3.0"
  },
  "autoload": {
    "psr-4": {
      "App\\": "app/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "Tests\\": "tests/"
    }
  },
  "scripts": {
    "test": "phpunit",
    "format": "php-cs-fixer fix"
  },
  "config": {
    "sort-packages": true,
    "optimize-autoloader": true
  }
}
```

As versões acima são referências de compatibilidade. O Composer deve
resolver e registrar as versões efetivamente instaladas no
`composer.lock`.

Após alterar o autoload:

``` bash
composer dump-autoload
```

Em produção:

``` bash
composer install --no-dev --optimize-autoloader
```

## 6. Arquivo `.env`

O `.env` deve armazenar informações específicas do ambiente,
especialmente credenciais e segredos.

Exemplo de `.env.example`:

``` dotenv
APP_NAME="Plataforma Fitness"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Sao_Paulo
APP_KEY=

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=academia
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4

SESSION_NAME=academia_session
SESSION_LIFETIME=120
SESSION_SECURE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=Lax

LOG_CHANNEL=daily
LOG_LEVEL=debug

MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="Plataforma Fitness"
```

### 6.1 Regras para o `.env`

-   O arquivo `.env` real nunca deve ser versionado.
-   O `.env.example` deve ser versionado, sem credenciais reais.
-   Em produção, `APP_DEBUG=false`.
-   `APP_KEY` deve receber um segredo aleatório forte.
-   Credenciais do banco não devem aparecer no código.
-   Não utilizar senhas reais em exemplos ou documentação.
-   Não compartilhar o `.env` em chamados, prints ou repositórios.

Gerar uma chave aleatória em ambiente de desenvolvimento:

``` bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

## 7. Arquivo `.gitignore`

Criar o arquivo `.gitignore` na raiz:

``` gitignore
# Ambiente
.env
.env.*
!.env.example

# Dependências
/vendor/

# Logs
/storage/logs/*
!/storage/logs/.gitkeep

# Cache e temporários
/storage/cache/*
!/storage/cache/.gitkeep
/storage/temp/*
!/storage/temp/.gitkeep

# Sessões
/storage/sessions/*

# Arquivos privados
/storage/private/*

# Uploads de usuários
/public/uploads/*
!/public/uploads/.gitkeep

# Arquivos de sistema
.DS_Store
Thumbs.db
desktop.ini

# IDEs
.idea/
.vscode/
*.sublime-workspace
*.sublime-project

# Sistema operacional
*.log
*.tmp
*.swp
*.swo

# Testes e cobertura
.phpunit.cache/
coverage/
.phpunit.result.cache

# Dependências e ferramentas locais
.php-cs-fixer.cache
```

Observação: `composer.lock` deve ser versionado em uma aplicação. Ele
garante que os ambientes instalem as mesmas versões de dependências.

## 8. Inicialização da aplicação

### 8.1 `public/index.php`

Será o Front Controller: todas as requisições web devem passar por ele.

Responsabilidades:

1.  Carregar o autoload do Composer.
2.  Carregar variáveis de ambiente.
3.  Inicializar configurações.
4.  Inicializar sessão, quando necessária.
5.  Carregar rotas.
6.  Executar middleware aplicáveis.
7.  Despachar a requisição.
8.  Tratar exceções de forma centralizada.

Não colocar regras de negócio nesse arquivo.

### 8.2 `bootstrap/app.php`

Responsável por montar os componentes essenciais:

-   Configurações.
-   Conexão com o banco.
-   Logger.
-   Roteador.
-   Serviços compartilhados.
-   Tratamento de erros.

A inicialização deve ser previsível e não executar consultas ou
operações de negócio desnecessárias.

### 8.3 Rotas

Separar rotas de páginas e rotas de API.

Exemplo conceitual de `routes/web.php`:

``` php
<?php

use App\Controllers\Web\HomeController;
use App\Controllers\Web\AuthController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/login', [AuthController::class, 'showLogin']],
    ['POST', '/login', [AuthController::class, 'login']],
    ['POST', '/logout', [AuthController::class, 'logout']],
];
```

Exemplo conceitual de `routes/api.php`:

``` php
<?php

use App\Controllers\Api\HealthController;

return [
    ['GET', '/api/v1/health', [HealthController::class, 'index']],
];
```

Os exemplos representam a organização das rotas. O adaptador do roteador
deverá ser implementado para registrar e despachar esses formatos.

### 8.4 Reescrita de URLs no Apache

Criar `public/.htaccess`:

``` apache
Options -Indexes

RewriteEngine On

# Impede acesso a arquivos ocultos
RewriteRule (^|/)\. - [F,L]

# Arquivos e diretórios existentes são servidos diretamente
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Demais requisições passam pelo Front Controller
RewriteRule ^ index.php [QSA,L]

# Proteções adicionais
<FilesMatch "\.(env|ini|log|sql|bak|dist)$">
    Require all denied
</FilesMatch>
```

No Nginx, configurar o equivalente no bloco do servidor e apontar o
`root` para a pasta `public/`.

## 9. Conexão MariaDB

Criar um serviço de conexão usando PDO.

Exemplo conceitual:

``` php
<?php

namespace App\Support;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $database = $_ENV['DB_DATABASE'] ?? '';
        $username = $_ENV['DB_USERNAME'] ?? '';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        if ($database === '' || $username === '') {
            throw new RuntimeException(
                'Configuração do banco de dados incompleta.'
            );
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        try {
            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            // Registrar detalhes no logger, sem expor credenciais ao usuário.
            throw new RuntimeException(
                'Não foi possível conectar ao banco de dados.',
                0,
                $exception
            );
        }

        return self::$connection;
    }
}
```

Em uma implementação mais avançada, a conexão deverá ser fornecida por
injeção de dependência. Evitar o uso indiscriminado de estado estático
em serviços e repositórios.

Regras:

-   Utilizar InnoDB.
-   Utilizar `utf8mb4`.
-   Criar índices para chaves estrangeiras e consultas frequentes.
-   Definir chaves estrangeiras quando aplicável.
-   Utilizar transações em operações que alterem múltiplas entidades.
-   Não concatenar dados recebidos do usuário diretamente em SQL.
-   Não utilizar `SELECT *` indiscriminadamente.

## 10. Identidade visual e Design System

A referência visual fornecida utiliza fundo preto/grafite, laranja
intenso, amarelo-limão e branco.

Paleta inicial aproximada, inspirada na imagem:

  Token           Cor         Aplicação
  --------------- ----------- -----------------------------------------
  Primary         `#F55800`   Ações principais e identidade
  Accent          `#E0FF51`   Destaques, chamadas e estados especiais
  Background      `#191919`   Fundo escuro
  Surface         `#242424`   Cards e painéis escuros
  Surface Light   `#FFFFFF`   Cards e superfícies claras
  Text            `#FFFFFF`   Texto sobre fundos escuros
  Text Dark       `#191919`   Texto sobre fundos claros
  Border          `#3A3A3A`   Bordas e divisores
  Success         `#22A06B`   Sucesso
  Warning         `#E0A52B`   Atenção
  Danger          `#DC3545`   Erros e bloqueios

As cores são valores iniciais de referência, não uma identidade
definitiva. A empresa contratante poderá personalizar as cores
institucionais.

### 10.1 CSS Custom Properties

Criar `public/assets/css/themes/theme-default.css`:

``` css
:root {
    /* Identidade da empresa */
    --brand-primary: #F55800;
    --brand-primary-hover: #D94E00;
    --brand-accent: #E0FF51;

    /* Tema escuro */
    --color-background: #191919;
    --color-surface: #242424;
    --color-surface-elevated: #303030;

    /* Texto */
    --color-text: #FFFFFF;
    --color-text-muted: #B8B8B8;
    --color-text-inverse: #191919;

    /* Estrutura */
    --color-border: #3A3A3A;
    --color-success: #22A06B;
    --color-warning: #E0A52B;
    --color-danger: #DC3545;

    /* Formas e espaçamentos */
    --radius-sm: 6px;
    --radius-md: 10px;
    --radius-lg: 16px;

    --spacing-1: 4px;
    --spacing-2: 8px;
    --spacing-3: 12px;
    --spacing-4: 16px;
    --spacing-5: 24px;
    --spacing-6: 32px;

    /* Tipografia */
    --font-family-base: Arial, Helvetica, sans-serif;
    --font-size-base: 16px;

    /* Componentes */
    --button-height: 44px;
    --input-height: 44px;
    --container-max-width: 1440px;
}
```

### 10.2 Tema claro

Criar `public/assets/css/themes/theme-light.css` com os mesmos tokens
semânticos, mas valores adequados ao tema claro.

Não duplicar os componentes para cada tema. Os componentes devem
consumir variáveis, e não cores fixas.

### 10.3 Componentes visuais

Organizar os estilos em:

-   `base/`: reset, tipografia, variáveis e estilos globais.
-   `components/`: botões, cards, formulários, tabelas, badges, modais.
-   `layouts/`: sidebar, header, navegação, estrutura de páginas.
-   `themes/`: temas e identidade por empresa.

Exemplo de componente:

``` css
.btn-primary {
    min-height: var(--button-height);
    padding: 0 20px;
    border: 0;
    border-radius: var(--radius-md);
    background: var(--brand-primary);
    color: #fff;
    font-weight: 700;
    cursor: pointer;
    transition: background .2s ease, transform .2s ease;
}

.btn-primary:hover {
    background: var(--brand-primary-hover);
}
```

### 10.4 Personalização por empresa

A personalização não deverá exigir alteração de CSS ou novo deploy.

A arquitetura deverá prever uma configuração de identidade visual por
rede:

-   Cor primária.
-   Cor de destaque.
-   Cor de fundo.
-   Cor de superfície.
-   Cor de texto.
-   Logotipo.
-   Ícone/favicons.
-   Nome de exibição.

No futuro, esses dados poderão ser armazenados em tabelas como `redes` e
`rede_temas`.

O backend deverá validar os valores recebidos. Para cores, aceitar
apenas formatos permitidos, como hexadecimal. Os valores serão
convertidos em CSS Custom Properties em um ponto controlado da
aplicação.

Nunca permitir que o usuário informe CSS ou JavaScript arbitrário para
personalizar o tema.

## 11. Multiempresa e multiunidade

A plataforma será projetada para suportar diversas redes.

Conceitos:

-   **Plataforma:** aplicação SaaS.
-   **Rede:** empresa contratante.
-   **Unidade:** filial ou academia pertencente à rede.
-   **Usuário:** pessoa autenticada.
-   **Vínculo:** associação de usuário com uma ou mais redes/unidades e
    respectivos papéis.

Diretrizes:

1.  Toda operação deverá identificar o contexto da rede.
2.  Operações específicas de unidade deverão validar o vínculo com a
    unidade.
3.  O contexto da empresa não poderá ser aceito cegamente a partir de
    parâmetros enviados pelo navegador.
4.  O backend deverá resolver e validar o contexto a partir da sessão,
    autenticação e vínculos autorizados.
5.  Repositories deverão aplicar o escopo da rede em consultas de dados
    empresariais.
6.  Testes deverão verificar tentativas de acesso cruzado entre redes.
7.  Um superadministrador da plataforma terá regras próprias, separadas
    das permissões dos administradores das redes.

O isolamento entre empresas é um requisito crítico de segurança.

## 12. Segurança mínima da fundação

Implementar desde o primeiro estágio:

-   Hash de senha com `password_hash()` usando Argon2id quando
    disponível ou bcrypt.
-   Verificação com `password_verify()`.
-   Regeneração do ID da sessão após autenticação.
-   Cookies `HttpOnly`, `Secure` em HTTPS e `SameSite`.
-   Proteção CSRF em formulários e requisições que alterem estado.
-   Escape de saída HTML com `htmlspecialchars()`.
-   Validação no backend, independentemente da validação JavaScript.
-   Prepared statements.
-   Controle de tentativas de login.
-   Autorização em todas as rotas protegidas.
-   Logs sem senhas, tokens ou dados financeiros sensíveis.
-   Respostas de erro sem stack trace em produção.
-   Política de backup e restauração testada.
-   Limitação de tipo, tamanho e conteúdo de arquivos enviados.
-   Proteção contra acesso direto a arquivos privados.

Dados de saúde e avaliações físicas deverão receber proteção adicional e
acesso estritamente controlado, em conformidade com a LGPD.

## 13. Padrões de código

### 13.1 Nomenclatura

-   Classes: `PascalCase`.
-   Métodos e variáveis: `camelCase`.
-   Constantes: `UPPER_SNAKE_CASE`.
-   Tabelas e colunas: `snake_case`.
-   Arquivos de classe: mesmo nome da classe.
-   Controllers: sufixo `Controller`.
-   Services: sufixo `Service`.
-   Repositories: sufixo `Repository`.

### 13.2 Controllers

Controllers devem ser pequenos.

Responsabilidades:

1.  Receber parâmetros.
2.  Validar formato básico da requisição.
3.  Acionar o Service adequado.
4.  Retornar View, redirect ou JSON.

Não colocar SQL, cálculos financeiros ou regras de negócio complexas
dentro dos Controllers.

### 13.3 Services

Services concentram regras de negócio e coordenam operações.

Exemplos futuros:

-   `AlunoService`
-   `MatriculaService`
-   `PagamentoService`
-   `ControleAcessoService`
-   `TreinoService`
-   `ComunidadeService`

### 13.4 Repositories

Repositories concentram operações de persistência.

Exemplos futuros:

-   `AlunoRepository`
-   `PagamentoRepository`
-   `UnidadeRepository`

Evitar que Controllers ou Views executem SQL diretamente.

### 13.5 Views

Views devem:

-   Exibir dados recebidos pelo Controller.
-   Utilizar componentes reutilizáveis.
-   Não conter regras de negócio.
-   Não acessar diretamente o banco.
-   Escapar dados dinâmicos.
-   Manter consistência visual.

## 14. Padrão de respostas da API

Definir um padrão consistente para respostas JSON.

Sucesso:

``` json
{
  "success": true,
  "message": "Operação realizada com sucesso.",
  "data": {}
}
```

Erro:

``` json
{
  "success": false,
  "message": "Não foi possível realizar a operação.",
  "errors": {}
}
```

Utilizar códigos HTTP adequados:

-   `200`: sucesso.
-   `201`: recurso criado.
-   `204`: sucesso sem conteúdo.
-   `400`: requisição inválida.
-   `401`: não autenticado.
-   `403`: sem permissão.
-   `404`: recurso não encontrado.
-   `409`: conflito.
-   `422`: erro de validação.
-   `429`: limite de requisições excedido.
-   `500`: erro interno.

Não retornar detalhes internos de exceções ao cliente.

## 15. Ambientes de execução

Manter ambientes separados:

  Ambiente      Finalidade
  ------------- ---------------------
  Local         Desenvolvimento
  Homologação   Validação integrada
  Produção      Operação real

Cada ambiente deverá possuir seu próprio `.env`, banco, credenciais e
configurações.

Nunca utilizar dados reais de alunos em ambiente de testes sem base
legal, controles e anonimização adequados.

### 15.1 Execução local

Com PHP instalado, a aplicação poderá ser iniciada inicialmente por:

``` bash
php -S localhost:8000 -t public
```

Esse servidor embutido é destinado ao desenvolvimento, não à produção.

## 16. Documentação obrigatória

A pasta `Manual/` deverá acompanhar a evolução do projeto.

Cada etapa de desenvolvimento deverá atualizar a documentação
correspondente.

Documentos previstos:

  Documento                            Conteúdo
  ------------------------------------ -----------------------------------------
  `00-visao-geral.md`                  Objetivos, escopo e arquitetura
  `01-estrutura-inicial.md`            Estrutura, dependências e configurações
  `02-padroes-de-desenvolvimento.md`   Convenções, MVC e Clean Code
  `03-banco-de-dados.md`               Entidades, tabelas e relacionamentos
  `04-autenticacao-e-permissoes.md`    Login, sessões e autorização
  `05-multiempresa.md`                 Isolamento e contexto das redes
  `06-identidade-visual.md`            Design system e temas
  `07-api.md`                          Endpoints e padrões de resposta
  `08-deploy-e-infraestrutura.md`      Publicação, backup e monitoramento
  `decisoes-tecnicas/`                 Registro de decisões arquiteturais

Ao concluir cada módulo, documentar:

-   Objetivo.
-   Regras de negócio.
-   Estrutura de dados.
-   Arquivos criados e alterados.
-   Rotas.
-   Permissões.
-   Integrações.
-   Testes realizados.
-   Pendências conhecidas.

## 17. Sequência de implementação da fundação

Executar na seguinte ordem:

### Etapa 1 --- Preparar o ambiente

-   [ ] Criar o repositório Git.
-   [ ] Criar a estrutura de pastas.
-   [ ] Configurar PHP e Composer.
-   [ ] Criar `composer.json`.
-   [ ] Instalar dependências.
-   [ ] Criar `.gitignore`.
-   [ ] Criar `.env.example` e `.env`.
-   [ ] Criar banco MariaDB local.

### Etapa 2 --- Inicialização MVC

-   [ ] Criar `public/index.php`.
-   [ ] Criar `bootstrap/app.php`.
-   [ ] Configurar Dotenv.
-   [ ] Configurar roteador.
-   [ ] Criar carregamento das rotas.
-   [ ] Criar tratamento centralizado de exceções.
-   [ ] Criar logger.
-   [ ] Criar primeira rota de saúde da aplicação.

### Etapa 3 --- Camada de dados

-   [ ] Criar conexão PDO.
-   [ ] Configurar transações.
-   [ ] Definir padrão de Repository.
-   [ ] Definir padrão de migrations.
-   [ ] Criar primeira migration de teste.
-   [ ] Validar conexão e persistência.

### Etapa 4 --- Design System

-   [ ] Criar reset e estilos base.
-   [ ] Criar tokens CSS.
-   [ ] Criar tema escuro.
-   [ ] Criar tema claro.
-   [ ] Criar tipografia e espaçamentos.
-   [ ] Criar botões e formulários.
-   [ ] Criar cards e tabelas.
-   [ ] Criar sidebar e header.
-   [ ] Criar layout responsivo.
-   [ ] Criar tela inicial de demonstração.

### Etapa 5 --- Segurança e qualidade

-   [ ] Configurar sessões seguras.
-   [ ] Criar middleware de autenticação.
-   [ ] Criar middleware de autorização.
-   [ ] Implementar proteção CSRF.
-   [ ] Validar escape de saída.
-   [ ] Configurar testes PHPUnit.
-   [ ] Configurar padrão de formatação.
-   [ ] Validar isolamento de arquivos privados.
-   [ ] Atualizar documentação.

## 18. Critérios de aceite da fundação

A estrutura inicial será considerada concluída quando:

1.  O projeto iniciar localmente sem erros.
2.  O Composer carregar as classes pelo namespace `App\\`.
3.  O `.env` for carregado corretamente.
4.  A aplicação conectar ao MariaDB via PDO.
5.  Uma rota web responder corretamente.
6.  Uma rota da API retornar JSON padronizado.
7.  Exceções forem tratadas sem expor detalhes em produção.
8.  O diretório público for o único exposto pelo servidor.
9.  O tema visual funcionar com variáveis CSS.
10. A troca das cores principais não exigir alteração dos componentes.
11. A aplicação funcionar em desktop e dispositivos móveis.
12. Arquivos sensíveis estiverem fora do Git.
13. O projeto possuir documentação atualizada em `Manual/`.

## 19. Orientações para agentes de programação

Ao utilizar um agente de IA para implementar esta fundação, seguir estas
instruções:

1.  Ler este documento integralmente antes de alterar arquivos.
2.  Respeitar a arquitetura MVC e os princípios de Clean Code.
3.  Não criar módulos funcionais que não façam parte do estágio
    solicitado.
4.  Não introduzir frameworks ou dependências sem justificativa.
5.  Não colocar regras de negócio nos Controllers ou Views.
6.  Não expor arquivos internos da aplicação.
7.  Não versionar credenciais.
8.  Utilizar variáveis CSS para todas as cores de interface.
9.  Não fixar cores institucionais diretamente nos componentes.
10. Testar as funcionalidades criadas.
11. Atualizar os arquivos de documentação da pasta `Manual/`.
12. Informar arquivos criados, arquivos alterados, dependências
    instaladas e testes executados.
13. Não afirmar que uma etapa está concluída sem verificar seu
    funcionamento.

Qualquer alteração estrutural relevante deverá ser registrada em
`Manual/decisoes-tecnicas/`.

------------------------------------------------------------------------

## 20. Próximo documento

Após a conclusão desta fundação, o próximo documento deverá especificar:

**`02-modelagem-banco-de-dados.md`**

Conteúdo previsto:

-   Modelo multiempresa e multiunidade.
-   Entidades e relacionamentos.
-   Usuários, pessoas e perfis.
-   Permissões e vínculos.
-   Redes e unidades.
-   Configurações de identidade visual.
-   Padrões de auditoria.
-   Convenções de chaves e índices.
-   Scripts iniciais de criação do banco.
-   Estratégia de migrations.

A implementação dos módulos funcionais deverá começar somente depois da
validação da fundação e da modelagem inicial do banco.
