# Relatório de Impacto: Remoção do Módulo Vínculos e Integração no Módulo Usuários

**Data:** 2026-11-09  
**Status:** Análise técnica

---

## 1. Resumo Executivo

A remoção do módulo **Vínculos** (`/admin/vinculos`) e a integração de suas funcionalidades no módulo **Usuários** (`/admin/usuarios`) tem **impacto SIGNIFICATIVO** na arquitetura atual, **não sendo insignificante**. A remoção direta quebraria o sistema de autorização (RBAC) e o controle de escopo.

### Conclusão
**NÃO RECOMENDADO** eliminar o módulo Vínculos neste momento. As funcionalidades de atribuição de perfis com escopo (rede/unidade/região) são centrais ao RBAC e usadas por múltiplos serviços. A integração deve ser feita de forma gradual, mantendo a separação de responsabilidades.

---

## 2. Análise Detalhada de Dependências

### 2.1 Serviços que dependem de `VinculoRepository`

| Serviço | Métodos que usam `VinculoRepository` | Impacto se removido |
|---------|--------------------------------------|---------------------|
| `AuthorizationService` | `profileAssignmentsForUser()`, `explicitPermissionsForUser()`, `highestLevelForContext()` | **CRÍTICO** - Quebra autorização completa |
| `UsuarioService` | `assertCanManage()`, `createWithMembership()` (linkRede/linkUnidade) | **ALTO** - Quebra criação/edição de usuários e verificação de escopo |
| `VinculoService` | `assignProfile()`, `revokeAssignment()`, `hasRedeMembership()`, `hasUnidadeMembership()` | **CRÍTICO** - É o próprio serviço de vínculos |
| `TenantContextService` | `profileAssignmentsForUser()` | **ALTO** - Quebra troca de contexto/unidade |

### 2.2 Tabelas de banco de dados afetadas

| Tabela | Função | Usada por |
|--------|--------|-----------|
| `usuario_perfil` | Atribuição de perfis com escopo (rede/unidade/região) | AuthorizationService, VinculoService, UsuarioService |
| `usuario_rede` | Vínculo direto usuário-rede | UsuarioService (createWithMembership), VinculoRepository |
| `usuario_unidade` | Vínculo direto usuário-unidade | UsuarioService (createWithMembership), VinculoRepository |

### 2.3 Controllers afetados

- `VinculoController` - Seria removido completamente
- `UsuarioController` - Precisaria absorver toda lógica de atribuição de perfis
- `PerfilController` - Usa `AuthorizationService` que depende de `VinculoRepository`

---

## 3. O que o Módulo Vínculos Faz Atualmente

1. **Atribuição de perfis com escopo**: Um usuário pode ter perfis diferentes em redes, unidades ou regiões diferentes
2. **Validação de escopo**: Garante que só se pode atribuir perfis dentro do escopo do ator
3. **Hierarquia de níveis**: Impede atribuição de perfil de nível superior ao do ator
4. **Revogação controlada**: Remove atribuições mantendo auditoria
5. **Membership (usuario_rede/usuario_unidade)**: Vínculos base para criação de usuários

---

## 4. Proposta de Integração Gradual (Recomendada)

### Fase 1 - Manter Vínculos, Melhorar Usuários (Imediato)
- Implementar novo layout de Usuários com filtros cascata (Rede → Região → Unidade)
- Adicionar busca de Pessoa (CPF/CNPJ) com vínculo obrigatório
- Manter campo senha temporário, planejar envio por e-mail
- **Manter módulo Vínculos funcional**

### Fase 2 - Unificar Interface (Médio prazo)
- Adicionar aba "Perfis/Vínculos" dentro da edição de Usuário
- Permitir gerenciar perfis do usuário na mesma tela
- Manter `VinculoService` e `VinculoRepository` como backend

### Fase 3 - Refatoração de Arquitetura (Longo prazo)
- Avaliar se `usuario_perfil` pode ser movido para `UsuarioService`
- Manter `AuthorizationService` isolado (ele é o core do RBAC)
- Só então considerar remover `VinculoController` e views

---

## 5. Riscos da Remoção Imediata

| Risco | Probabilidade | Impacto |
|-------|---------------|---------|
| Quebra de login/autorização | Muito Alta | Crítico |
| Perda de controle de escopo por região | Alta | Alto |
| Inconsistência em `usuario_rede`/`usuario_unidade` | Média | Médio |
| Falha na troca de contexto (TenantContextService) | Alta | Alto |
| Quebra de auditoria de atribuições | Média | Médio |

---

## 6. Recomendação Final

**NÃO ELIMINE o módulo Vínculos agora.** O impacto é **significativo** e afeta o core de autorização do sistema.

**Ação imediata**: Implementar as melhorias no módulo Usuários (layout, filtros, busca de pessoa) mantendo Vínculos como está. Registrar a unificação futura como pendência técnica.

---

## 7. Pendências Relacionadas (a registrar)

- [ ] Unificar interface de atribuição de perfis na edição de Usuário (Fase 2)
- [ ] Envio de senha temporária por e-mail + validação no primeiro login
- [ ] Revisão do módulo Perfil (manter como está, listar para revisão futura)