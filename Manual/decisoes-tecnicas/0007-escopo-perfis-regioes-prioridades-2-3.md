# 0007 — Escopo de perfis, matriz de concessão e contexto regional

**Data:** 2026-11-08
**Status:** aceita
**Relacionados:** `0004-rbac-perfis-niveis-escopo.md`, `0006-correcao-escopo-prioridade-1.md`

## Contexto

As Prioridades 2 e 3 do plano de correção exigiram:

- escopo de perfis personalizados;
- proteção de perfis de sistema;
- matriz explícita de concessão;
- filtragem de vínculos e opções de formulário;
- contexto regional para o Admin Regional;
- limpeza dos dados de teste.

## Decisões

### Perfis

1. `perfis.rede_id` foi adicionado:
   - `NULL` para perfis globais/de sistema;
   - preenchido para perfis personalizados de uma rede.
2. Perfis de sistema são somente leitura para não Superadmin.
3. Perfis globais só podem ser criados/editados pelo Superadmin.
4. O Admin Regional cria perfis com escopo `REGIAO` ou `UNIDADE` na própria rede.
5. O Admin Local cria perfis com escopo `UNIDADE` na própria rede, com nível
   inferior ao seu.
6. A listagem de perfis mostra perfis visíveis com indicação de somente leitura.
7. Edição de perfil de sistema, global, de outra rede ou de nível igual/superior
   retorna 403 para não Superadmin.

### Concessão de perfis

8. A regra de nível é estrita: o ator só concede perfis de nível **inferior** ao
   seu, exceto o Superadmin, que pode conceder qualquer nível.
9. O ator precisa possuir todas as permissões do perfil concedido.
10. Perfis personalizados só podem ser concedidos na rede de origem.
11. Admin Local não concede `ADMIN_LOCAL`; Admin Regional não concede
    `ADMIN_REGIONAL`; o Superadmin concede administradores.

### Vínculos

12. Contexto de unidade lista apenas vínculos da própria unidade.
13. Contexto regional lista vínculos da região e das unidades da região.
14. O seletor de região não é exibido no contexto de unidade.
15. O seletor de perfis lista apenas perfis concedíveis pelo ator.

### Contexto regional

16. `context.regiao_id` foi incorporado à sessão.
17. `TenantContextService` cria contexto de região e contextos de unidade.
18. `AuthorizationService` resolve permissões por região.
19. Admin Regional tem escopo regional em unidades, usuários, vínculos,
    regiões e auditoria.
20. `regiao.criar` e `regiao.editar` foram removidas do `ADMIN_REGIONAL`; regiões
    passam a ser criadas/editadas pelo Superadmin.
21. Ao criar uma unidade em contexto regional, ela é vinculada automaticamente à
    região.

### Dados

22. Dados de teste foram removidos: usuário `novo@local.test`, perfil `TESTE`,
    região `sadasdasdasd` e artefatos de auditoria `teste.*`.
23. O banco de demonstração ficou com 1 rede, 1 região, 2 unidades, 8 usuários e
    8 perfis.

## Consequências

- Admin Local opera apenas na própria unidade, sem gerenciar perfis de sistema
  ou perfis globais.
- Admin Regional opera na própria região, com contexto regional explícito.
- Superadmin mantém administração global.
- Suíte atual: 49 testes e 318 asserções aprovados.
- Pendências: política de retenção/mascaramento da auditoria (LGPD), interface de
  permissões explícitas e módulos funcionais.
