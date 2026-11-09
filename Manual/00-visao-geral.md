# Visão geral

**Projeto:** Plataforma Fitness — sistema de gestão de redes de academias
**Versão:** 1.9 (fundação + Fases 0–6 + correções 1–3 + Ondas 1–4 + menus + Redes/planos)
**Stack:** PHP 8.2+ · MariaDB · HTML5 · CSS3 · JavaScript (ES modules)

## Objetivo

Estabelecer a fundação técnica de uma plataforma SaaS para redes de academias,
com arquitetura MVC, separação de responsabilidades, segurança por padrão e
identidade visual configurável.

Sobre a fundação foram implementadas as Fases 0–6 do documento 02: níveis,
perfis, permissões, redes, unidades, regiões, vínculos, contexto, auditoria e
administração. A Onda 1 do documento 03 adicionou pessoas, alunos, responsáveis,
contatos, endereços, documentos, consentimentos e o vínculo dos usuários com
pessoas. A Onda 2 adicionou catálogo comercial, modalidades, planos, preços por
unidade, contratos e matrículas. A Onda 3 adicionou turmas, horários, reservas,
frequência, dispositivos e eventos de acesso. A Onda 4 adicionou cobranças,
pagamentos, estornos, contas a pagar, contas bancárias, movimentos de caixa e
conciliação bancária. Os demais módulos funcionais (contabilidade, RH, treinos
e comunicação) ainda não foram implementados.

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
- Financeiro com cobranças, pagamentos, contas a pagar e conciliação bancária.
- Menu agrupado em submenus, recolhível no desktop e fechado por padrão no mobile.
- Formulário de Redes em linhas/colunas, com máscaras, CEP automático e planos de rede.
- Formulário de Unidades com rede/região dependentes, código automático e endereço completo.
- Camada global de carregamento com spinner, bloqueio de tela e anti-duplo clique.
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
- Suíte com 73 testes e 552 asserções aprovados.
- Fluxo web validado: login, contexto, permissões, administração e auditoria.
- CRUD administrativo e anti-escalada validados em testes e no fluxo web.
- Prioridades 2–3 validadas: perfis com escopo de rede, matriz de concessão,
  contexto regional, filtragem de vínculos e limpeza dos dados de teste.
- Onda 1 validada: pessoas, alunos, responsáveis, documentos, consentimentos e
  vínculo `usuarios.pessoa_id`.
- Onda 2 validada: modalidades, planos, preços por unidade, contratos e
  matrículas.
- Onda 3 validada: turmas, horários, reservas, frequência, dispositivos e
  eventos de acesso.
- Onda 4 validada: cobranças geradas de matrículas, pagamentos com estorno,
  contas a pagar, movimentos de caixa e conciliação bancária por CSV.
- Menu agrupado validado com submenus, recolhimento no desktop e gaveta de
  `90vw` no mobile; módulo de modalidades criado e validado.

## Pendências conhecidas

- Onda 1: upload/tela de documentos, tela de consentimentos, tela de restrições
  de saúde e dados de demonstração de alunos.
- Onda 2: telas de serviços, promoções, reajustes, descontos e transferências.
- Onda 3: vínculo turma/aluno, professor no horário, tela de ocorrências,
  registro manual de eventos e integração real com catracas.
- Onda 4: geração recorrente automática de mensalidades, integração real de
  PIX/boleto, tela de fornecedores e renegociação de dívidas.
- Aplicar a política de retenção LGPD de 2 anos.
- Interface para permissões explícitas em `usuario_permissao`.
- Ondas 5–8: contabilidade, RH, treinos, comunicação e relatórios.
- Testes externos de carga, segurança e acessibilidade.

## Documentos relacionados

- `01-estrutura-inicial.md` — registro da implementação da fundação, Fases 0–6 e Ondas 1–4
- `02-padroes-de-desenvolvimento.md` — convenções de código
- `03-banco-de-dados.md` — schema, seeds, perfis, permissões, administração e Ondas 1–4
- `04-onda-1-pessoas-alunos.md` — registro da Onda 1
- `05-onda-2-catalogo-matriculas.md` — registro da Onda 2
- `06-onda-3-operacao-acesso.md` — registro da Onda 3
- `07-onda-4-financeiro.md` — registro da Onda 4
- `08-menus-submenus.md` — menu agrupado, barra recolhível e módulo de modalidades
- `09-redes-layout-planos-rede.md` — formulário de redes, máscaras/CEP e planos de rede
- `10-unidade-layout.md` — formulário de unidades, vínculo com região e código automático
- `pendencias-ondas-1-2.md` — pendências registradas das Ondas 1 e 2
- `pendencias-onda-3.md` — pendências registradas da Onda 3
- `pendencias-onda-4.md` — pendências registradas da Onda 4
- `decisoes-tecnicas/` — registros de decisões arquiteturais
