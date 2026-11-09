# Onda 4 — Financeiro

**Documento de referência:** `projeto/3 - Banco de dados e modelagem do domínio.md`
**Status:** implementada e validada
**Data:** 2026-11-09

## 1. Objetivo

Implementar a base financeira da rede: contas bancárias, formas de pagamento,
centros de custo, cobranças, pagamentos, estornos, contas a pagar, movimentos de
caixa e conciliação bancária, integrando a geração de mensalidades às matrículas
e aos contratos.

## 2. Migrations aplicadas

| Migration | Conteúdo |
| --- | --- |
| `0029_create_financeiro_base_tables.php` | `contas_bancarias`, `formas_pagamento`, `centros_custo`, `fornecedores` |
| `0030_create_cobrancas_pagamentos_tables.php` | `cobrancas`, `cobranca_itens`, `pagamentos`, `pagamento_alocacoes`, `estornos` |
| `0031_create_contas_pagar_conciliacao_tables.php` | `renegociacoes`, `renegociacao_itens`, `contas_pagar`, `movimentos_caixa`, `conciliacoes_bancarias`, `conciliacao_itens` |

## 3. Modelo aplicado

- Contas bancárias por rede/unidade, com tipo, agência, conta, saldo inicial e
  status.
- Formas de pagamento globais ou por rede.
- Centros de custo por unidade.
- Fornecedores vinculados a `pessoas` (base preparada para a tela de compras).
- Cobranças por aluno, matrícula e contrato, com competência, número único,
  valores de original/desconto/juros/multa/total e status.
- Itens de cobrança vinculados a plano ou serviço.
- Pagamentos com conta bancária, forma, data, compensação, transação e
  comprovante opcional.
- Alocação de pagamentos em cobranças (`pagamento_alocacoes`).
- Estornos com motivo, valor, data e usuário.
- Contas a pagar por unidade, fornecedor e centro de custo.
- Movimentos de caixa de entrada/saída por conta bancária, com origem e
  referência.
- Conciliação bancária por conta e competência, com itens importados do extrato
  e vínculo ao movimento de caixa.

## 4. Regras de negócio

- Mensalidade gerada a partir de matrícula ativa/suspensa, usando o valor mensal
  do preço vigente e a competência informada.
- Mensalidade duplicada para a mesma matrícula e competência é bloqueada.
- Cobrança avulsa exige aluno no contexto do usuário, valor positivo e desconto
  menor ou igual ao valor.
- Pagamento exige cobrança aberta/vencida/parcial, valor positivo e limite de
  saldo.
- Conta bancária e forma de pagamento devem estar ativas e pertencer à rede da
  cobrança; a conta deve ser da mesma unidade.
- Pagamento confirmado gera alocação, movimento de caixa de entrada e atualiza o
  status da cobrança para `PAGA` ou `PARCIAL`.
- Estorno marca o pagamento como `ESTORNADO`, registra o estorno, gera movimento
  de saída e recalcula o status da cobrança.
- Conta a pagar paga gera movimento de caixa de saída e só pode ser quitada com
  conta bancária da mesma unidade.
- Conciliação importa CSV (`data;descrição;valor;tipo`), casa automaticamente
  com movimentos de mesmo dia, tipo e valor e só fecha sem itens pendentes.
- Escopo por rede, unidade e região em todas as operações.
- Pagamentos e contas a pagar usam estorno/cancelamento, sem exclusão física.

## 5. Permissões

Não foram necessárias novas permissões. O módulo reutiliza:

- `financeiro.visualizar`
- `financeiro.editar`

Total permanece em **72 permissões**.

## 6. Rotas

| Método | Caminho | Permissão |
| --- | --- | --- |
| GET | `/admin/cobrancas` | `financeiro.visualizar` |
| GET | `/admin/cobrancas/nova` | `financeiro.editar` |
| POST | `/admin/cobrancas` | `financeiro.editar` |
| GET | `/admin/cobrancas/{id}` | `financeiro.visualizar` |
| GET | `/admin/pagamentos` | `financeiro.visualizar` |
| GET | `/admin/pagamentos/novo` | `financeiro.editar` |
| POST | `/admin/pagamentos` | `financeiro.editar` |
| POST | `/admin/pagamentos/{id}/estornar` | `financeiro.editar` |
| GET | `/admin/contas-pagar` | `financeiro.visualizar` |
| GET | `/admin/contas-pagar/nova` | `financeiro.editar` |
| POST | `/admin/contas-pagar` | `financeiro.editar` |
| POST | `/admin/contas-pagar/{id}/pagar` | `financeiro.editar` |
| POST | `/admin/contas-pagar/{id}/cancelar` | `financeiro.editar` |
| GET | `/admin/contas-bancarias` | `financeiro.visualizar` |
| POST | `/admin/contas-bancarias` | `financeiro.editar` |
| GET | `/admin/conciliacoes` | `financeiro.visualizar` |
| GET | `/admin/conciliacoes/nova` | `financeiro.editar` |
| POST | `/admin/conciliacoes` | `financeiro.editar` |
| GET | `/admin/conciliacoes/{id}` | `financeiro.visualizar` |
| POST | `/admin/conciliacoes/{id}/importar` | `financeiro.editar` |
| POST | `/admin/conciliacoes/{id}/fechar` | `financeiro.editar` |

## 7. Serviços e repositórios

- `CobrancaService`, `PagamentoService`, `ContaPagarService`,
  `ConciliacaoService`.
- `CobrancaRepository`, `PagamentoRepository`, `ContaPagarRepository`,
  `ContaBancariaRepository`, `FormaPagamentoRepository`,
  `CentroCustoRepository`, `MovimentoCaixaRepository`,
  `ConciliacaoRepository`, `EstornoRepository`.
- Transações para geração de cobrança, pagamento/estorno, quitação de conta a
  pagar e importação de conciliação.

## 8. Interface

- Tela de cobranças com filtros, geração por matrícula, cobrança avulsa e
  detalhe com itens e saldo.
- Tela de pagamentos com registro, alocação e estorno.
- Tela de contas a pagar com criação, quitação e cancelamento.
- Tela de contas bancárias com listagem e criação.
- Tela de conciliações com criação, importação de extrato e fechamento.
- Menu lateral e dashboard com indicadores financeiros.

## 9. Seeds

- Formas de pagamento globais: Dinheiro, PIX, Cartão de Crédito, Cartão de
  Débito, Boleto e Transferência.
- Conta "Caixa Interno" e centro de custo "Geral" por unidade.

## 10. Testes

- Geração de mensalidade a partir de matrícula, pagamento e estorno.
- Bloqueio de pagamento acima do saldo.
- Conta a pagar quitada com movimento de saída.
- Conciliação com importação de CSV, casamento automático e fechamento.

Situação atual: **69 testes e 536 asserções aprovados**.

## 11. Pendências da Onda 4

- Geração recorrente automática de mensalidades em lote.
- Integração real com PIX, boleto e gateways de cartão.
- Tela de fornecedores e vínculo no formulário de contas a pagar.
- Tela de renegociação de dívidas (tabelas criadas).
- Conciliação com upload de arquivo OFX/CSV e conciliação manual item a item.
- Relatórios financeiros e fluxo de caixa.

## 12. Próximo passo

Onda 5 — contabilidade e repasses, conforme o plano do documento 03.
