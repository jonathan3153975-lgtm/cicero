# 0006 — Correção de escopo (Prioridade 1)

**Data:** 2026-11-08
**Status:** aceita
**Relacionados:** `0004-rbac-perfis-niveis-escopo.md`, `0005-administracao-e-auditoria.md`

## Contexto

A análise do painel identificou que o Administrador Local tinha a lista de
usuários corretamente limitada à unidade, mas o escopo não era aplicado de forma
consistente em edição de usuários, auditoria e gestão de perfis.

## Decisões

1. **Edição de usuários**
   - `VinculoRepository::userInDirectScope()` considera apenas vínculo direto
     com a unidade/rede, sem incluir cobertura regional.
   - `UsuarioService::assertCanManage()` valida o escopo direto e compara o maior
     nível do usuário-alvo com o nível do ator no contexto.
   - Usuários de nível superior ao ator não podem ser editados, desativados nem
     ter a senha redefinida.
2. **Auditoria**
   - Contexto de unidade: somente registros com `unidade_id` da unidade.
   - Contexto de rede: registros da rede.
   - Contexto de plataforma: todos.
   - Ações de região, rede, unidade (criação) e perfis globais passam a ser
     auditadas como nível de rede/plataforma, sem `unidade_id`.
   - A migration `0015_normalize_audit_scope.php` corrige registros históricos
     que haviam sido gravados com escopo de unidade indevidamente.
3. **Perfis**
   - `PerfilService::manageableProfiles()` lista apenas perfis com nível menor ou
     igual ao do ator.
   - `PerfilService::assertCanManage()` retorna 403 para edição de perfil de
     nível superior.
   - A listagem de perfis e o formulário passam a respeitar o nível do ator.

## Consequências

- Admin Local não consegue mais abrir a edição de Admin Regional ou Superadmin.
- Admin Local não consegue redefinir a senha desses usuários.
- Auditoria de unidade não mostra mais ações de região/rede.
- A listagem de perfis oculta `SUPERADMIN` e `ADMIN_REGIONAL` para o Admin Local.
- Permanecem para as próximas prioridades: escopo global de perfis, concessão de
  mesmo nível, escopo regional e filtragem de vínculos/opções de formulário.
- Suíte atual: 47 testes e 315 asserções aprovados.
