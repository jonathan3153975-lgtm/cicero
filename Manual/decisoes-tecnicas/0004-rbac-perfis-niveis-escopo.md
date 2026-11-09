# 0004 — RBAC híbrido com níveis, escopo e permissões explícitas

**Data:** 2026-11-08
**Status:** aceita
**Relacionados:** `0002-autenticacao-de-demonstracao.md`, `0003-tema-por-css-custom-properties.md`

## Contexto

O documento 02 define perfis administrativos, perfis operacionais, redes,
unidades, regiões, vínculos e permissões. A fundação atual possui autenticação
de demonstração e autorização plana por uma única string `role`. Isso não atende
aos requisitos de granularidade, escopo multiempresa nem auditoria.

## Decisão

1. **RBAC híbrido:** a autorização efetiva é calculada por permissões. Perfis
   agrupam permissões padrão; permissões explícitas podem complementar ou negar.
2. **Níveis numéricos:** cada perfil possui `nivel` inteiro. O nível expressa
   autoridade e capacidade de concessão, não acesso automático a dados.
3. **Escopo de vínculo:** `usuario_perfil` recebe `rede_id`, `unidade_id` e
   `regiao_id` opcionais. O perfil vale apenas no escopo atribuído.
4. **Permissões explícitas:** a tabela `usuario_permissao` registra concessões e
   negações adicionais no mesmo escopo. Negação prevalece sobre concessão.
5. **Regiões:** pertencem à rede e são agrupamentos opcionais de unidades
   próximas, para administração intermediária e relatórios regionais.
6. **Contexto:** a sessão armazena `context.rede_id` e `context.unidade_id`,
   sempre validados contra os vínculos do usuário no backend.
7. **Deny by default:** rotas protegidas declaram a permissão necessária.
8. **Auditoria:** ações administrativas relevantes são registradas em
   `auditoria`, sem senhas ou tokens.
9. **Catálogo:** permissões e perfis de sistema são carregados por seeds
   idempotentes e persistidos no banco. Novas permissões entram por seed ou
   migration.
10. **Níveis iniciais:** `SUPERADMIN=100`, `ADMIN_REGIONAL=80`,
    `ADMIN_LOCAL=60`, `DIRETOR=50`, `GERENTE=40`, `CONTABILIDADE=30`,
    `RECEPCAO=20`, `FUNCIONARIO=10`.

## Consequências

- A autenticação de demonstração será substituída por usuários reais.
- O middleware de papel plano será substituído por middleware de permissão e de
  escopo.
- O pipeline de middleware passa a aceitar parâmetros por rota.
- Consultas de dados empresariais deverão aplicar o escopo do contexto.
- A implementação será feita em fases; as Fases 0–3 param ao final e aguardam
  comando para as Fases 4–6.
