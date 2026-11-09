# 0005 — Administração, auditoria e atualização de permissões

**Data:** 2026-11-08
**Status:** aceita
**Relacionados:** `0004-rbac-perfis-niveis-escopo.md`

## Contexto

As Fases 4–6 do documento 02 exigiam administração mínima de redes, regiões,
unidades, usuários, perfis e vínculos; consolidação da auditoria; testes de
isolamento e não escalada; e homologação/documentação.

## Decisão

1. **Controllers administrativos separados por entidade** em
   `App\Controllers\Admin`, com base `AdminController` para contexto, escopo,
   flash e auditoria.
2. **Regras de negócio em Services:** `RedeService`, `RegiaoService`,
   `UnidadeService`, `PerfilService`, `UsuarioService` e `VinculoService`.
3. **Escopo obrigatório:** listagens e edições são limitadas ao contexto ativo.
   O backend valida o escopo antes de qualquer alteração.
4. **Anti-escalada:** `PerfilService` e `VinculoService` impedem conceder nível
   ou permissão superior ao ator e revogar vínculo acima do seu nível.
5. **CRUD sem exclusão física:** entidades usam status/situação; não há delete
   destrutivo nas telas administrativas.
6. **Auditoria:** ações de criação, edição, vínculo, revogação, login, logout e
   troca de contexto são registradas em `auditoria` com escopo, IP e user agent.
7. **Atualização de permissões:** as permissões efetivas são recalculadas a cada
   requisição a partir do banco. Não há cache de permissão na sessão; alterações
   passam a valer na próxima requisição do usuário.
8. **Permissões explícitas:** a tabela `usuario_permissao` e a resolução estão
   implementadas; a interface dedicada fica como pendência.
9. **Catálogo de permissões somente leitura:** alterações entram por seed ou
   migration.
10. **Testes:** isolamento entre unidades, anti-escalada, CRUD administrativo,
    recálculo de permissões e auditoria são cobertos por testes de integração.

## Consequências

- Fases 4–6 concluídas com CRUD administrativo funcional.
- Mudanças de permissão refletem imediatamente sem invalidação manual de sessão.
- Permanecem pendentes: política formal de retenção/mascaramento da auditoria
  (LGPD), interface de permissões explícitas e os módulos funcionais.
