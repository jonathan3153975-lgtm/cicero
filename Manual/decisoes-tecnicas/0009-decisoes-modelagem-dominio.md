# 0009 — Decisões de modelagem do domínio

**Data:** 2026-11-09
**Status:** aceita
**Relacionados:** `projeto/3 - Banco de dados e modelagem do domínio.md`

## Contexto

O documento 03 foi submetido à análise. Todas as decisões de modelagem foram
respondidas e incorporadas à especificação.

## Decisões confirmadas

1. **Pessoa unificada:** `pessoas` com especializações para aluno, funcionário e
   fornecedor; sem duplicação de cadastro.
2. **Contabilidade interna:** partidas dobradas internas. Exportação para sistema
   contábil externo fica prevista como adapter futuro.
3. **Folha interna:** cálculo inicial interno. Evolução para híbrido/externo
   permanece possível.
4. **eSocial e NF-e:** apenas integração futura; fora do escopo atual.
5. **Contabilidade por unidade:** plano de contas, centros de custo, períodos e
   lançamentos independentes por unidade. Consolidação por rede é relatório
   gerencial futuro.
6. **Perfil RH:** criar perfil `RH`, nível 35, escopo `UNIDADE`.
7. **Acesso de funcionários:** somente por necessidade funcional; a catraca
   registra ponto independentemente de login.
8. **Dados de saúde:** acesso por perfis específicos e, para terceiros, somente
   mediante autorização explícita do cliente/responsável.
9. **Retenção LGPD:** padrão de 2 anos, ressalvadas obrigações legais específicas
   (fiscais, trabalhistas e de saúde).
10. **Arquivos:** arquivos personalíssimos do cliente em armazenamento local
    privado; demais arquivos no servidor. Object storage poderá ser adicionado
    como adapter.
11. **Moeda:** BRL nesta fase.
12. **Exclusão:** `status` em vez de exclusão física.

## Consequências

- Contabilidade e custos são independentes por unidade.
- Folha interna integra-se à contabilidade por unidade.
- Acesso de funcionários é por necessidade funcional; catraca é fonte de ponto.
- Dados de saúde exigem autorização explícita para não profissionais.
- Perfil `RH` e permissões `rh.*`, `contabilidade.*` e `saude.*` entram no
  catálogo.
- Retenção padrão de 2 anos, com exceções legais.
- A Onda 1 (pessoas e alunos) está liberada para implementação.
