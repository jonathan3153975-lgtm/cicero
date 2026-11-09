# Onda 2 — Catálogo, planos, preços, contratos e matrículas

**Documento de referência:** `projeto/3 - Banco de dados e modelagem do domínio.md`
**Status:** implementada e validada
**Data:** 2026-11-09

## 1. Objetivo

Implementar o catálogo comercial, planos e preços por unidade, contratos e
matrículas, integrados aos alunos da Onda 1.

## 2. Migrations aplicadas

| Migration | Conteúdo |
| --- | --- |
| `0023_create_catalogo_comercial_tables.php` | `modalidades`, `planos`, `plano_modalidades`, `plano_unidade_precos` |
| `0024_create_servicos_promocoes_tables.php` | `servicos_adicionais`, `promocoes` |
| `0025_create_contratos_matriculas_tables.php` | `contratos`, `matriculas`, `matricula_servicos`, `reajustes`, `descontos`, `transferencias` |

## 3. Modelo aplicado

- Modalidades globais ou por rede.
- Planos globais ou por rede, com modalidades associadas.
- Preços por plano/unidade com vigência, valores de matrícula, mensal, adesão e
  taxa administrativa.
- Contratos gerados automaticamente pela matrícula, com número, plano, unidade,
  valor mensal, vigência, renovação e dia de vencimento.
- Matrícula vinculada ao aluno, unidade do aluno, preço de plano e contrato.
- Tabelas preparadas para serviços adicionais, promoções, reajustes, descontos e
  transferências.

## 4. Regras de negócio

- O preço usado na matrícula deve estar ativo e pertencer à unidade e à rede do
  aluno.
- O plano deve estar ativo e compatível com a rede.
- Um aluno só pode ter uma matrícula ativa por unidade.
- A data de início deve estar dentro da vigência do preço.
- O contrato é criado na mesma transação da matrícula.
- Encerramento, cancelamento ou transferência atualizam o status do contrato.
- Planos globais são editáveis apenas pelo Superadmin; planos de rede são
  administrados pela própria rede.
- Escopo de unidade, região e rede é aplicado em planos, preços, matrículas e
  contratos.

## 5. Permissões

Novos módulos:

- `matricula.*`
- `contrato.*`

Perfis:

- Admin Regional/Local: matrícula e contrato completos.
- Gerente: matrícula e contrato.
- Recepção: matrícula e contrato.
- Diretor: leitura de matrícula e contrato.
- Superadmin: acesso total.

Total atual: **72 permissões**.

## 6. Rotas

| Método | Caminho | Permissão |
| --- | --- | --- |
| GET | `/admin/planos` | `plano.visualizar` |
| GET/POST | `/admin/planos/novo` | `plano.criar` |
| GET/POST | `/admin/planos/{id}/editar` | `plano.editar` |
| GET/POST | `/admin/planos/{id}/precos` | `plano.editar` |
| GET | `/admin/matriculas` | `matricula.visualizar` |
| GET/POST | `/admin/matriculas/novo` | `matricula.criar` |
| GET/POST | `/admin/matriculas/{id}/editar` | `matricula.editar` |
| GET | `/admin/contratos` | `contrato.visualizar` |
| GET/POST | `/admin/contratos/{id}/editar` | `contrato.editar` |

## 7. Serviços e repositórios

- `PlanoService`, `PlanoPrecoService`, `MatriculaService`, `ContratoService`.
- `PlanoRepository`, `PlanoPrecoRepository`, `ModalidadeRepository`,
  `MatriculaRepository`, `ContratoRepository`.
- Criação de plano + modalidades em transação.
- Criação de contrato + matrícula em transação.
- Validação de vigência, escopo, valores e duplicidade de matrícula ativa.

## 8. Interface

- Tela de planos com modalidades.
- Tela de preços por unidade vinculada ao plano.
- Tela de matrículas com seleção de aluno e preço ativo.
- Tela de contratos com edição de vigência, valor, vencimento, renovação e status.
- Menu lateral e dashboard com atalhos para Planos, Matrículas e Contratos.
- Modalidades padrão semeadas: Musculação, Funcional, Pilates, Cross Training e
  Hidroginástica.

## 9. Testes

- Criação de plano com modalidades e preço por unidade.
- Matrícula cria contrato e impede duplicidade ativa.
- Preço de outra unidade é rejeitado.
- Admin Local não acessa matrícula de outra unidade.
- Escopos de plano, preço, matrícula e contrato.

Situação atual: **65 testes e 493 asserções aprovados**.

## 10. Pendências da Onda 2

- Telas de modalidades, serviços adicionais e promoções.
- Telas de reajustes, descontos e transferências.
- Renovação automática e geração de cobranças (Onda 4).
- Histórico completo de alterações contratuais.

## 11. Próximo passo

Onda 3 — turmas, horários, reservas, frequência e controle de acesso; ou Onda 4
— financeiro, conforme prioridade do projeto.
