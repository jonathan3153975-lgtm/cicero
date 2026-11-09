# Pendências da Onda 4

**Status:** registradas para verificação posterior
**Data:** 2026-11-09

| # | Pendência | Verificação futura |
| --- | --- | --- |
| 4.1 | Geração recorrente de mensalidades em lote | Verificar competência, matrículas elegíveis, duplicidade e agendamento |
| 4.2 | Integração real de PIX e boleto | Verificar gateway, webhook, idempotência e conciliação automática |
| 4.3 | Tela de fornecedores | Verificar cadastro, vínculo com pessoas e uso em contas a pagar |
| 4.4 | Tela de renegociação de dívidas | Verificar uso das tabelas `renegociacoes`/`renegociacao_itens` e baixa de cobranças |
| 4.5 | Conciliação manual item a item | Verificar vínculo manual, diferenças e desfazer conciliação |
| 4.6 | Upload de arquivo OFX/CSV | Verificar upload real, encoding, layout e armazenamento |
| 4.7 | Relatórios e fluxo de caixa | Verificar filtros por rede/unidade/período e exportação |
| 4.8 | Juros, multa e descontos automáticos | Verificar cálculo por atraso, negociação e limites |
| 4.9 | Anexos de comprovante e documento fiscal | Verificar uso de `arquivos`, permissões e retenção LGPD |
| 4.10 | Comissões e repasses | Implementar na Onda 5 (contabilidade/repasses) |

## Observação

As pendências não bloquearam a Onda 4. A base de dados e os serviços financeiros
principais estão implementados; as verificações devem ocorrer antes da entrada em
produção do módulo financeiro.
