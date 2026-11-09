# Banco de dados e modelagem do domínio

**Documento:** `03-banco-de-dados.md`\
**Versão:** 1.1 (decisões incorporadas)\
**Status:** Especificação de modelagem — decisões confirmadas; não implementada\
**Dependência:** `02 - Criação de niveis de acesso e ambiente.md`\
**Última atualização:** 2026-11-09

------------------------------------------------------------------------

## 1. Objetivo

Definir a modelagem física e conceitual do domínio da plataforma de gestão de
redes de academias, incluindo:

- Pessoas, alunos, responsáveis e dados cadastrais.
- Catálogo comercial, planos, contratos e matrículas.
- Operação de unidades, turmas, frequência e controle de acesso.
- Financeiro: cobranças, pagamentos, despesas e conciliação.
- **Contabilidade da unidade:** plano de contas, lançamentos, períodos e
  demonstrativos.
- **RH:** funcionários, cargos, jornada, ponto, férias, benefícios, folha e
  rescisão.
- Treinos, avaliações físicas e dados sensíveis de saúde.
- Comunicação e notificações.
- Auditoria, LGPD e retenção de dados.

O documento serve de base para análise antes da implementação. Nenhuma tabela
descrita aqui deve ser criada sem aprovação das decisões pendentes da seção 15.

------------------------------------------------------------------------

## 2. Escopo e limites

### 2.1 Incluído

- Modelo multiempresa e multiunidade.
- Cadastro unificado de pessoas físicas e jurídicas.
- Alunos, responsáveis e vínculos familiares/financeiros.
- Catálogo de modalidades, planos, serviços e preços por unidade.
- Contratos, matrículas, reajustes, descontos e transferências.
- Turmas, horários, reservas, frequência e controle de acesso.
- Contas a receber, cobranças, pagamentos, estornos, renegociações e contas a
  pagar.
- Contabilidade por unidade, com partidas dobradas, centros de custo, períodos e
  integração com financeiro e folha.
- RH: cadastro de funcionários, cargos, departamentos, jornada, ponto, banco de
  horas, férias, afastamentos, benefícios, folha e rescisão.
- Treinos, fichas, avaliações físicas e restrições de saúde.
- Comunicação, notificações e fila de envio.
- Auditoria, consentimentos, solicitações LGPD e classificação de dados.

### 2.2 Fora do escopo desta etapa

- Implementação de integrações externas reais (gateway de pagamento, PIX,
  boletos, bancos, eSocial, NF-e/NFS-e, WhatsApp/SMS/push e catracas).
- Cálculo trabalhista/tributário definitivo sem validação contábil e jurídica.
- Aplicativo mobile nativo.
- BI e data warehouse.
- Regras fiscais específicas por regime tributário.

As integrações serão tratadas como **adapters** e **pontos de extensão**, não
como dependências estruturais do banco.

### 2.3 Lacuna identificada: RH e contabilidade

O documento 02 definiu o perfil `CONTABILIDADE` e permissões financeiras, mas
**não definiu o domínio de RH** (funcionários, ponto, folha, benefícios,
rescisão) nem a **contabilidade formal** (plano de contas, lançamentos,
competência, fechamento e demonstrativos).

Este documento inclui ambos como módulos de primeira classe:

- **Contabilidade da unidade:** contabilidade de partidas dobradas, com centros
  de custo por unidade e integração com financeiro e folha.
- **RH:** gestão de pessoal e folha por unidade, com integração contábil.

Isso exige também uma extensão do catálogo de perfis e permissões da seção 12.

### 2.4 Decisões aprovadas e pendências

| # | Decisão | Situação |
| --- | --- | --- |
| 1 | Pessoa unificada (`pessoas` + especializações) | Confirmado |
| 2 | Contabilidade interna com partidas dobradas; exportação futura permitida | Confirmado |
| 3 | Folha calculada internamente; futuro híbrido/externo permitido | Confirmado |
| 4 | eSocial e NF-e apenas como integração futura | Confirmado |
| 5 | Contabilidade e custos por unidade, independentes | Confirmado |
| 6 | Perfil `RH` nível 35 | Confirmado |
| 7 | Acesso de funcionários apenas por necessidade funcional; catraca registra ponto independentemente de login | Confirmado |
| 8 | Dados de saúde por perfis específicos + autorização do cliente | Confirmado |
| 9 | Retenção LGPD padrão de 2 anos, ressalvadas obrigações legais específicas | Confirmado |
| 10 | Arquivos: pessoais do cliente em armazenamento local privado; demais no servidor | Confirmado |
| 11 | Moeda BRL nesta fase | Confirmado |
| 12 | `status` em vez de exclusão física | Confirmado |

Todas as decisões de modelagem estão confirmadas. A Onda 0 fica limitada a
registrar o ADR final e congelar nomes/convenções antes da Onda 1.

------------------------------------------------------------------------

## 3. Estado atual da implementação

### 3.1 Tabelas existentes

- `schema_migrations`
- `health_checks`
- `redes`
- `regioes`
- `unidades`
- `usuarios`
- `perfis`
- `permissoes`
- `perfil_permissao`
- `usuario_rede`
- `usuario_unidade`
- `regiao_unidade`
- `usuario_perfil`
- `usuario_permissao`
- `auditoria`

### 3.2 O que já está resolvido

- Multiempresa com `redes`.
- Multiunidade com `unidades`.
- Agrupamento regional com `regioes`/`regiao_unidade`.
- RBAC híbrido com perfis, permissões explícitas e escopo.
- Vínculos com escopo de rede/unidade/região.
- Contexto validado e auditoria administrativa.
- Autenticação real em `usuarios`.

### 3.3 O que será estendido

- `usuarios` receberá vínculo opcional com `pessoas` (`pessoa_id`).
- `perfis`/`permissoes` receberão os novos módulos de RH, contabilidade,
  treinos, comunicação e LGPD.
- `auditoria` poderá receber `regiao_id` e `modulo` para filtros mais precisos.

### 3.4 O que não será alterado de forma destrutiva

- Tabelas existentes não serão removidas.
- Novas colunas serão preferencialmente anuláveis.
- Backfills serão feitos em migrations separadas.
- Nenhuma operação de negócio será apagada fisicamente.

------------------------------------------------------------------------

## 4. Princípios e convenções

### 4.1 Identificadores e tipos

| Item | Convenção |
| --- | --- |
| Chave primária | `BIGINT UNSIGNED AUTO_INCREMENT` |
| Identificador público | `public_id CHAR(26)` ULID, único, quando houver exposição externa |
| Dinheiro | `DECIMAL(15,2)` |
| Percentuais | `DECIMAL(7,4)` |
| Datas | `DATE` |
| Horas | `TIME` |
| Data/hora | `DATETIME` |
| Booleanos | `TINYINT(1)` |
| Enums de negócio | `VARCHAR(30)` validado na aplicação, não `ENUM` do banco |
| Metadados flexíveis | `JSON` apenas para dados não relacionais |
| Textos longos | `TEXT`/`MEDIUMTEXT` |

### 4.2 Chaves, índices e integridade

- Toda tabela de negócio possui `created_at` e `updated_at`.
- Tabelas relevantes possuem `created_by` e `updated_by` (`usuarios.id`).
- Chaves estrangeiras usam `RESTRICT` por padrão; `CASCADE` apenas em vínculos
  dependentes e controlados.
- Índices obrigatórios em chaves estrangeiras, datas de movimento, status e
  campos de busca frequente.
- Unicidade sempre que houver risco de duplicidade de negócio.
- Preferir desativação lógica (`status`) a exclusão física.
- Registros financeiros, contábeis e de folha são imutáveis após fechamento.
- Estornos e reversões são registrados como novos lançamentos, nunca por
  alteração destrutiva.

### 4.3 Escopo multiempresa

- Toda tabela operacional possui `rede_id` e/ou `unidade_id`.
- Tabelas de catálogo podem ter `rede_id NULL` (global) ou preenchido (rede).
- Tabelas de unidade possuem `rede_id` e `unidade_id` juntos.
- Consultas sempre aplicam o escopo do contexto autenticado.
- O backend nunca aceita `rede_id`/`unidade_id` do navegador sem validação.
- Quando possível, usar chave composta `(unidade_id, rede_id)` com FK composta
  para impedir unidade de outra rede.
- Testes de acesso cruzado são obrigatórios em todas as ondas.

### 4.4 Pessoas e papéis

Adotar **pessoa única** com especializações por papel:

```text
pessoas
├── alunos
│   └── aluno_responsaveis
├── funcionarios
│   ├── funcionario_contratos
│   ├── registros_ponto
│   └── folha_itens
├── fornecedores
└── usuarios (vínculo opcional)
```

- A mesma pessoa pode ser aluna e funcionária, sem duplicar cadastro.
- `usuarios.pessoa_id` será anulável e preenchido por backfill.
- Nem todo funcionário terá acesso ao sistema.
- Nem todo usuário será aluno ou funcionário.

### 4.5 Arquivos

Centralizar uploads em `arquivos`:

- `id`, `public_id`, `disco`, `caminho`, `nome_original`, `mime`, `tamanho`,
  `hash`, `status`, `enviado_por`, `created_at`.
- Tabelas de documentos, comprovantes, anexos e fotos referenciam `arquivo_id`.
- `disco=private_local`: arquivos personalíssimos do cliente, em armazenamento
  local privado (`storage/private`), fora de `public/`.
- `disco=server`: demais arquivos operacionais no servidor.
- Object storage externo é uma evolução futura, via adapter.
- Acesso sempre mediado pelo backend.

### 4.6 Auditoria e LGPD

- Dados pessoais, financeiros, de folha e de saúde recebem classificação.
- Retenção padrão de **2 anos**, ressalvadas obrigações legais específicas
  (fiscais, trabalhistas e de saúde).
- Dados de saúde têm acesso restrito e retenção definida.
- Logs não registram senhas, tokens, dados bancários ou conteúdo sensível.
- Consentimentos e solicitações LGPD são registrados.
- Ações críticas geram trilha de auditoria.
- Relatórios e exportações também são auditados.

------------------------------------------------------------------------

## 5. Mapa do domínio

```text
Organização
└── Rede
    ├── Região
    └── Unidade
        ├── Pessoas / Alunos / Responsáveis
        ├── Catálogo / Planos / Contratos / Matrículas
        ├── Operação / Turmas / Frequência / Acesso
        ├── Financeiro / Cobranças / Pagamentos / Contas a Pagar
        ├── Contabilidade / Plano de Contas / Lançamentos / Períodos
        ├── RH / Funcionários / Ponto / Férias / Folha
        ├── Treinos / Fichas / Avaliações
        └── Comunicação / Notificações
```

------------------------------------------------------------------------

## 6. Domínios e tabelas propostas

### 6.1 Pessoas, alunos e responsáveis

#### `pessoas`

| Campo | Tipo | Observação |
| --- | --- | --- |
| `id` | BIGINT UNSIGNED PK | |
| `public_id` | CHAR(26) UNIQUE | ULID para links externos |
| `tipo` | VARCHAR(10) | `PF` ou `PJ` |
| `nome` | VARCHAR(180) | obrigatório |
| `nome_social` | VARCHAR(180) NULL | |
| `cpf` | VARCHAR(11) NULL UNIQUE | apenas PF |
| `cnpj` | VARCHAR(14) NULL UNIQUE | apenas PJ |
| `data_nascimento` | DATE NULL | |
| `sexo` | VARCHAR(20) NULL | opcional |
| `estado_civil` | VARCHAR(30) NULL | opcional |
| `email_principal` | VARCHAR(180) NULL | |
| `telefone_principal` | VARCHAR(30) NULL | |
| `foto_arquivo_id` | BIGINT UNSIGNED NULL | FK `arquivos` |
| `status` | VARCHAR(20) | `ATIVO`, `INATIVO`, `BLOQUEADO` |
| `observacoes` | TEXT NULL | |
| `created_by` / `updated_by` | BIGINT UNSIGNED NULL | |
| `created_at` / `updated_at` | DATETIME | |

Índices: `nome`, `cpf`, `cnpj`, `email_principal`, `status`.

Regras:

1. `tipo=PF` exige `cpf` ou documento alternativo; `tipo=PJ` exige `cnpj`.
2. CPF/CNPJ são únicos quando informados.
3. A exclusão é lógica via `status`.
4. Dados pessoais são tratados conforme LGPD.

#### `pessoa_enderecos`

`id`, `pessoa_id`, `tipo` (`RESIDENCIAL`, `COMERCIAL`, `COBRANCA`), `cep`,
`logradouro`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `pais`,
`principal`, `status`, timestamps.

#### `pessoa_contatos`

`id`, `pessoa_id`, `tipo` (`EMAIL`, `TELEFONE`, `WHATSAPP`), `valor`,
`principal`, `verificado_em`, `status`, timestamps.

#### `pessoa_documentos`

`id`, `pessoa_id`, `tipo`, `numero`, `orgao_emissor`, `uf_emissor`,
`data_emissao`, `validade`, `arquivo_id`, `status`, timestamps.

Documentos podem ser mascarados em listagens e exports.

#### `alunos`

| Campo | Tipo | Observação |
| --- | --- | --- |
| `id` | BIGINT UNSIGNED PK | |
| `public_id` | CHAR(26) UNIQUE | |
| `pessoa_id` | BIGINT UNSIGNED UNIQUE | FK `pessoas` |
| `codigo` | VARCHAR(40) NULL | código interno por rede |
| `data_entrada` | DATE NULL | |
| `como_conheceu` | VARCHAR(80) NULL | |
| `indicado_por_pessoa_id` | BIGINT UNSIGNED NULL | |
| `status` | VARCHAR(20) | `ATIVO`, `INATIVO`, `INADIMPLENTE`? (status financeiro não deve duplicar) |
| `observacoes` | TEXT NULL | |
| timestamps | | |

Regras:

- Um aluno referencia uma pessoa única.
- O status acadêmico não substitui o status financeiro da cobrança.
- Pode haver múltiplas matrículas ao longo do tempo.

#### `aluno_responsaveis`

`id`, `aluno_id`, `responsavel_pessoa_id`, `parentesco`, `responsavel_financeiro`
(bool), `autorizado_buscar` (bool), `status`, timestamps.

Regras:

- Aluno menor de idade deve possuir ao menos um responsável financeiro ativo.
- O responsável financeiro pode ser diferente do responsável legal.

#### `aluno_restricoes_saude`

Tabela sensível:

`id`, `aluno_id`, `tipo`, `descricao`, `gravidade`, `orientacao`, `status`,
`created_by`, timestamps.

Acesso restrito a perfis autorizados. Não expor em listagens comuns.

### 6.2 Catálogo comercial, contratos e matrículas

#### `modalidades`

`id`, `rede_id` (NULL = global), `nome`, `descricao`, `status`, timestamps.

#### `planos`

| Campo | Tipo | Observação |
| --- | --- | --- |
| `id` | BIGINT UNSIGNED PK | |
| `rede_id` | BIGINT UNSIGNED NULL | global ou por rede |
| `public_id` | CHAR(26) UNIQUE | |
| `nome` | VARCHAR(120) | |
| `descricao` | TEXT NULL | |
| `tipo` | VARCHAR(30) | `MENSAL`, `ANUAL`, `PACOTE`, `AVULSO` |
| `duracao_meses` | SMALLINT UNSIGNED NULL | |
| `aulas_semana` | SMALLINT UNSIGNED NULL | |
| `limite_modalidades` | SMALLINT UNSIGNED NULL | |
| `status` | VARCHAR(20) | |
| timestamps | | |

#### `plano_modalidades`

`plano_id`, `modalidade_id`, `aulas_semana`, PK composta.

#### `plano_unidade_precos`

| Campo | Tipo | Observação |
| --- | --- | --- |
| `id` | BIGINT UNSIGNED PK | |
| `plano_id` | BIGINT UNSIGNED | |
| `rede_id` | BIGINT UNSIGNED | |
| `unidade_id` | BIGINT UNSIGNED | |
| `vigencia_inicio` | DATE | |
| `vigencia_fim` | DATE NULL | |
| `valor_matricula` | DECIMAL(15,2) | |
| `valor_mensal` | DECIMAL(15,2) | |
| `valor_adesao` | DECIMAL(15,2) NULL | |
| `taxa_administrativa` | DECIMAL(15,2) NULL | |
| `status` | VARCHAR(20) | |
| timestamps | | |

Regras: não permitir dois preços ativos para o mesmo plano/unidade/vigência.

#### `servicos_adicionais`

`id`, `rede_id` NULL, `nome`, `descricao`, `tipo_cobranca`, `valor_padrao`,
`status`.

#### `promocoes`

`id`, `rede_id`, `unidade_id` NULL, `nome`, `tipo_desconto`, `valor`,
`percentual`, `vigencia_inicio`, `vigencia_fim`, `condicoes_json`, `status`.

#### `contratos`

`id`, `public_id`, `aluno_id`, `rede_id`, `unidade_id`, `plano_id`,
`numero`, `data_inicio`, `data_fim`, `renovacao_automatica`,
`dia_vencimento`, `valor_mensal`, `status`, `observacoes`, timestamps.

#### `matriculas`

| Campo | Tipo | Observação |
| --- | --- | --- |
| `id` | BIGINT UNSIGNED PK | |
| `public_id` | CHAR(26) UNIQUE | |
| `aluno_id` | BIGINT UNSIGNED | |
| `rede_id` | BIGINT UNSIGNED | |
| `unidade_id` | BIGINT UNSIGNED | |
| `contrato_id` | BIGINT UNSIGNED NULL | |
| `plano_unidade_preco_id` | BIGINT UNSIGNED | |
| `data_matricula` | DATE | |
| `data_inicio` | DATE | |
| `data_encerramento` | DATE NULL | |
| `motivo_encerramento` | VARCHAR(120) NULL | |
| `status` | VARCHAR(20) | `ATIVA`, `SUSPENSA`, `CANCELADA`, `ENCERRADA`, `TRANSFERIDA` |
| timestamps | | |

Regras:

- Um aluno pode ter histórico de matrículas, mas apenas uma matrícula ativa por
  unidade/plano quando a regra de negócio exigir.
- Encerramento gera histórico, não exclusão.
- Transferência entre unidades deve gerar evento auditável.

#### `matricula_servicos`

`id`, `matricula_id`, `servico_id`, `valor`, `vigencia_inicio`,
`vigencia_fim`, `status`.

#### `reajustes`

`id`, `contrato_id`, `data_aplicacao`, `tipo`, `percentual`, `valor`,
`motivo`, `aplicado_por`, timestamps.

#### `descontos`

`id`, `matricula_id` NULL, `contrato_id` NULL, `tipo`, `valor`, `percentual`,
`vigencia_inicio`, `vigencia_fim`, `motivo`, `status`.

#### `transferencias`

`id`, `aluno_id`, `rede_id`, `unidade_origem_id`, `unidade_destino_id`,
`data_solicitacao`, `data_efetivacao`, `status`, `motivo`, `usuario_id`.

### 6.3 Operação, turmas e controle de acesso

#### `turmas`

`id`, `rede_id`, `unidade_id`, `modalidade_id`, `nome`, `capacidade`, `sala`,
`status`, timestamps.

#### `turma_horarios`

`id`, `turma_id`, `dia_semana`, `hora_inicio`, `hora_fim`,
`professor_funcionario_id`, `status`.

#### `turma_alunos`

`id`, `turma_id`, `matricula_id`, `data_entrada`, `data_saida`, `status`.

#### `reservas`

`id`, `aluno_id`, `turma_horario_id`, `data`, `status`, `observacao`.

#### `frequencias`

`id`, `aluno_id`, `turma_id`, `turma_horario_id` NULL, `data`,
`presenca`, `origem` (`MANUAL`, `CATRACA`, `APP`), `registrado_por`,
`created_at`.

#### `dispositivos_acesso`

`id`, `rede_id`, `unidade_id`, `nome`, `tipo`, `identificador`, `direcao`,
`status`, `ultima_comunicacao_em`, timestamps.

#### `eventos_acesso`

`id`, `dispositivo_id`, `rede_id`, `unidade_id`, `pessoa_id`,
`aluno_id` NULL, `funcionario_id` NULL, `tipo` (`ENTRADA`, `SAIDA`,
`TENTATIVA`), `ocorrido_em`, `autorizado` (bool), `motivo_negacao`,
`payload_json`, `created_at`.

Regras:

- Eventos de acesso são imutáveis.
- Retenção e anonimização definidas conforme LGPD.
- Catracas e integrações usam esse modelo como destino.

#### `ocorrencias`

`id`, `rede_id`, `unidade_id`, `aluno_id` NULL, `funcionario_id` NULL,
`tipo`, `descricao`, `data`, `status`, `arquivo_id` NULL, timestamps.

### 6.4 Financeiro

#### `contas_bancarias`

`id`, `rede_id`, `unidade_id`, `banco`, `agencia`, `conta`, `tipo`,
`saldo_inicial`, `status`, timestamps.

#### `formas_pagamento`

`id`, `rede_id` NULL, `nome`, `tipo` (`DINHEIRO`, `PIX`, `CARTAO`,
`BOLETO`, `TRANSFERENCIA`), `status`.

#### `cobrancas`

| Campo | Tipo | Observação |
| --- | --- | --- |
| `id` | BIGINT UNSIGNED PK | |
| `public_id` | CHAR(26) UNIQUE | |
| `rede_id` | BIGINT UNSIGNED | |
| `unidade_id` | BIGINT UNSIGNED | |
| `aluno_id` | BIGINT UNSIGNED | |
| `matricula_id` | BIGINT UNSIGNED NULL | |
| `contrato_id` | BIGINT UNSIGNED NULL | |
| `competencia` | CHAR(7) | `YYYY-MM` |
| `numero` | VARCHAR(40) | |
| `tipo` | VARCHAR(30) | mensalidade, matrícula, serviço, avulso |
| `data_emissao` | DATE | |
| `data_vencimento` | DATE | |
| `valor_original` | DECIMAL(15,2) | |
| `valor_desconto` | DECIMAL(15,2) | |
| `valor_juros` | DECIMAL(15,2) | |
| `valor_multa` | DECIMAL(15,2) | |
| `valor_total` | DECIMAL(15,2) | |
| `status` | VARCHAR(20) | `ABERTA`, `PAGA`, `VENCIDA`, `CANCELADA`, `RENEGOCIADA` |
| `pix_ref` / `boleto_ref` | VARCHAR(120) NULL | |
| timestamps | | |

Índices: `(rede_id, unidade_id, status)`, `(aluno_id, competencia)`,
`data_vencimento`, `numero`.

#### `cobranca_itens`

`id`, `cobranca_id`, `descricao`, `quantidade`, `valor_unitario`,
`valor_total`, `plano_id` NULL, `servico_id` NULL.

#### `pagamentos`

`id`, `public_id`, `rede_id`, `unidade_id`, `aluno_id`, `cobranca_id` NULL,
`conta_bancaria_id`, `forma_pagamento_id`, `valor`, `data_pagamento`,
`data_compensacao`, `status`, `transacao_id`, `comprovante_arquivo_id`,
`criado_por`, `estornado_em`.

#### `pagamento_alocacoes`

`id`, `pagamento_id`, `cobranca_id`, `valor`.

Um pagamento pode quitar várias cobranças; uma cobrança pode receber vários
pagamentos.

#### `estornos`

`id`, `pagamento_id`, `valor`, `motivo`, `data`, `usuario_id`.

#### `renegociacoes`

`id`, `aluno_id`, `rede_id`, `unidade_id`, `valor_total`, `entrada`,
`parcelas`, `vencimento_primeira`, `status`, `usuario_id`, timestamps.

#### `renegociacao_itens`

`id`, `renegociacao_id`, `cobranca_id`, `valor_original`, `valor_renegociado`.

#### `fornecedores`

`id`, `rede_id`, `pessoa_id` (PJ), `nome_fantasia`, `categoria`, `status`.

#### `centros_custo`

`id`, `rede_id`, `unidade_id`, `codigo`, `nome`, `status`.

#### `contas_pagar`

`id`, `public_id`, `rede_id`, `unidade_id`, `fornecedor_id` NULL,
`centro_custo_id` NULL, `descricao`, `competencia`, `data_emissao`,
`data_vencimento`, `valor`, `status`, `data_pagamento`,
`conta_bancaria_id` NULL, `documento_arquivo_id` NULL, timestamps.

#### `movimentos_caixa`

`id`, `rede_id`, `unidade_id`, `conta_bancaria_id`, `tipo`
(`ENTRADA`, `SAIDA`), `origem` (`COBRANCA`, `PAGAMENTO`, `CONTA_PAGAR`,
`AJUSTE`), `valor`, `data`, `descricao`, `referencia_id`, `referencia_tipo`.

#### `conciliacoes_bancarias`

`id`, `rede_id`, `unidade_id`, `conta_bancaria_id`, `competencia`, `status`,
`arquivo_id`, `importado_em`, `fechado_em`, `fechado_por`.

#### `conciliacao_itens`

`id`, `conciliacao_id`, `data`, `descricao`, `valor`, `tipo`,
`movimento_caixa_id` NULL, `status`, `diferenca`.

### 6.5 Contabilidade da unidade

A contabilidade é **por unidade e independente**. Cada unidade possui seu próprio
plano de contas, períodos, centros de custo e lançamentos. A consolidação por
rede é apenas um relatório gerencial futuro, não uma regra contábil.

#### `planos_contas`

`id`, `rede_id`, `unidade_id`, `nome`, `versao`, `status`, timestamps.

Regras:

- O plano de contas pertence a uma unidade.
- Unidades não compartilham plano de contas.
- Uma unidade pode ter versões de plano, com apenas uma ativa por vez.

#### `contas_contabeis`

| Campo | Tipo | Observação |
| --- | --- | --- |
| `id` | BIGINT UNSIGNED PK | |
| `plano_conta_id` | BIGINT UNSIGNED | |
| `codigo` | VARCHAR(30) | ex.: `1.1.01` |
| `nome` | VARCHAR(180) | |
| `natureza` | VARCHAR(20) | ATIVO, PASSIVO, PATRIMONIO, RECEITA, DESPESA |
| `tipo` | VARCHAR(20) | SINTETICA, ANALITICA |
| `parent_id` | BIGINT UNSIGNED NULL | hierarquia |
| `grau` | SMALLINT UNSIGNED | |
| `aceita_lancamento` | TINYINT(1) | |
| `status` | VARCHAR(20) | |
| timestamps | | |

Regras:

- Conta analítica aceita lançamento; sintética não.
- Código único por plano.
- Contas não são excluídas; são desativadas.

#### `periodos_contabeis`

`id`, `rede_id`, `unidade_id`, `competencia` CHAR(7), `data_inicio`,
`data_fim`, `status` (`ABERTO`, `FECHADO`, `REABERTO`),
`fechado_por`, `fechado_em`, `reaberto_por`, `reaberto_em`, `motivo_reabertura`.

Regras: cada unidade possui seus próprios períodos contábeis; fechamento de uma
unidade não fecha as demais.

Regras:

- Período fechado não aceita lançamentos novos.
- Reabertura exige permissão específica e auditoria.

#### `lancamentos_contabeis`

| Campo | Tipo | Observação |
| --- | --- | --- |
| `id` | BIGINT UNSIGNED PK | |
| `public_id` | CHAR(26) UNIQUE | |
| `rede_id` | BIGINT UNSIGNED | |
| `unidade_id` | BIGINT UNSIGNED | |
| `periodo_id` | BIGINT UNSIGNED | |
| `data_lancamento` | DATE | |
| `competencia` | CHAR(7) | |
| `historico` | VARCHAR(255) | |
| `origem` | VARCHAR(30) | MANUAL, FINANCEIRO, FOLHA, FECHAMENTO |
| `documento_ref` | VARCHAR(120) NULL | |
| `status` | VARCHAR(20) | RASCUNHO, POSTADO, REVERTIDO |
| `total_debito` | DECIMAL(15,2) | |
| `total_credito` | DECIMAL(15,2) | |
| `created_by` | BIGINT UNSIGNED | |
| `posted_by` / `posted_em` | | |
| `reversao_de_id` | BIGINT UNSIGNED NULL | |
| timestamps | | |

Regras:

- Débitos = créditos em todo lançamento postado.
- Lançamento postado não é editado; é revertido por outro lançamento.
- Exclusão física proibida.
- Integração com financeiro e folha cria lançamentos por regras.

#### `lancamento_partidas`

`id`, `lancamento_id`, `conta_id`, `centro_custo_id` NULL,
`tipo` (`DEBITO`, `CREDITO`), `valor`, `descricao`, `pessoa_id` NULL.

#### `regras_contabilizacao`

`id`, `rede_id`, `unidade_id`, `evento` (`COBRANCA_PAGA`, `DESPESA`, `FOLHA`,
`RESCISAO`, `TRANSFERENCIA`), `conta_debito_id`, `conta_credito_id`,
`centro_custo_id` NULL, `condicoes_json`, `status`.

As regras são configuradas por unidade, pois o plano de contas e os centros de
custo são independentes.

#### `obrigacoes_fiscais`

`id`, `rede_id`, `unidade_id`, `tipo`, `competencia`, `vencimento`, `valor`,
`status`, `conta_contabil_id` NULL, `documento_arquivo_id` NULL.

#### Relatórios contábeis

Não exigem tabelas próprias nesta fase:

- Livro diário.
- Razão.
- Balancete.
- Balanço patrimonial.
- DRE.
- Fluxo de caixa.
- Fechamento e conciliação.

### 6.6 RH — Recursos Humanos

RH é operado por unidade e consolidado por rede. A folha é integrada à
contabilidade por centros de custo.

#### `departamentos`

`id`, `rede_id`, `unidade_id` NULL, `nome`, `centro_custo_id` NULL,
`status`, timestamps.

#### `cargos`

`id`, `rede_id`, `nome`, `cbo`, `descricao`, `salario_base_referencia`,
`status`, timestamps.

#### `funcionarios`

| Campo | Tipo | Observação |
| --- | --- | --- |
| `id` | BIGINT UNSIGNED PK | |
| `public_id` | CHAR(26) UNIQUE | |
| `pessoa_id` | BIGINT UNSIGNED UNIQUE | FK `pessoas` |
| `rede_id` | BIGINT UNSIGNED | |
| `unidade_id` | BIGINT UNSIGNED | |
| `cargo_id` | BIGINT UNSIGNED | |
| `departamento_id` | BIGINT UNSIGNED NULL | |
| `matricula` | VARCHAR(40) | única por rede |
| `data_admissao` | DATE | |
| `data_desligamento` | DATE NULL | |
| `tipo_contrato` | VARCHAR(30) | CLT, PJ, ESTAGIO, APRENDIZ |
| `jornada_semanal` | DECIMAL(5,2) NULL | horas |
| `salario_base` | DECIMAL(15,2) NULL | |
| `status` | VARCHAR(20) | ATIVO, AFASTADO, DESLIGADO |
| timestamps | | |

Regras:

- Funcionário é uma pessoa; não duplicar cadastro.
- Desligamento encerra contrato e gera rescisão.
- Funcionário pode ou não ter usuário no sistema.
- Acesso ao sistema apenas por necessidade funcional.
- A catraca registra ponto independentemente de login no sistema.
- Se a decisão futura for dar acesso a todos, o acesso continua sendo por
  usuário + perfil/permissões, nunca automático por ser funcionário.
- Salário e dados bancários são sensíveis.

#### `funcionario_contratos`

`id`, `funcionario_id`, `cargo_id`, `salario`, `tipo_contrato`,
`data_inicio`, `data_fim` NULL, `motivo_alteracao`, `status`.

#### `historico_salarial`

`id`, `funcionario_id`, `salario`, `cargo_id`, `vigencia_inicio`,
`vigencia_fim` NULL, `motivo`, `usuario_id`.

#### `jornadas`

`id`, `rede_id`, `nome`, `horas_semanais`, `descricao`, `status`.

#### `escalas`

`id`, `rede_id`, `unidade_id`, `nome`, `tipo`, `status`.

#### `escala_funcionarios`

`id`, `escala_id`, `funcionario_id`, `dia_semana`, `hora_inicio`,
`hora_fim`, `vigencia_inicio`, `vigencia_fim`, `status`.

#### `registros_ponto`

`id`, `funcionario_id`, `data`, `entrada_1`, `saida_1`, `entrada_2`,
`saida_2`, `horas_trabalhadas`, `horas_extras`, `origem`,
`status`, `aprovado_por`, `aprovado_em`.

#### `ajustes_ponto`

`id`, `registro_ponto_id`, `motivo`, `minutos`, `status`,
`aprovado_por`, `aprovado_em`.

#### `banco_horas`

`id`, `funcionario_id`, `competencia`, `saldo_inicial`, `creditos`,
`debitos`, `saldo_final`, `status`.

#### `ferias`

`id`, `funcionario_id`, `periodo_aquisitivo_inicio`,
`periodo_aquisitivo_fim`, `data_inicio`, `data_fim`, `dias`, `status`,
`aprovado_por`, `aprovado_em`, `observacao`.

#### `afastamentos`

`id`, `funcionario_id`, `tipo`, `data_inicio`, `data_fim`, `motivo`,
`arquivo_id` NULL, `status`.

Dados de saúde em afastamentos são sensíveis e restritos.

#### `beneficios`

`id`, `rede_id`, `nome`, `tipo`, `valor_padrao`, `status`.

#### `funcionario_beneficios`

`id`, `funcionario_id`, `beneficio_id`, `valor`, `vigencia_inicio`,
`vigencia_fim`, `status`.

#### `dependentes`

`id`, `funcionario_id`, `nome`, `cpf`, `data_nascimento`, `parentesco`,
`status`.

#### `rubricas_folha`

`id`, `rede_id`, `codigo`, `nome`, `tipo`
(`PROVENTO`, `DESCONTO`, `INFORMATIVO`), `incide_inss`, `incide_fgts`,
`incide_irrf`, `status`.

#### `folhas_pagamento`

`id`, `rede_id`, `unidade_id`, `competencia`, `status`
(`ABERTA`, `FECHADA`, `PAGA`), `total_proventos`, `total_descontos`,
`total_liquido`, `fechado_por`, `fechado_em`, `pago_em`.

#### `folha_itens`

`id`, `folha_id`, `funcionario_id`, `rubrica_id`, `tipo`, `referencia`,
`valor`, `base_calculo`, `observacao`.

#### `encargos_folha`

`id`, `folha_id`, `tipo` (`INSS`, `FGTS`, `IRRF`, `OUTROS`), `valor`,
`guia_ref`, `status`.

#### `rescisoes`

`id`, `funcionario_id`, `data_desligamento`, `motivo`, `tipo_aviso`,
`saldo_salario`, `ferias_vencidas`, `decimo_terceiro`, `multa_fgts`,
`outros_valores`, `total`, `status`, `folha_id` NULL.

#### `documentos_funcionario`

`id`, `funcionario_id`, `tipo`, `arquivo_id`, `validade`, `status`.

#### `treinamentos`

`id`, `rede_id`, `nome`, `descricao`, `obrigatorio`, `carga_horaria`,
`status`.

#### `funcionario_treinamentos`

`id`, `funcionario_id`, `treinamento_id`, `data_conclusao`, `validade`,
`certificado_arquivo_id` NULL, `status`.

#### Integração RH × Contabilidade

- Fechamento da folha gera lançamentos contábeis por unidade e centro de custo.
- Encargos, benefícios, férias, rescisões e provisões têm regras de
  contabilização configuráveis.
- Folha fechada é imutável; ajustes geram nova competência ou complemento.
- Perfil `CONTABILIDADE` não deve processar folha; perfil `RH` processa e
  contabilidade apenas lança/revisa.

### 6.7 Treinos e avaliações

#### `exercicios`

`id`, `rede_id` NULL, `nome`, `grupo_muscular`, `equipamento`, `descricao`,
`video_url`, `status`.

#### `fichas_treino`

`id`, `public_id`, `aluno_id`, `rede_id`, `unidade_id`,
`profissional_funcionario_id`, `nome`, `objetivo`, `data_inicio`,
`data_fim`, `status`.

#### `ficha_treino_itens`

`id`, `ficha_id`, `exercicio_id`, `series`, `repeticoes`, `carga`,
`descanso_segundos`, `ordem`, `observacao`.

#### `avaliacoes_fisicas`

`id`, `aluno_id`, `rede_id`, `unidade_id`, `profissional_funcionario_id`,
`data`, `peso`, `altura`, `imc`, `percentual_gordura`,
`medidas_json`, `observacoes`, `status`.

Dados de saúde: acesso restrito, retenção definida, sem exposição em listagens.

#### `avaliacao_medidas` (opcional)

Se for necessário relatório por medida:

`id`, `avaliacao_id`, `local`, `valor`, `unidade_medida`.

#### `autorizacoes_acesso_saude`

`id`, `rede_id`, `unidade_id`, `aluno_id`, `pessoa_autorizada_id`,
`concedido_por_pessoa_id`, `escopo` (`AVALIACOES`, `RESTRICOES`, `AMBOS`),
`validade_inicio`, `validade_fim` NULL, `motivo`, `status`,
`created_by`, `created_at`, `revogado_em`, `revogado_por`.

Regras:

- O acesso a dados de saúde exige permissão `saude.visualizar` e autorização
  explícita do cliente/responsável para usuários que não sejam profissionais de
  saúde autorizados.
- Profissionais de saúde da unidade acessam apenas dentro do seu escopo
  funcional.
- Administradores não recebem acesso automático a dados de saúde.
- Toda concessão, revogação e acesso é auditada.

### 6.8 Comunicação e notificações

#### `comunicados`

`id`, `rede_id`, `unidade_id` NULL, `titulo`, `corpo`,
`publico` (`ALUNOS`, `FUNCIONARIOS`, `TODOS`), `data_publicacao`,
`expira_em`, `status`.

#### `comunicado_leituras`

`id`, `comunicado_id`, `pessoa_id`, `lido_em`.

#### `notificacao_templates`

`id`, `rede_id` NULL, `chave`, `canal`, `assunto`, `corpo`, `status`.

#### `notificacoes`

`id`, `rede_id`, `unidade_id` NULL, `pessoa_id`, `tipo`, `titulo`,
`mensagem`, `canal`, `status`, `lida_em`, `enviada_em`,
`referencia_tipo`, `referencia_id`.

#### `preferencias_notificacao`

`id`, `pessoa_id`, `canal`, `tipo`, `opt_in`, `updated_at`.

#### `fila_envio`

`id`, `canal`, `destinatario`, `payload_json`, `status`, `tentativas`,
`agendado_para`, `enviado_em`, `erro`.

Integrações reais (e-mail, WhatsApp, SMS, push) consomem a fila por adapters.

### 6.9 Arquivos, configurações e LGPD

#### `arquivos`

`id`, `public_id`, `disco`, `caminho`, `nome_original`, `mime`, `tamanho`,
`hash`, `status`, `enviado_por`, `created_at`.

Valores de `disco`:

- `private_local`: arquivos personalíssimos do cliente, armazenados localmente
  em área privada.
- `server`: demais arquivos operacionais.
- Object storage externo poderá ser adicionado como novo adapter.

#### `configuracoes`

`id`, `rede_id` NULL, `unidade_id` NULL, `chave`, `valor_json`,
`tipo`, `status`, `updated_by`, timestamps.

Chaves de configuração não devem armazenar segredos; segredos ficam no `.env`.

#### `consentimentos`

`id`, `pessoa_id`, `tipo`, `finalidade`, `versao_termo`, `concedido_em`,
`revogado_em`, `ip`, `user_agent`, `status`.

#### `lgpd_solicitacoes`

`id`, `pessoa_id`, `tipo` (`ACESSO`, `CORRECAO`, `EXCLUSAO`,
`PORTABILIDADE`, `REVOGACAO`), `status`, `prazo`, `descricao`,
`resposta`, `atendido_por`, `atendido_em`, timestamps.

------------------------------------------------------------------------

## 7. Relacionamentos principais

```text
pessoas 1 ── 0..1 alunos
pessoas 1 ── 0..1 funcionarios
pessoas 1 ── 0..1 fornecedores
pessoas 1 ── 0..N usuarios (vínculo opcional)

alunos 1 ── N aluno_responsaveis
alunos 1 ── N matriculas
matriculas N ── 1 plano_unidade_precos
matriculas 1 ── N cobrancas
cobrancas 1 ── N cobranca_itens
cobrancas 1 ── N pagamento_alocacoes
pagamentos 1 ── N pagamento_alocacoes

funcionarios 1 ── N registros_ponto
funcionarios 1 ── N folha_itens
folhas_pagamento 1 ── N folha_itens
folhas_pagamento 1 ── N lancamentos_contabeis

planos_contas 1 ── N contas_contabeis
periodos_contabeis 1 ── N lancamentos_contabeis
lancamentos_contabeis 1 ── N lancamento_partidas
contas_contabeis 1 ── N lancamento_partidas
centros_custo 1 ── N lancamento_partidas
```

Regras de integridade:

- `aluno_responsaveis` exige aluno e pessoa responsável ativos.
- `matriculas` exige aluno, unidade e preço de plano vigente.
- `cobrancas` e `pagamentos` exigem unidade e aluno.
- `lancamentos_contabeis` exigem período aberto e partidas balanceadas.
- `folha_itens` exigem folha aberta e funcionário ativo.
- Exclusões são lógicas; registros financeiros/contábeis não são apagados.

------------------------------------------------------------------------

## 8. Multiempresa, catálogo e preços

| Entidade | Escopo |
| --- | --- |
| `redes` | global |
| `regioes`, `unidades` | rede |
| `modalidades`, `planos`, `servicos_adicionais`, `beneficios`, `rubricas_folha`, `exercicios` | global (`rede_id NULL`) ou por rede |
| `plano_unidade_precos` | rede + unidade |
| `matriculas`, `cobrancas`, `pagamentos`, `contas_pagar`, `funcionarios`, `folhas_pagamento` | rede + unidade |
| `planos_contas`, `contas_contabeis`, `periodos_contabeis`, `lancamentos_contabeis`, `regras_contabilizacao` | unidade (independente; `rede_id` para tenancy) |
| `centros_custo` | rede + unidade |
| `turmas`, `dispositivos_acesso`, `eventos_acesso`, `frequencias` | rede + unidade |

Preços e regras comerciais podem variar por unidade sem duplicar o catálogo.
A contabilidade é independente por unidade; a consolidação por rede é um
relatório gerencial futuro.

------------------------------------------------------------------------

## 9. Permissões e perfis — extensão proposta

### 9.1 Novos módulos de permissão

- `pessoa.*`, `aluno.*`, `responsavel.*`
- `plano.*`, `matricula.*`, `contrato.*`, `turma.*`, `acesso.*`
- `financeiro.*` (já existente, ampliar)
- `contabilidade.*`
- `rh.*`, `folha.*`
- `treino.*`, `avaliacao.*`, `saude.*`
- `comunicacao.*`, `lgpd.*`

Permissões de saúde:

- `saude.visualizar`: ver dados de saúde dentro do escopo autorizado.
- `saude.autorizar`: conceder/revogar acesso de terceiros.
- `saude.auditar`: consultar trilha de acesso a dados de saúde.

### 9.2 Perfis

- `CONTABILIDADE`: acrescentar `contabilidade.*` e `financeiro.conciliacao.*`.
- Criar perfil `RH` (confirmado):
  - nível 35, escopo `UNIDADE`;
  - `rh.funcionario.*`, `rh.ponto.*`, `rh.ferias.*`, `rh.beneficio.*`,
    `rh.folha.*`;
  - sem permissões administrativas globais.
- Perfis de saúde: profissionais autorizados recebem `saude.visualizar` e
  `avaliacao.*`/`treino.*` conforme a função.
- `ADMIN_LOCAL`: pode receber permissões de RH e contabilidade apenas por
  concessão explícita, se a governança permitir.
- `DIRETOR`/`GERENTE`: permissões de leitura de RH e custos, sem folha.

### 9.3 Regras

- Folha e contabilidade exigem permissões separadas.
- Contabilidade não processa folha; RH não fecha período contábil.
- Aprovação e fechamento exigem confirmação e auditoria.
- Segregação de funções: quem processa folha não deve aprovar sozinho.
- Dados de saúde: acesso apenas a perfis com `saude.visualizar` e, para quem não
  é profissional de saúde, somente com autorização explícita do cliente em
  `autorizacoes_acesso_saude`.
- Toda concessão/revogação de acesso à saúde é auditada.

------------------------------------------------------------------------

## 10. Classificação e proteção de dados

| Classe | Exemplos | Proteção |
| --- | --- | --- |
| Pública | nome fantasia, unidade, modalidade | baixa |
| Interna | turmas, frequência, comunicados | autenticação |
| Pessoal | CPF, e-mail, telefone, endereço | mascaramento e acesso por permissão |
| Financeira | cobranças, pagamentos, contas a pagar | acesso restrito, auditoria |
| Folha/RH | salário, banco, dependentes | acesso restrito, segregação |
| Saúde | avaliações, restrições, afastamentos | acesso altamente restrito, LGPD |
| Credenciais | senhas, tokens | hash/segredo, nunca em log |

Diretrizes:

- Mascarar CPF, dados bancários e salário em listagens.
- Não registrar dados sensíveis em logs.
- Retenção padrão de 2 anos, com prazos legais específicos prevalecendo.
- Anonimizar dados em relatórios quando possível.
- Auditar acessos a dados de saúde e folha.
- Consentimento para marketing, imagem e comunicações.

------------------------------------------------------------------------

## 11. Estratégia de migrations

### 11.1 Convenção

- Manter migrations sequenciais: `0017_...`, `0018_...`.
- Uma migration por mudança coesa.
- Adicionar colunas anuláveis primeiro; backfill em migration separada; depois
  tornar obrigatório, se necessário.
- Nunca remover ou renomear colunas em uso sem etapa de compatibilidade.
- Criar índices e FKs após o backfill.
- Seeds de referência idempotentes.
- Seeds de demonstração apenas em ambiente local.

### 11.2 Ordem macro

1. Pessoas, endereços, contatos, documentos, arquivos, consentimentos.
2. Vínculo `usuarios.pessoa_id` e backfill.
3. Alunos, responsáveis, restrições de saúde.
4. Catálogo comercial, planos, preços, contratos, matrículas.
5. Turmas, frequência, acesso.
6. Financeiro.
7. Contabilidade.
8. RH.
9. Treinos, avaliações.
10. Comunicação, LGPD.
11. Relatórios e índices adicionais.

### 11.3 Rollback

- Toda migration deve possuir `down` seguro.
- Dados de negócio não podem ser perdidos no rollback.
- Tabelas novas podem ser removidas apenas se não houver dependências.
- Em produção, rollback de schema exige janela e backup.

------------------------------------------------------------------------

## 12. Seeds de referência

- Formas de pagamento.
- Tipos de documento.
- Modalidades e planos de demonstração.
- Plano de contas padrão.
- Rubricas de folha padrão.
- Cargos e departamentos de exemplo.
- Benefícios comuns.
- Templates de notificação.
- Perfis e permissões de sistema.
- Dados de demonstração apenas em `APP_ENV=local`.

Nenhum seed deve conter credenciais reais, dados de alunos reais ou informações
financeiras reais.

------------------------------------------------------------------------

## 13. Relatórios e integrações

### 13.1 Relatórios esperados

- Financeiro: fluxo de caixa, cobranças em aberto, inadimplência, recebimentos,
  despesas e conciliação.
- Contábil: diário, razão, balancete, balanço, DRE e fechamento.
- RH: folha, encargos, ponto, férias, custo de pessoal e rescisões.
- Operacional: alunos ativos, cancelamentos, frequência, ocupação e acessos.
- Comercial: matrículas, planos, descontos e reajustes.

### 13.2 Integrações futuras

- Gateway de pagamento e PIX.
- Boletos e conciliação bancária.
- eSocial e folha externa.
- NF-e/NFS-e.
- Catracas e dispositivos de acesso.
- E-mail, WhatsApp, SMS e push.
- Exportação contábil.

Integrações usam adapters e logs próprios; não devem acoplar o domínio.

------------------------------------------------------------------------

## 14. Critérios de aceite do modelo

1. Todas as tabelas de unidade possuem `rede_id` e `unidade_id`.
2. Todas as FKs, índices e unicidades estão definidos.
3. O modelo de pessoas evita duplicação entre aluno, funcionário e usuário.
4. Matrículas, cobranças, pagamentos e folha têm histórico e imutabilidade.
5. Lançamentos contábeis são balanceados e vinculados a período aberto.
6. Folha fechada não pode ser alterada; ajustes geram complemento.
7. Dados de saúde e folha têm acesso restrito e auditado.
8. Seeds de referência são idempotentes.
9. Migrations têm rollback seguro.
10. Testes de isolamento entre redes/unidades cobrem as novas entidades.
11. Relatórios financeiros e contábeis conferem com os lançamentos.
12. Documentação e dicionário de dados são atualizados.

------------------------------------------------------------------------

## 15. Decisões de modelagem

### 15.1 Confirmadas

| # | Decisão | Consequência no modelo |
| --- | --- | --- |
| 1 | Pessoa unificada | `pessoas` + especializações, sem duplicar aluno/funcionário/fornecedor |
| 2 | Contabilidade interna predominante | Partidas dobradas internas; exportação futura por adapter |
| 3 | Folha interna inicial | Cálculo interno; evolução futura para híbrido/externo |
| 4 | eSocial/NF-e | Apenas integração futura; fora do escopo atual |
| 5 | Contabilidade e custos por unidade | Plano de contas, períodos, lançamentos e centros de custo independentes por unidade |
| 6 | Perfil `RH` nível 35 | Novo perfil de sistema com escopo de unidade |
| 7 | Acesso de funcionários | Apenas por necessidade funcional; catraca registra ponto sem login |
| 8 | Dados de saúde | Perfis específicos + autorização do cliente para terceiros |
| 9 | Retenção LGPD | Padrão de 2 anos, ressalvadas obrigações legais específicas |
| 10 | Arquivos | `private_local` para arquivos personalíssimos do cliente; `server` para os demais |
| 11 | Moeda | BRL nesta fase |
| 12 | Exclusão | `status` em vez de exclusão física |

### 15.2 Situação

Todas as decisões de modelagem estão confirmadas. Não há pendências bloqueantes
para a Onda 1.

------------------------------------------------------------------------

## 16. Plano de ação para implementação

### 16.1 Premissas

- Execução incremental, sem quebrar a fundação atual.
- Cada onda termina com migrations, services, permissões, telas mínimas, seeds,
  testes e documentação.
- Nenhuma onda avança sem aceite da anterior.
- Estimativas relativas para um desenvolvedor.

### 16.2 Ondas

| Onda | Entrega | Dependência | Estimativa |
| --- | --- | --- | --- |
| 0 | Decisões remanescentes (7, 9, 12) e ADR de modelagem | Este documento | 1 dia |
| 1 | Pessoas, alunos, responsáveis, arquivos, consentimentos | Onda 0 | 3–5 dias |
| 2 | Catálogo, planos, preços, contratos e matrículas | Onda 1 | 4–6 dias |
| 3 | Turmas, frequência e controle de acesso | Ondas 1–2 | 3–5 dias |
| 4 | Financeiro: cobranças, pagamentos, contas a pagar e conciliação | Ondas 1–3 | 5–7 dias |
| 5 | Contabilidade independente por unidade: plano de contas, lançamentos, períodos e relatórios | Onda 4 | 5–7 dias |
| 6 | RH: funcionários, ponto, férias, benefícios e folha interna | Ondas 1 e 5 | 7–10 dias |
| 7 | Treinos, avaliações, autorizações de saúde, comunicação e LGPD | Ondas 1–2 | 5–8 dias |
| 8 | Relatórios consolidados, hardening, testes e documentação | Todas | 4–6 dias |

**Estimativa total:** 37–55 dias de desenvolvimento, dependendo das decisões e
do nível de integração externa.

Decisões já incorporadas: pessoa unificada, contabilidade interna por unidade,
folha interna, eSocial/NF-e como integração futura, perfil `RH` nível 35,
autorização de saúde pelo cliente e armazenamento local privado para arquivos
personalíssimos.

### 16.3 Detalhamento por onda

#### Onda 0 — Decisões e ADR

- Registrar as decisões confirmadas no ADR de modelagem.
- Congelar nomes de tabelas e convenções.
- Definir matriz de permissões de RH, contabilidade e saúde.
- Confirmar retenção padrão de 2 anos e exceções legais.

#### Onda 1 — Pessoas e alunos

- Migrations: `arquivos`, `pessoas`, endereços, contatos, documentos,
  consentimentos, `alunos`, responsáveis, restrições.
- Adicionar `usuarios.pessoa_id` com backfill.
- Repositories e Services de pessoas/alunos.
- Validação de CPF/CNPJ, duplicidade e LGPD.
- Telas de cadastro e consulta.
- Testes de escopo e dados sensíveis.

#### Onda 2 — Comercial e matrículas

- Migrations de modalidades, planos, preços, serviços, promoções, contratos,
  matrículas, reajustes e descontos.
- Services de precificação e vigência.
- Telas de planos e matrículas.
- Regras de unicidade e histórico.
- Testes de vigência, transferência e cancelamento.

#### Onda 3 — Operação e acesso

- Turmas, horários, reservas, frequência, dispositivos e eventos.
- Integração conceitual com catracas.
- Telas de turmas e frequência.
- Testes de capacidade, conflito de horário e eventos.

#### Onda 4 — Financeiro

- Cobranças, itens, pagamentos, alocações, estornos, renegociações,
  fornecedores, contas a pagar, centros de custo, movimentos e conciliação.
- Serviço de geração de cobranças por competência.
- Telas de cobrança, recebimento e contas a pagar.
- Testes de cálculo, baixa, estorno e conciliação.

#### Onda 5 — Contabilidade por unidade

- Plano de contas, contas, períodos, lançamentos, partidas, regras e obrigações
  por unidade, independentes.
- Integração financeira → lançamentos.
- Fechamento/reabertura de período com auditoria.
- Relatórios diário, razão, balancete, balanço e DRE por unidade; consolidação
  por rede como relatório futuro.
- Testes de partidas dobradas, período fechado, reversão e isolamento entre
  unidades.

#### Onda 6 — RH

- Cargos, departamentos, funcionários, contratos, histórico salarial.
- Jornada, escala, ponto, banco de horas, férias, afastamentos, benefícios,
  dependentes, rubricas, folha interna, encargos e rescisão.
- Perfil `RH` nível 35.
- Integração folha → contabilidade por unidade.
- Ponto pode receber eventos de catraca independentemente de login.
- Telas de funcionários, ponto e folha.
- eSocial permanece como integração futura.
- Testes de fechamento, segregação de funções e dados sensíveis.

#### Onda 7 — Treinos, comunicação e LGPD

- Exercícios, fichas, avaliações e medidas.
- `autorizacoes_acesso_saude` e permissões `saude.*`.
- Comunicados, notificações, templates, preferências e fila.
- Consentimentos, solicitações LGPD e retenção.
- Telas e permissões específicas.
- Testes de acesso a dados de saúde, autorização do cliente e notificações.

#### Onda 8 — Relatórios e hardening

- Relatórios consolidados.
- Índices adicionais e revisão de performance.
- Testes de isolamento, autorização e LGPD.
- Backup, restore e documentação.
- Aceite final.

### 16.4 Estratégia de testes por onda

- Unitários: cálculos, vigências, competência, folha e contabilidade.
- Integração: banco real, FKs, transações e rollback.
- Escopo: tentativa de acesso cruzado entre redes/unidades.
- Segurança: permissões, CSRF, dados sensíveis e logs.
- Regressão: suíte existente + novas ondas.
- Financeiro/contábil: débitos = créditos, baixas, estornos e fechamentos.
- RH: folha fechada imutável, férias, ponto e rescisão.
- LGPD: consentimento, anonimização e acesso restrito.

### 16.5 Riscos e mitigação

| Risco | Impacto | Mitigação |
| --- | --- | --- |
| Complexidade trabalhista/contábil | Alto | Validar com contador; começar com regras parametrizáveis |
| Escopo excessivo | Alto | Ondas pequenas e aceite formal |
| Dados sensíveis vazando | Crítico | Permissões, mascaramento, testes e auditoria |
| Divergência financeiro × contábil | Alto | Regras de contabilização e conciliação |
| Migração de usuários para pessoas | Médio | Backfill idempotente e rollback |
| Performance em relatórios | Médio | Índices, paginação e views/materializações |
| Integrações externas | Médio | Adapters e escopo separado |
| Folha sem validação legal | Alto | Não prometer cálculo legal sem homologação |

### 16.6 Critérios para iniciar a implementação

- Documento 03 aprovado.
- Todas as decisões de modelagem confirmadas e registradas no ADR.
- Matriz de permissões de RH, contabilidade e saúde aprovada.
- Contabilidade independente por unidade e perfil `RH` nível 35 aprovados.
- Acesso de funcionários somente por necessidade funcional e catraca como ponto.
- Retenção LGPD padrão de 2 anos, com exceções legais definidas.
- Política de arquivos (`private_local` e `server`) e autorização de saúde
  definidas.
- Orçamento/prioridade das ondas definidos.
- Backup do banco e ambiente de homologação preparados.

------------------------------------------------------------------------

## 17. Próximo passo recomendado

1. Revisar este documento e registrar as decisões confirmadas da seção 15.
2. Registrar o ADR final de modelagem do domínio.
3. Iniciar pela **Onda 1 — Pessoas e alunos**, que é pré-requisito para RH,
   matrículas, financeiro e treinos.

Após a Onda 1, seguir para a Onda 2 e assim por diante, com aceite formal entre
as ondas.

------------------------------------------------------------------------

### Status de execução — Onda 1

Implementada em 2026-11-09:

- Migrations 0017–0022: rquivos, pessoas, detalhes de pessoa,
  consentimentos, lunos/responsáveis/restrições e usuarios.pessoa_id.
- Backfill dos usuários existentes para pessoas.
- PessoaService e AlunoService com escopo, validação, contatos, endereços e
  responsáveis.
- Telas administrativas de pessoas, alunos e responsáveis.
- Permissões pessoa.*, luno.*, esponsavel.*, lgpd.consentimento.* e
  saude.*; catálogo com 72 permissões.
- Suíte com 65 testes e 493 asserções.
- Registro em Manual/04-onda-1-pessoas-alunos.md.

Pendências da Onda 1: upload/tela de documentos, tela de consentimentos, tela de
restrições de saúde e dados de demonstração de alunos.

Próxima onda: Onda 2 — catálogo, planos, preços, contratos e matrículas.
### Status de execução — Onda 2

Implementada em 2026-11-09:

- Migrations 0023–0025: catálogo comercial, planos, preços por unidade,
  serviços, promoções, contratos, matrículas, reajustes, descontos e
  transferências.
- PlanoService, PlanoPrecoService, MatriculaService e ContratoService.
- Telas de planos, preços, matrículas e contratos.
- Permissões matricula.* e contrato.*; catálogo com 72 permissões.
- Regras de escopo, vigência, preço da unidade do aluno e matrícula ativa única.
- Suíte com 65 testes e 493 asserções.
- Registro em Manual/05-onda-2-catalogo-matriculas.md.

Pendências da Onda 2: telas de modalidades, serviços, promoções, reajustes,
descontos e transferências; renovação automática e cobranças na Onda 4.

Próxima onda: Onda 3 — operação/acesso ou Onda 4 — financeiro, conforme
prioridade.
### Status de execução — Onda 3

Implementada em 2026-11-09:

- Migrations 0026–0028: turmas, horários, turma/aluno, reservas, frequências,
  dispositivos de acesso, eventos de acesso e ocorrências.
- TurmaService, FrequenciaService, ReservaService e AcessoService.
- Telas de turmas, horários, frequência, reservas, dispositivos e eventos.
- Permissões 	urma.*, requencia.*, eserva.*, dispositivo.*,
  evento_acesso.* e ocorrencia.*; catálogo com 72 permissões.
- Regras de sobreposição, duplicidade, capacidade, dia da semana e isolamento.
- Suíte com 65 testes e 493 asserções.
- Registro em Manual/06-onda-3-operacao-acesso.md.

Pendências da Onda 3: tela de vínculo turma/aluno, professor no horário, tela de
ocorrências, registro manual de eventos e integração real com catracas.

Próxima onda: Onda 4 — financeiro.