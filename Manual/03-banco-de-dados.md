# Banco de dados — perfis, níveis, unidades e permissões
**Documento de referência:** `projeto/2 - Criação de niveis de acesso e ambiente.md`
**Versão:** 1.2
**Status:** Fases 0–6 e Ondas 1–4 implementadas
**Data:** 2026-11-09

## 1. Objetivo

Registrar a modelagem e a implementação das Fases 0–6 do documento 02: redes,
regiões, unidades, usuários, perfis, permissões, vínculos, contexto, auditoria e
administração.

## 2. Migrations aplicadas

| Migration | Tabela |
| --- | --- |
| `0002_create_redes_table.php` | `redes` |
| `0003_create_regioes_table.php` | `regioes` |
| `0004_create_unidades_table.php` | `unidades` |
| `0005_create_usuarios_table.php` | `usuarios` |
| `0006_create_perfis_table.php` | `perfis` |
| `0007_create_permissoes_table.php` | `permissoes` |
| `0008_create_perfil_permissao_table.php` | `perfil_permissao` |
| `0009_create_usuario_rede_table.php` | `usuario_rede` |
| `0010_create_usuario_unidade_table.php` | `usuario_unidade` |
| `0011_create_regiao_unidade_table.php` | `regiao_unidade` |
| `0012_create_usuario_perfil_table.php` | `usuario_perfil` |
| `0013_create_usuario_permissao_table.php` | `usuario_permissao` |
| `0014_create_auditoria_table.php` | `auditoria` |
| `0029_create_financeiro_base_tables.php` | `contas_bancarias`, `formas_pagamento`, `centros_custo`, `fornecedores` |
| `0030_create_cobrancas_pagamentos_tables.php` | `cobrancas`, `cobranca_itens`, `pagamentos`, `pagamento_alocacoes`, `estornos` |
| `0031_create_contas_pagar_conciliacao_tables.php` | `renegociacoes`, `renegociacao_itens`, `contas_pagar`, `movimentos_caixa`, `conciliacoes_bancarias`, `conciliacao_itens` |
| `0032_create_planos_rede_table.php` | `planos_rede`, `redes.plano_rede_id` |

A migration `0001_create_health_checks_table.php` pertence à fundação.

## 3. Modelo

### 3.1 Estrutura organizacional

- `redes`: empresa contratante; CNPJ único; status e plano.
- `regioes`: agrupamento opcional de unidades dentro da rede (`rede_id`).
- `unidades`: academia física; sempre pertence a uma rede; código interno único
  por rede.
- `regiao_unidade`: associação entre região e unidade.

### 3.2 Identidade e autorização

- `usuarios`: cadastro único por e-mail, com `senha_hash` e status.
- `perfis`: `slug`, nome, `nivel`, `escopo`, `sistema` e status.
- `permissoes`: catálogo `modulo.acao` com slug único.
- `perfil_permissao`: permissões padrão de cada perfil.
- `usuario_perfil`: atribuição de perfil com escopo (`rede_id`, `unidade_id`,
  `regiao_id` opcionais).
- `usuario_permissao`: permissões explícitas adicionais ou negadas no mesmo
  escopo. A negação prevalece sobre a concessão.
- `usuario_rede` e `usuario_unidade`: participação do usuário na rede/unidade.
- `auditoria`: trilha administrativa com usuário, escopo, ação, entidade,
  valores anterior/posterior, IP, user agent e data.

## 4. Perfis de sistema e níveis

| Perfil | Nível | Escopo |
| --- | ---: | --- |
| `SUPERADMIN` | 100 | Plataforma |
| `ADMIN_REGIONAL` | 80 | Região |
| `ADMIN_LOCAL` | 60 | Unidade |
| `DIRETOR` | 50 | Unidade |
| `GERENTE` | 40 | Unidade |
| `CONTABILIDADE` | 30 | Unidade |
| `RECEPCAO` | 20 | Unidade |
| `FUNCIONARIO` | 10 | Unidade |

As permissões são explícitas por perfil e por vínculo. O nível expressa
autoridade e capacidade de concessão; não concede acesso automático a dados.

## 5. Permissões iniciais

O catálogo é carregado por seed a partir de
`database/seeds/data/permissions.php`. Módulos iniciais:

- `rede`, `regiao`, `unidade`, `usuario`, `perfil`, `permissao`, `vinculo`.
- `cliente`, `plano`, `financeiro`, `relatorio`, `auditoria`.
- `contexto`, `dashboard`, `configuracao`.

Total atual: 78 permissões.

## 6. Resolução de permissões

1. O usuário autentica e a sessão guarda `auth.user_id` e `auth.user`.
2. O contexto ativo é guardado em `context.rede_id` e `context.unidade_id`.
3. `AuthorizationService` carrega os vínculos ativos do usuário.
4. Aplica somente vínculos compatíveis com o contexto atual:
   - plataforma: aplica-se a qualquer contexto;
   - unidade: exige a unidade atual;
   - região: exige unidade pertencente à região;
   - rede: exige a rede atual.
5. Soma as permissões dos perfis e aplica permissões explícitas
   (`conceder`/`negar`).
6. A negação prevalece.

## 7. Contexto

- O contexto é resolvido a partir dos vínculos, nunca aceito às cegas do
  navegador.
- `TenantContextService` lista os contextos disponíveis:
  - plataforma para perfis globais;
  - unidades do usuário;
  - unidades das regiões às quais o usuário está vinculado;
  - redes quando o vínculo é de rede.
- `SUPERADMIN` recebe o contexto de plataforma e também os contextos de redes e
  unidades ativas.
- A troca de contexto exige `contexto.trocar` e um vínculo válido.
- A troca é auditada.

## 8. Seeds

Arquivos:

- `database/seeds/data/permissions.php`
- `database/seeds/data/profiles.php`
- `database/seeds/data/demo.php`

Comando:

```bash
composer seed
```

O seed é idempotente. Ele cria/atualiza:

- 78 permissões.
- 8 perfis de sistema.
- Rede demonstração, região Metropolitana e unidades Centro e Shopping.
- Usuários de demonstração com senha `Admin@123`.

> **Atenção:** o seed de demonstração redefine a senha dos usuários de teste.
> Não utilize essas credenciais em produção. Substitua o seed de demonstração
> por dados reais antes da publicação.

## 9. Usuários de demonstração

| E-mail | Perfil | Escopo |
| --- | --- | --- |
| `admin@local.test` | `SUPERADMIN` | Plataforma + contextos globais |
| `regional@local.test` | `ADMIN_REGIONAL` | Região Metropolitana |
| `local@local.test` | `ADMIN_LOCAL` | Unidade Centro |
| `diretor@local.test` | `DIRETOR` | Unidade Centro |
| `gerente@local.test` | `GERENTE` | Unidade Centro |
| `contabilidade@local.test` | `CONTABILIDADE` | Unidade Centro |
| `recepcao@local.test` | `RECEPCAO` | Unidade Centro |
| `funcionario@local.test` | `FUNCIONARIO` | Unidade Centro |

Senha de demonstração: `Admin@123`.

## 10. Comandos

```bash
composer migrate
composer seed
composer test
composer serve
```

## 11. Testes

A suíte cobre:

- autenticação válida, inválida e usuário inativo;
- permissões por perfil;
- isolamento por unidade;
- permissões regionais;
- não escalada de privilégio;
- contextos disponíveis e rejeição de contexto fora do escopo;
- conexão e persistência;
- CRUD de redes, unidades, usuários, perfis e vínculos;
- isolamento entre unidades e recálculo imediato de permissões;
- registro e consulta de auditoria.

Situação atual: 73 testes e 552 asserções aprovados.

## 12. Administração

Rotas em `/admin`, protegidas por permissão e escopo:

- `/admin` — visão geral.
- `/admin/redes` — listagem e edição de redes.
- `/admin/regioes` — regiões por rede.
- `/admin/unidades` — unidades por rede/escopo.
- `/admin/usuarios` — usuários por escopo e criação com vínculo.
- `/admin/perfis` — perfis e permissões com validação anti-escalada.
- `/admin/permissoes` — catálogo somente leitura.
- `/admin/vinculos` — atribuição e revogação de perfis com escopo.
- `/admin/auditoria` — últimas ações no escopo do contexto.

Regras aplicadas:

- O escopo do contexto limita listagens e edições.
- O backend valida nível e permissões em cada concessão.
- O seed de demonstração permanece idempotente.
- Ações administrativas são auditadas com usuário, escopo, IP e valores.

## 13. Atualização de permissões

As permissões efetivas são recalculadas a cada requisição a partir do banco.
Alterações de perfil, permissão ou vínculo passam a valer na próxima requisição
do usuário afetado, sem necessidade de cache de permissão na sessão.

## 14. Pendências

- Política formal de retenção e mascaramento da auditoria (LGPD).
- Interface para permissões explícitas em `usuario_permissao` (tabela e resolução
  já existem; falta tela).
- Módulos funcionais de alunos, financeiro, treinos e comunidade.
- Testes externos de carga, segurança e acessibilidade.

## 15. Correções de escopo — Prioridade 1

Aplicadas em 2026-11-08:

- Edição de usuários limitada ao vínculo direto com a unidade/rede e ao nível do ator.
- Bloqueio de edição, desativação e redefinição de senha de usuários de nível superior.
- Auditoria de unidade limitada a registros da própria unidade.
- Ações de região, rede, criação de unidade e perfis globais auditadas sem unidade.
- Migration  015_normalize_audit_scope.php para corrigir escopos históricos.
- Listagem de perfis filtrada pelo nível do ator.
- Edição de perfil de nível superior retorna 403.
- Suíte atual: 73 testes e 552 asserções.
## 16. Prioridades 2–3 — escopo de perfis e regiões

Aplicadas em 2026-11-08:

- perfis.rede_id diferencia perfis globais/de sistema de perfis da rede.
- Perfis de sistema e globais são somente leitura para não Superadmin.
- Admin Regional cria perfis REGIAO/UNIDADE na própria rede; Admin Local cria UNIDADE.
- Concessão de perfil exige nível estritamente inferior, exceto Superadmin.
- Admin Local não concede ADMIN_LOCAL; Admin Regional não concede ADMIN_REGIONAL.
- Vínculos de unidade listam apenas a unidade; contexto regional lista a região e suas unidades.
- Seletor de região não aparece no contexto de unidade.
- context.regiao_id integra a sessão e a resolução de permissões.
- Admin Regional tem escopo regional em unidades, usuários, vínculos, regiões e auditoria.
- Criação de unidade em contexto regional vincula a unidade à região.
- egiao.criar/egiao.editar removidas do Admin Regional.
- Dados de teste removidos; ambiente com 1 rede, 1 região, 2 unidades, 8 usuários e 8 perfis.
- Suíte atual: 73 testes e 552 asserções.
## 18. Onda 1 — Pessoas e alunos

Implementada em 2026-11-09:

- Novas tabelas: rquivos, pessoas, pessoa_enderecos, pessoa_contatos,
  pessoa_documentos, consentimentos, lunos, luno_responsaveis e
  luno_restricoes_saude.
- usuarios.pessoa_id adicionado com backfill dos usuários existentes.
- Catálogo ampliado para 72 permissões: pessoa.*, luno.*, esponsavel.*,
  lgpd.consentimento.* e saude.*.
- Rotas administrativas de pessoas e alunos com escopo por rede/unidade/região.
- Serviços PessoaService e AlunoService com validação de CPF/CNPJ,
  responsáveis, contatos e endereços.
- Telas de pessoas, alunos e responsáveis.
- Suíte atual: 73 testes e 552 asserções.

Pendências da onda: upload/tela de documentos, tela de consentimentos, tela de
restrições de saúde e dados de demonstração de alunos.
## 19. Onda 2 — Catálogo, planos, preços, contratos e matrículas

Implementada em 2026-11-09:

- Novas tabelas: modalidades, planos, plano_modalidades,
  plano_unidade_precos, servicos_adicionais, promocoes, contratos,
  matriculas, matricula_servicos, eajustes, descontos e 	ransferencias.
- Serviços: PlanoService, PlanoPrecoService, MatriculaService e
  ContratoService.
- Telas: planos, preços por unidade, matrículas e contratos.
- Regras: preço ativo da unidade do aluno, uma matrícula ativa por unidade,
  contrato criado em transação, escopo por rede/unidade/região.
- Catálogo ampliado para 72 permissões com matricula.* e contrato.*.
- Modalidades padrão semeadas.
- Suíte atual: 73 testes e 552 asserções.

Pendências: telas de modalidades, serviços, promoções, reajustes, descontos e
transferências; renovação automática e cobranças na Onda 4.
## 20. Onda 3 — Operação e controle de acesso

Implementada em 2026-11-09:

- Novas tabelas: 	urmas, 	urma_horarios, 	urma_alunos, eservas,
  requencias, dispositivos_acesso, eventos_acesso e ocorrencias.
- Serviços: TurmaService, FrequenciaService, ReservaService e
  AcessoService.
- Telas: turmas, horários, frequência, reservas, dispositivos e eventos de
  acesso.
- Regras: sobreposição de horários, frequência única por aluno/turma/data,
  reserva no dia do horário, capacidade e isolamento por unidade.
- Catálogo ampliado para 72 permissões com 	urma.*, requencia.*,
  reserva.*, dispositivo.*, evento_acesso.* e ocorrencia.*.
- Suíte atual: 73 testes e 552 asserções.

Pendências: tela de vínculo turma/aluno, professor no horário, tela de
ocorrências, registro manual de eventos e integração real com catracas.

## 21. Onda 4 — Financeiro

Implementada em 2026-11-09:

- Novas tabelas: contas_bancarias, formas_pagamento, centros_custo,
  fornecedores, cobrancas, cobranca_itens, pagamentos, pagamento_alocacoes,
  estornos, renegociacoes, renegociacao_itens, contas_pagar, movimentos_caixa,
  conciliacoes_bancarias e conciliacao_itens.
- Serviços: CobrancaService, PagamentoService, ContaPagarService e
  ConciliacaoService.
- Repositórios: CobrancaRepository, PagamentoRepository, ContaPagarRepository,
  ContaBancariaRepository, FormaPagamentoRepository, CentroCustoRepository,
  MovimentoCaixaRepository, ConciliacaoRepository e EstornoRepository.
- Telas: cobranças, pagamentos, contas a pagar, contas bancárias e conciliação.
- Regras: mensalidade única por matrícula/competência, pagamento limitado ao
  saldo, estorno com recálculo, quitação de despesas com movimento de saída,
  conciliação automática por data/tipo/valor e escopo por rede/unidade/região.
- Seeds: formas de pagamento globais e conta "Caixa Interno" + centro "Geral"
  por unidade.
- Catálogo passa a 78 permissões com modalidades e planos de rede, mantendo financeiro.visualizar e
  financeiro.editar.
- Suíte atual: 73 testes e 552 asserções.

Pendências: geração recorrente em lote, integração real de PIX/boleto,
tela de fornecedores, tela de renegociação, upload OFX/CSV e relatórios
financeiros.
## 22. Planos de rede

Implementado em 2026-11-09:

- Tabela planos_rede com nome único, descrição rica sanitizada e status.
- Coluna redes.plano_rede_id com chave estrangeira para planos_rede.
- CRUD /admin/planos-rede exclusivo do Superadmin.
- Permissões plano_rede.visualizar, plano_rede.criar e plano_rede.editar.
- Seeds: DEMO, Básico, Profissional e Enterprise; redes antigas vinculadas por nome.
- Formulário de Redes reorganizado em linhas/colunas, com máscaras de CNPJ, telefone e CEP, além de autopreenchimento via ViaCEP.
- Catálogo atual: 78 permissões.
- Suíte atual: 73 testes e 552 asserções.