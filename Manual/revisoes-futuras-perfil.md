# Lista de Revisões Futuras - Módulo Perfil

**Data:** 2026-11-09  
**Status:** Registrado para revisão futura  
**Módulo atual:** Mantido como está

---

## Revisões Planejadas

| # | Item | Descrição | Prioridade |
|---|------|-----------|------------|
| P.1 | **Escopo de permissões por módulo** | Permitir definir permissões por módulo/funcionalidade específica, não apenas global | Alta |
| P.2 | **Herança de perfis** | Permitir que perfis herdem permissões de outros perfis (ex: Gerente herda de Supervisor) | Média |
| P.3 | **Perfis dinâmicos/contexto** | Perfis que mudam permissões baseadas no contexto (unidade, região, horário) | Baixa |
| P.4 | **Auditoria de mudanças de perfil** | Log detalhado de quem alterou quais permissões de qual perfil e quando | Alta |
| P.5 | **Templates de perfil** | Modelos pré-definidos para criar perfis comuns rapidamente (Academia, Franquia, etc.) | Média |
| P.6 | **Validação de conflitos** | Alertar quando um perfil tem permissões conflitantes (ex: criar e aprovar mesma entidade) | Média |
| P.7 | **Perfis temporários/expiração** | Atribuir perfis com data de validade (ex: substituição de férias) | Baixa |
| P.8 | **Importação/Exportação de perfis** | Backup e restore de configurações de perfis entre ambientes | Baixa |

---

## Observações

O módulo Perfil atual atende às necessidades básicas de RBAC (Role-Based Access Control) com:
- Níveis hierárquicos (1-100)
- Escopos (Plataforma, Rede, Unidade, Região)
- Permissões granulares por módulo/ação
- Validação de nível máximo do ator

As revisões acima devem ser consideradas quando houver demanda real dos usuários ou quando o sistema escalar para cenários mais complexos de multi-tenancy e franquias.

---

## Itens Relacionados em Outras Listas

- **Pendências Onda 4**: Item 4.10 - Comissões e repasses (pode exigir novos perfis)
- **Pendências Onda 3**: Item 3.6 - Catraca como fonte de ponto de funcionários (pode exigir perfil RH)
- **Impacto Vínculos**: Unificação futura da interface de atribuição de perfis na edição de Usuário (Fase 2)