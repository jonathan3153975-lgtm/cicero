# Pendências das Ondas 1 e 2

**Status:** registradas para verificação posterior
**Data:** 2026-11-09

## Onda 1 — Pessoas e alunos

| # | Pendência | Verificação futura |
| --- | --- | --- |
| 1.1 | Upload/tela de documentos de pessoas | Verificar upload, armazenamento privado e vínculo com `pessoa_documentos` |
| 1.2 | Tela de consentimentos LGPD | Verificar registro, revogação e histórico por pessoa |
| 1.3 | Tela de restrições de saúde | Verificar acesso restrito e autorização do cliente |
| 1.4 | Dados de demonstração de alunos | Verificar seed idempotente e escopo |
| 1.5 | Interface de `autorizacoes_acesso_saude` | Verificar criação de tabela/serviço e bloqueio de acesso indevido |

## Onda 2 — Catálogo, planos, preços, contratos e matrículas

| # | Pendência | Verificação futura |
| --- | --- | --- |
| 2.1 | Tela de modalidades — concluída em 2026-11-09 | CRUD e escopo global/rede validados |
| 2.2 | Tela de serviços adicionais | Verificar CRUD e vínculo com matrícula |
| 2.3 | Tela de promoções | Verificar vigência e aplicação |
| 2.4 | Tela de reajustes | Verificar histórico e impacto no contrato |
| 2.5 | Tela de descontos | Verificar vigência e vínculo com matrícula/contrato |
| 2.6 | Tela de transferências | Verificar fluxo entre unidades e atualização de vínculos |
| 2.7 | Renovação automática e cobranças | Implementar na Onda 4 (financeiro) |
| 2.8 | Histórico completo de alterações contratuais | Verificar trilha de auditoria específica |

## Observação

Essas pendências não bloquearam as Ondas 1 e 2. Elas devem ser verificadas
antes da entrada em produção dos respectivos módulos ou incorporadas às ondas
seguintes.
