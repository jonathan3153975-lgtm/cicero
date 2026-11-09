# Estrutura inicial — registro da implementação

**Documento de referência:** `projeto/1 - Estrutura inicial.md`
**Status:** fundação implementada; Fases 0–6 do documento 02, Ondas 1–4 do documento 03 e menus do documento 04 implementados
**Data:** 2026-11-09

## 1. Resumo

A fundação foi implementada sobre a raiz do repositório. O documento de
especificação permanece em `projeto/`, e a aplicação ocupa os diretórios
descritos na seção 3 da especificação.

Sobre essa base foram implementadas as Fases 0–6 do documento 02: níveis,
perfis, permissões, redes, unidades, regiões, vínculos, contexto, auditoria e
administração.

A Onda 1 do documento 03 adicionou pessoas, alunos, responsáveis, contatos,
endereços, documentos, consentimentos e o vínculo dos usuários com pessoas.

A Onda 2 adicionou catálogo comercial, modalidades, planos, preços por unidade,
contratos e matrículas.

A Onda 3 adicionou turmas, horários, reservas, frequência, dispositivos e
eventos de acesso.

A Onda 4 adicionou cobranças, pagamentos, estornos, contas a pagar, contas
bancárias, movimentos de caixa e conciliação bancária.

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
- 72 permissões e 8 perfis de sistema.
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

### Onda 1 — Pessoas e alunos

- Cadastro unificado de pessoas PF/PJ.
- Alunos, responsáveis, contatos, endereços, documentos e consentimentos.
- Vínculo `usuarios.pessoa_id` com backfill dos usuários existentes.
- Telas administrativas de pessoas, alunos e responsáveis.
- Escopo de pessoas/alunos por rede, unidade e região.
- Permissões `pessoa.*`, `aluno.*`, `responsavel.*`, `lgpd.consentimento.*` e
  `saude.*`.

### Onda 2 — Catálogo e matrículas

- Modalidades, planos, modalidades por plano e preços por unidade.
- Contratos, matrículas, serviços, reajustes, descontos e transferências.
- Telas de planos, preços, matrículas e contratos.
- Uma matrícula ativa por aluno/unidade.
- Contrato criado em transação com a matrícula.
- Permissões `matricula.*` e `contrato.*`; catálogo com
  72 permissões.

### Onda 3 — Operação e acesso

- Turmas, horários, reservas e frequência.
- Dispositivos e eventos de acesso.
- Bloqueio de horários sobrepostos, frequência duplicada e reserva fora do dia.
- Capacidade de turma e isolamento por unidade.
- Permissões `turma.*`, `frequencia.*`, `reserva.*`, `dispositivo.*`,
  `evento_acesso.*` e `ocorrencia.*`.

### Onda 4 — Financeiro

- Cobranças por matrícula e avulsas, com itens e saldo.
- Pagamentos com alocação, movimento de caixa e estorno.
- Contas a pagar, contas bancárias, centros de custo e formas de pagamento.
- Conciliação bancária por importação de CSV.
- Reaproveitamento das permissões `financeiro.visualizar` e `financeiro.editar`.

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

Suíte atual: **73 testes, 552 asserções, todos aprovados**.

Cobrem fundação, conexão, persistência, autenticação real, permissões por
perfil, isolamento por unidade, permissões regionais, não escalada, contextos,
CRUD administrativo, recálculo de permissões e auditoria.

## 10. Pendências

1. Onda 1: upload/tela de documentos, tela de consentimentos, tela de
   restrições de saúde e dados de demonstração de alunos.
2. Onda 2: telas de modalidades, serviços, promoções, reajustes, descontos e
   transferências.
3. Onda 3: tela de vínculo turma/aluno, professor no horário, tela de
   ocorrências, registro manual de eventos e integração real com catracas.
4. Onda 4: geração recorrente em lote, PIX/boleto reais, tela de fornecedores,
   renegociação e relatórios financeiros.
5. Aplicar a política de retenção LGPD de 2 anos.
6. Interface para permissões explícitas em `usuario_permissao`.
7. Ondas 5–8: contabilidade, RH, treinos, comunicação e relatórios.
7. Testes externos de carga, segurança e acessibilidade.
