# Perfis, Níveis e Unidades

**Documento:** `02-perfis-niveis-unidades.md`\
**Versão:** 1.1 (revisada)\
**Status:** Especificação estrutural normativa\
**Dependência:** `01-estrutura-inicial.md`\
**Última atualização:** 2026-11-08

------------------------------------------------------------------------

## 1. Objetivo

Definir a estrutura organizacional da plataforma de gestão de academias,
estabelecendo:

- Hierarquia administrativa da plataforma.
- Perfis de acesso.
- Papéis e responsabilidades.
- Estrutura multiempresa.
- Estrutura multiunidade.
- Agrupamento regional.
- Regras de vinculação entre usuários, redes e unidades.
- Fundamentos do sistema de permissões.
- Regras normativas de autorização, isolamento e auditoria.

Este documento não implementa funcionalidades específicas. Seu objetivo é
definir a base de autorização e administração da plataforma para suportar todos
os módulos futuros.

O sistema gerencia redes de academias. Cada **rede** é uma empresa contratante,
com isolamento de dados em relação às demais. Cada rede pode organizar suas
unidades em **regiões administrativas** opcionais, conforme a conveniência dos
proprietários.

------------------------------------------------------------------------

## 2. Conceitos fundamentais

A plataforma possui três níveis organizacionais principais:

```text
Plataforma (SaaS)
│
├── Rede
│   │
│   ├── Região (opcional)
│   │   ├── Unidade
│   │   └── Unidade
│   │
│   ├── Unidade
│   └── Unidade
│
├── Rede
│   │
│   ├── Unidade
│   └── Unidade
│
└── Rede
```

### Plataforma

Representa todo o sistema SaaS. É administrada pelos Super Administradores da
plataforma e é responsável por:

- Gestão de redes.
- Controle global.
- Configurações gerais.
- Auditoria.
- Licenciamento.
- Monitoramento.

### Rede

Representa uma empresa contratante.

Exemplos:

- Rede Alpha Fitness.
- Rede Power Gym.
- Rede Corpo em Movimento.

Cada rede possui isolamento de dados. Nenhuma consulta, operação ou relatório
pode atravessar os limites de uma rede sem autorização explícita e compatível
com o escopo do usuário.

### Unidade

Representa uma academia física e sempre pertence a uma rede.

Exemplos:

- Alpha Fitness Centro.
- Alpha Fitness Zona Sul.
- Alpha Fitness Shopping.

Cada unidade possui:

- Equipe própria.
- Custos próprios.
- Contratos próprios.
- Contabilidade própria.
- Clientes vinculados.
- Receitas e despesas próprias.

### Região

Agrupamento administrativo opcional de unidades próximas dentro da mesma rede.
Exemplos: unidades de Porto Alegre, unidades da Região Metropolitana.

A região não é um nível organizacional obrigatório. Ela existe para possibilitar
um nível intermediário de administração, delegação de responsabilidade e
relatórios regionais, sempre a critério dos proprietários da rede.

### Usuário, vínculo, perfil e permissão

- **Usuário:** pessoa autenticada, com cadastro único na plataforma.
- **Vínculo:** associação do usuário com uma rede e/ou unidade, com perfil e
  escopo definidos.
- **Perfil:** conjunto nomeado de permissões, podendo ser de sistema ou
  personalizado.
- **Permissão:** autorização granular no padrão `modulo.acao`.
- **Contexto:** rede e unidade ativas na sessão do usuário.

------------------------------------------------------------------------

## 3. Hierarquia administrativa

A hierarquia inicial será organizada da seguinte forma:

```text
SUPERADMIN
│
├── ADMIN_REGIONAL
│     │
│     ├── ADMIN_LOCAL
│     │      │
│     │      ├── DIRETOR
│     │      ├── GERENTE
│     │      ├── CONTABILIDADE
│     │      ├── RECEPCAO
│     │      └── FUNCIONARIO
│     │
│     └── ADMIN_LOCAL
│
└── ADMIN_REGIONAL
```

### 3.1 Níveis normativos

Os níveis abaixo são a referência inicial. Podem ser ajustados pela plataforma,
desde que permaneçam explícitos, únicos e persistidos em `perfis.nivel`.

| Perfil | Nível | Escopo típico | Finalidade |
| --- | ---: | --- | --- |
| `SUPERADMIN` | 100 | Plataforma | Administração global e configurações da plataforma |
| `ADMIN_REGIONAL` | 80 | Rede/Região | Administração de um conjunto de unidades da mesma rede |
| `ADMIN_LOCAL` | 60 | Unidade | Administração de uma unidade específica |
| `DIRETOR` | 50 | Unidade | Supervisão da unidade, financeiro e aprovações |
| `GERENTE` | 40 | Unidade | Operação diária, alunos, equipe e planos |
| `CONTABILIDADE` | 30 | Unidade | Informações financeiras e obrigações contábeis |
| `RECEPCAO` | 20 | Unidade | Atendimento, cadastro, matrículas e presença |
| `FUNCIONARIO` | 10 | Unidade | Perfil genérico com permissões conforme a função |

### 3.2 Regras de hierarquia

1. O nível expressa **autoridade administrativa e capacidade de concessão**.
2. O nível **não concede acesso automático** a dados de outros módulos.
3. A visibilidade de dados depende de permissão explícita e do escopo do
   vínculo.
4. Um usuário nunca poderá conceder permissões ou perfis superiores às suas.
5. Um usuário pode conceder perfis e permissões de nível igual ou inferior ao
   seu, desde que possua as permissões correspondentes e esteja dentro do seu
   escopo.
6. O isolamento entre redes é obrigatório em qualquer nível.
7. O `SUPERADMIN` é o nível máximo e pode conceder qualquer perfil ou permissão
   existente no catálogo.

> **Normativa:** a hierarquia serve para escopo, visibilidade administrativa e
> regras de concessão. Ela não substitui a verificação de permissão nem o
> isolamento por rede/unidade.

------------------------------------------------------------------------

## 4. Tipos de usuários administrativos

### 4.1 SUPERADMIN

Nível máximo da plataforma. Possui acesso irrestrito, sempre com auditoria.

Responsabilidades:

- Criar redes.
- Editar redes.
- Bloquear redes.
- Criar regiões administrativas.
- Criar unidades.
- Criar administradores regionais.
- Acessar qualquer informação.
- Consultar auditorias.
- Gerenciar permissões globais.
- Definir configurações da plataforma.

Pode criar:

- `SUPERADMIN`.
- `ADMIN_REGIONAL`.
- `ADMIN_LOCAL`.

### 4.2 ADMIN_REGIONAL

Responsável por um conjunto de unidades de uma rede.

Exemplo:

```text
Região Sul
├── Unidade A
├── Unidade B
└── Unidade C
```

Permissões:

- Visualizar unidades da região.
- Gerenciar unidades da região.
- Consultar relatórios regionais.
- Gerenciar administradores locais.
- Aprovar determinadas operações.

Não possui acesso a unidades fora de sua região nem a redes diferentes.

Pode criar e gerenciar, dentro do seu escopo:

- `ADMIN_LOCAL`.
- Perfis operacionais da unidade.

### 4.3 ADMIN_LOCAL

Responsável por uma unidade específica.

Exemplo: Unidade Centro.

Permissões:

- Gerenciar usuários locais.
- Gerenciar planos.
- Gerenciar clientes.
- Gerenciar pagamentos.
- Gerenciar funcionários.
- Gerenciar configurações da unidade.

Não possui acesso a outras unidades.

Pode criar e gerenciar, dentro da sua unidade:

- `DIRETOR`.
- `GERENTE`.
- `CONTABILIDADE`.
- `RECEPCAO`.
- `FUNCIONARIO`.

### 4.4 Regras de delegação

1. Nenhum usuário pode promover outro acima de seu nível.
2. Nenhum usuário pode conceder permissões que não possua.
3. Nenhum usuário pode conceder perfil ou permissão fora do seu escopo de rede
   ou unidade.
4. Toda concessão, revogação ou alteração de perfil e permissão deve ser
   auditada.
5. Perfis críticos poderão exigir confirmação adicional no futuro.

------------------------------------------------------------------------

## 5. Perfis operacionais da unidade

Os perfis abaixo atuam dentro da unidade. Suas permissões são sugestões iniciais
e devem existir no catálogo de permissões.

### 5.1 DIRETOR

Responsável pela supervisão da unidade.

Permissões sugeridas:

- Acesso financeiro completo.
- Relatórios operacionais.
- Gestão de equipe.
- Aprovação de operações críticas.

### 5.2 GERENTE

Responsável pela operação diária.

Permissões sugeridas:

- Gestão de alunos.
- Gestão de funcionários.
- Gestão de planos.
- Gestão de turmas.
- Relatórios operacionais.

### 5.3 CONTABILIDADE

Responsável pelas informações financeiras.

Permissões sugeridas:

- Contas a pagar.
- Contas a receber.
- Folha de pagamento.
- Impostos.
- Demonstrativos financeiros.

Não possui acesso administrativo completo.

### 5.4 RECEPCAO

Responsável pelo atendimento.

Permissões sugeridas:

- Cadastro de clientes.
- Matrículas.
- Renovação de planos.
- Controle de presença.
- Atendimento.

Sem acesso a informações estratégicas.

### 5.5 FUNCIONARIO

Perfil genérico destinado à equipe.

Exemplos:

- Professor.
- Instrutor.
- Personal interno.
- Nutricionista.
- Avaliador físico.

As permissões dependerão de sua função e serão atribuídas explicitamente.

> **Normativa:** as permissões listadas acima são referências. A autorização
> efetiva é sempre calculada a partir dos perfis e permissões atribuídos no
> contexto, nunca pelo nome do cargo.

------------------------------------------------------------------------

## 6. Arquitetura de perfis e permissões

O sistema não deverá depender exclusivamente de cargos fixos. Será adotado um
modelo híbrido:

```text
Usuário
  │
  └── Vínculo com escopo (rede/unidade)
        │
        ├── Perfil
        │     └── Permissões padrão
        │
        └── Permissões explícitas adicionais
```

Exemplo:

```text
Usuário
└── João Silva

Vínculo
└── Rede Alpha / Unidade Centro

Perfil
└── Gerente

Permissões padrão
├── aluno.visualizar
├── aluno.editar
├── pagamento.visualizar
├── plano.editar
└── relatorio.visualizar

Permissão adicional
└── financeiro.visualizar
```

### 6.1 Decisões normativas

1. **RBAC híbrido:** o acesso é concedido por permissões; os perfis agrupam
   permissões padrão; permissões explícitas podem complementar o perfil.
2. **Perfis de sistema:** `SUPERADMIN`, `ADMIN_REGIONAL`, `ADMIN_LOCAL`,
   `DIRETOR`, `GERENTE`, `CONTABILIDADE`, `RECEPCAO` e `FUNCIONARIO` são criados
   por seed e não podem ser excluídos. Podem ser editados conforme as regras de
   governança.
3. **Perfis personalizados:** podem ser criados por usuários autorizados, com
   nível igual ou inferior ao seu e permissões que o criador possua.
4. **Permissões efetivas:** são a união das permissões dos perfis atribuídos no
   contexto atual com as permissões explícitas, limitadas pelo nível e pelo
   escopo do vínculo.
5. **Deny by default:** toda rota protegida declara a permissão necessária. Na
   ausência da permissão, o acesso é negado.
6. **Backend como fonte de verdade:** o frontend pode ocultar ações, mas nunca é
   responsável pela autorização.
7. **Catálogo de permissões:** armazenado no banco, com cache por requisição.
   Novas permissões entram por seed ou migration, nunca por texto livre em tempo
   de execução.
8. **Sem herança automática de permissões:** o nível hierárquico não concede
   permissões de módulos automaticamente.

### 6.2 Vantagens do modelo

- Criar novos perfis.
- Alterar permissões sem alterar código.
- Reutilizar perfis.
- Evoluir o sistema futuramente.
- Conceder permissões específicas sem criar cargos intermediários.

------------------------------------------------------------------------

## 7. Estrutura inicial de permissões

As permissões seguem o padrão:

```text
modulo.acao
```

Catálogo inicial sugerido:

| Módulo | Permissões |
| --- | --- |
| Rede | `rede.criar`, `rede.editar`, `rede.bloquear`, `rede.visualizar` |
| Região | `regiao.criar`, `regiao.editar`, `regiao.visualizar` |
| Unidade | `unidade.criar`, `unidade.editar`, `unidade.visualizar` |
| Usuário | `usuario.criar`, `usuario.editar`, `usuario.excluir`, `usuario.visualizar` |
| Perfil | `perfil.criar`, `perfil.editar`, `perfil.visualizar`, `perfil.atribuir` |
| Permissão | `permissao.visualizar`, `permissao.atribuir` |
| Vínculo | `vinculo.criar`, `vinculo.editar`, `vinculo.visualizar` |
| Cliente | `cliente.criar`, `cliente.editar`, `cliente.visualizar` |
| Plano | `plano.criar`, `plano.editar`, `plano.visualizar` |
| Financeiro | `financeiro.visualizar`, `financeiro.editar` |
| Relatório | `relatorio.visualizar` |
| Auditoria | `auditoria.visualizar` |
| Contexto | `contexto.trocar` |

Regras:

1. O `slug` da permissão é único.
2. Permissões são agrupadas por módulo para facilitar a administração.
3. A inclusão de novas permissões exige atualização do catálogo por seed ou
   migration.
4. A remoção de permissão deve ser feita com cautela e auditada.
5. O catálogo é a fonte de verdade para a autorização.

------------------------------------------------------------------------

## 8. Herança de permissões e regras de concessão

A autorização deverá considerar os seguintes fatores:

### 8.1 Nível hierárquico

```text
SUPERADMIN
> ADMIN_REGIONAL
> ADMIN_LOCAL
> DIRETOR
> GERENTE
> CONTABILIDADE
> RECEPCAO
> FUNCIONARIO
```

O nível permite que um usuário superior **administre** níveis inferiores dentro
do mesmo escopo, desde que possua as permissões necessárias. O nível, por si só,
não concede acesso a dados de outros módulos.

### 8.2 Permissões explícitas

Além do perfil:

```text
Cargo:
Gerente

Permissão adicional:
financeiro.visualizar
```

Dessa forma o sistema torna-se mais flexível.

### 8.3 Regras de concessão

1. Um usuário só pode conceder perfis de nível igual ou inferior ao seu.
2. Um usuário só pode conceder permissões que ele próprio possua.
3. Um usuário só pode conceder perfis e permissões dentro do seu escopo de rede
   ou unidade.
4. Um usuário não pode criar perfil com nível superior ao seu.
5. O `SUPERADMIN` pode conceder qualquer perfil e permissão existente.
6. Toda concessão, revogação ou alteração deve ser auditada.
7. A permissão efetiva é sempre verificada no backend.

### 8.4 Escopo do vínculo

O perfil atribuído ao usuário tem escopo de rede e/ou unidade. O mesmo usuário
pode ser:

```text
João
├── Gerente na Unidade Centro
└── Recepção na Unidade Shopping
```

As permissões do perfil valem apenas no contexto em que foram atribuídas.

### 8.5 Negação por padrão

Se não houver permissão explícita para a operação no contexto atual, o acesso é
negado.

------------------------------------------------------------------------

## 9. Estrutura de redes

Cada empresa contratante será cadastrada como uma Rede.

Exemplo:

```text
Rede Alpha Fitness
```

Informações mínimas:

- Razão social.
- Nome fantasia.
- CNPJ.
- E-mail principal.
- Telefone.
- Endereço.
- Status.
- Plano contratado.

Status possíveis:

```text
ATIVA
SUSPENSA
EM_IMPLANTACAO
CANCELADA
```

Regras:

1. O CNPJ deve ser único.
2. Toda rede possui isolamento de dados.
3. O status controla o acesso operacional da rede.
4. A criação, alteração e bloqueio de redes devem ser auditados.

------------------------------------------------------------------------

## 10. Estrutura de unidades

Uma unidade sempre pertence a uma rede.

Relacionamento:

```text
Rede
└── Unidade
```

Informações mínimas:

### Identificação

- Nome da unidade.
- Código interno.
- Situação.

### Endereço

- CEP.
- Logradouro.
- Número.
- Complemento.
- Bairro.
- Cidade.
- Estado.

### Contato

- Telefone.
- WhatsApp.
- E-mail.

### Operação

- Data de abertura.
- Horário de funcionamento.
- Capacidade máxima.
- Metragem.

Regras:

1. `rede_id` é obrigatório.
2. O código interno é único dentro da rede.
3. A unidade pode ou não pertencer a uma região.
4. A unidade não pode ser acessada por usuários de outra rede.
5. A criação e alteração de unidades devem ser auditadas.

------------------------------------------------------------------------

## 11. Agrupamento regional

As unidades poderão ser agrupadas em regiões dentro da mesma rede.

Exemplo:

```text
Rede Alpha Fitness
└── Região Metropolitana
    ├── Unidade Porto Alegre Centro
    ├── Unidade Porto Alegre Zona Sul
    └── Unidade Canoas
```

Objetivos:

- Facilitar a administração.
- Delegar responsabilidade.
- Gerar relatórios regionais.
- Criar um nível intermediário de administração entre a rede e a unidade.

Regras:

1. A região pertence a uma rede (`regioes.rede_id`).
2. O agrupamento é opcional e definido pelos proprietários da rede.
3. Uma unidade pode ter no máximo uma região ativa.
4. A relação pode ser mantida em `regiao_unidade` para preservar histórico e
   flexibilidade.
5. O escopo do `ADMIN_REGIONAL` é derivado das regiões e unidades vinculadas.
6. A criação e alteração de regiões devem ser auditadas.

------------------------------------------------------------------------

## 12. Vínculo de usuários

Um usuário poderá estar vinculado a:

### Uma Rede

```text
João
└── Rede Alpha
```

### Múltiplas Unidades

```text
João
├── Unidade Centro
├── Unidade Shopping
└── Unidade Sul
```

### Um ou mais Perfis

```text
João
├── Gerente
└── Recepção
```

O sistema deverá permitir múltiplos vínculos sem duplicar cadastros de usuário.

### 12.1 Modelo normativo de vínculo

- `usuario_rede`: indica que o usuário participa da rede.
- `usuario_unidade`: indica que o usuário participa da unidade.
- `usuario_perfil`: atribui o perfil com escopo de rede e/ou unidade.
- `usuario_permissao`: registra permissões explícitas adicionais ou negadas no
  mesmo escopo; a negação prevalece sobre a concessão.
- O escopo do perfil é obrigatório e deve ser compatível com os vínculos.
- O mesmo usuário pode ter perfis diferentes em unidades diferentes.
- Perfis administrativos de plataforma podem ter escopo de rede nulo.

### 12.2 Regras de consistência

1. Se `unidade_id` for informado, a unidade deve pertencer à `rede_id`.
2. Se `regiao_id` for informado, a região deve pertencer à `rede_id`.
3. O usuário deve possuir vínculo ativo com a rede e/ou unidade do perfil.
4. O perfil deve existir e estar ativo.
5. A combinação usuário, perfil e escopo não deve duplicar vínculos ativos.
6. A remoção de vínculo deve ser lógica (status), preservando histórico e
   auditoria.

Exemplo:

```text
usuario_perfil
├── João | Gerente | Rede Alpha | Unidade Centro
└── João | Recepção | Rede Alpha | Unidade Shopping
```

------------------------------------------------------------------------

## 13. Alternância de contexto

Usuários autorizados poderão trocar de contexto.

Exemplo:

```text
ADMIN_REGIONAL

Unidade atual:
Centro

Trocar para:
Shopping
Zona Sul
```

A mudança deverá atualizar:

- Escopo de consultas.
- Permissões aplicadas.
- Dados exibidos.

Regras normativas:

1. O contexto ativo é armazenado na sessão (`context.rede_id`,
   `context.unidade_id`).
2. O contexto é sempre validado contra os vínculos do usuário no backend.
3. Nunca aceitar `rede_id` ou `unidade_id` enviados pelo navegador sem
   validação.
4. Ao autenticar, o sistema resolve o contexto padrão a partir dos vínculos.
5. Se houver múltiplos contextos, o usuário escolhe; se não houver, o acesso é
   negado.
6. A troca exige a permissão `contexto.trocar` e um vínculo válido.
7. A troca de contexto deve ser auditada.
8. As permissões efetivas devem ser recarregadas após a troca.
9. Consultas de dados empresariais devem aplicar o escopo atual em Repositories.

------------------------------------------------------------------------

## 14. Auditoria de administração

Toda ação administrativa relevante deverá ser registrada.

Exemplos:

- Criação de rede.
- Criação de unidade.
- Criação de região.
- Alteração de perfil.
- Mudança de permissões.
- Criação de usuário.
- Bloqueio de usuário.
- Troca de administrador.
- Troca de contexto.
- Concessão ou revogação de vínculo.

Informações mínimas do log:

- Usuário.
- Data.
- Hora.
- IP.
- Operação.
- Recurso alterado.
- Valores anteriores.
- Valores posteriores.

Modelo conceitual:

```text
auditoria
├── usuario_id
├── rede_id
├── unidade_id
├── acao
├── entidade
├── entidade_id
├── dados_anteriores (JSON)
├── dados_posteriores (JSON)
├── ip
├── user_agent
└── created_at
```

Regras:

1. Senhas, tokens e dados sensíveis não devem ser registrados.
2. O acesso à auditoria deve ser restrito por permissão.
3. A retenção e o mascaramento devem observar a LGPD.
4. Ações críticas devem ser auditadas em transação com a operação principal,
   quando possível.
5. A auditoria não substitui logs técnicos de aplicação.

------------------------------------------------------------------------

## 15. Diretrizes de segurança

Regras obrigatórias:

1. Nenhum usuário pode promover outro acima de seu nível.
2. Nenhum usuário pode conceder permissões que não possua.
3. O backend sempre valida autorização.
4. O frontend nunca é considerado fonte de verdade.
5. O contexto de rede e unidade deve ser validado no backend.
6. Alterações de permissões devem ser auditadas.
7. Perfis críticos exigirão confirmação adicional no futuro.
8. Toda rota protegida deve declarar permissão (deny by default).
9. O isolamento entre redes deve ser testado em todas as camadas.
10. Consultas empresariais devem aplicar o escopo da rede/unidade.
11. A sessão deve ser regenerada após autenticação.
12. Mudanças de permissão devem invalidar ou recarregar sessões afetadas.
13. Arquivos privados e dados sensíveis devem permanecer fora de acesso público.
14. O sistema deve evitar vazamento de dados entre redes em mensagens de erro,
    logs e relatórios.

------------------------------------------------------------------------

## 16. Estrutura inicial de banco de dados (conceitual)

As tabelas abaixo são referência de modelagem inicial. A modelagem detalhada
será especificada em `03-banco-de-dados.md`.

```text
redes
regioes
unidades

usuarios

perfis
permissoes

perfil_permissao

usuario_perfil
usuario_permissao
usuario_rede
usuario_unidade

regiao_unidade

auditoria
```

### 16.1 Campos e observações

| Tabela | Campos principais | Observações |
| --- | --- | --- |
| `redes` | razão social, nome fantasia, CNPJ, e-mail, telefone, endereço, status, plano | CNPJ único; isolamento de dados |
| `regioes` | `rede_id`, nome, status | Região pertence à rede; agrupamento opcional |
| `unidades` | `rede_id`, nome, código interno, situação, endereço, contatos, operação | Código interno único por rede |
| `usuarios` | nome, e-mail único, senha_hash, status, último_login | Substitui a autenticação de demonstração |
| `perfis` | slug, nome, nível, escopo, sistema, status | Perfis de sistema protegidos |
| `permissoes` | slug `modulo.acao`, módulo, ação, descrição | Catálogo único |
| `perfil_permissao` | `perfil_id`, `permissao_id` | Permissões padrão do perfil |
| `usuario_perfil` | `usuario_id`, `perfil_id`, `rede_id`, `unidade_id`, `regiao_id` | Escopo obrigatório; região opcional |
| `usuario_permissao` | `usuario_id`, `permissao_id`, `rede_id`, `unidade_id`, `regiao_id`, efeito | Permissões explícitas; negação prevalece |
| `usuario_rede` | `usuario_id`, `rede_id`, status | Participação na rede |
| `usuario_unidade` | `usuario_id`, `unidade_id`, status | Participação na unidade |
| `regiao_unidade` | `regiao_id`, `unidade_id` | Agrupamento regional |
| `auditoria` | usuário, rede, unidade, ação, entidade, dados anteriores/posteriores, IP, user agent, data | Sem dados sensíveis |

### 16.2 Diretrizes de modelagem

1. Utilizar `utf8mb4` e InnoDB.
2. Criar chaves estrangeiras com `RESTRICT` para entidades de negócio.
3. Criar índices em chaves estrangeiras e colunas de busca.
4. Utilizar `status` para desativação lógica em vez de exclusão física.
5. Manter `created_at` e `updated_at`.
6. Validar a consistência de escopo na aplicação e, quando possível, no banco.
7. Ordem sugerida de migrations:
   `redes` → `regioes` → `unidades` → `usuarios` → `perfis` →
   `permissoes` → `perfil_permissao` → `usuario_perfil` →
   `usuario_rede` → `usuario_unidade` → `regiao_unidade` → `auditoria`.
8. Criar seeds para permissões, perfis de sistema, rede/unidade de demonstração e
   superadministrador inicial.

------------------------------------------------------------------------

## 17. Critérios de aceite

Esta etapa será considerada concluída quando:

1. Existir definição formal dos níveis administrativos.
2. Existir definição formal dos perfis operacionais.
3. Existir estrutura de permissões baseada em perfis e permissões explícitas.
4. Existir catálogo de permissões `modulo.acao` persistido.
5. Existir definição de redes e unidades.
6. Existir definição de agrupamento regional.
7. Existir definição de vínculos de usuários com escopo de rede/unidade.
8. Existir estratégia de auditoria inicial.
9. Existir base para modelagem do banco de dados.
10. Existir autenticação real baseada em `usuarios`.
11. Existir middleware de permissão e de escopo.
12. Existir alternância de contexto validada no backend.
13. Existirem testes de isolamento entre redes e de não escalada de privilégio.
14. A documentação do módulo estiver atualizada.

------------------------------------------------------------------------

## 18. Decisões normativas consolidadas

1. O modelo é RBAC híbrido: perfil + permissões explícitas + escopo.
2. A hierarquia define autoridade e escopo, não concessão automática de
   permissões.
3. Os níveis são numéricos e persistidos em `perfis.nivel`.
4. `CONTABILIDADE` e `FUNCIONARIO` integram formalmente a hierarquia.
5. O vínculo de perfil (`usuario_perfil`) possui escopo de rede e/ou unidade,
   com região opcional para administração regional.
6. O mesmo usuário pode ter perfis diferentes em unidades diferentes.
7. O contexto ativo é armazenado na sessão e validado no backend.
8. O acesso é negado por padrão na ausência de permissão.
9. Toda ação administrativa relevante é auditada.
10. O catálogo de permissões é a fonte de verdade e é atualizado por seed ou
    migration.
11. O pipeline de middleware deve suportar permissões parametrizáveis por rota.
12. Regiões pertencem à rede, são opcionais e existem para agrupamento
    administrativo de unidades próximas.

------------------------------------------------------------------------

## 19. Próximo documento

Documento: `03-banco-de-dados.md`.

Conteúdo previsto:

- Modelagem física inicial.
- Estrutura multiempresa.
- Estrutura multiunidade.
- Usuários.
- Perfis.
- Permissões.
- Redes.
- Unidades.
- Regiões.
- Auditoria.
- Índices.
- Chaves estrangeiras.
- Estratégia de migrations.
- Primeiros scripts SQL da plataforma.
- Seeds iniciais.

------------------------------------------------------------------------

## 20. Plano de ação

### 20.1 Escopo acordado

A execução será dividida em fases. **Nesta etapa, serão executadas as Fases 0 a
3.** Ao concluir a Fase 3, o trabalho será interrompido e aguardará comando
explícito para prosseguir com as Fases 4 a 6.

### 20.2 Visão geral

| Fase | Entrega | Dependência | Estimativa |
| --- | --- | --- | --- |
| 0 | Decisões, catálogo de permissões e matriz de níveis | Aprovação deste documento | 0,5–1 dia |
| 1 | Migrations, seeds e documentação do schema | Fase 0 | 1,5–2 dias |
| 2 | Repositories, Services, Authorization, TenantContext e Audit | Fase 1 | 2–3 dias |
| 3 | Autenticação real, middleware de permissão/escopo e contexto | Fase 2 | 2–3 dias |
| 4 | Administração mínima de redes, unidades, regiões, usuários, perfis e vínculos | Fase 3 | 3–5 dias |
| 5 | Auditoria, anti-escalada, invalidação de sessão e testes de isolamento | Fases 2–4 | 1–2 dias |
| 6 | Homologação, seeds de demonstração, documentação e aceite | Todas | 1–2 dias |

**Estimativa das Fases 0–3:** 6–9 dias de desenvolvimento.\
**Estimativa total (Fases 0–6):** 11–18 dias de desenvolvimento.\
As estimativas são relativas e dependem da validação de escopo.

### 20.3 Fase 0 — Decisões e catálogo

- [ ] Aprovar as decisões normativas da seção 18.
- [ ] Confirmar a matriz de níveis e a matriz de perfis.
- [ ] Definir o catálogo inicial de permissões por módulo.
- [ ] Definir a política de retenção e mascaramento da auditoria.
- [ ] Registrar decisão arquitetural em `Manual/decisoes-tecnicas/`.
- [ ] Definir os critérios de saída das Fases 1–3.

**Saída esperada:** decisões registradas e catálogo aprovado.

### 20.4 Fase 1 — Banco e migrations

- [ ] Criar migrations na ordem definida na seção 16.2.
- [ ] Aplicar chaves estrangeiras, índices e restrições de unicidade.
- [ ] Criar seeds de permissões e perfis de sistema.
- [ ] Criar seed de rede/unidade de demonstração e superadministrador inicial.
- [ ] Implementar validações de consistência de escopo.
- [ ] Documentar o modelo em `Manual/03-banco-de-dados.md`.
- [ ] Validar `migrate`, `rollback` e `status`.

**Saída esperada:** schema aplicado, seeds carregadas e rollback testado.

### 20.5 Fase 2 — Domínio e serviços

- [ ] Criar Repositories:
  `RedeRepository`, `RegiaoRepository`, `UnidadeRepository`,
  `UsuarioRepository`, `PerfilRepository`, `PermissaoRepository`,
  `VinculoRepository`, `AuditoriaRepository`.
- [ ] Criar Services:
  `AuthService`, `AuthorizationService`, `TenantContextService`,
  `AuditService`, `UsuarioService`, `VinculoService`.
- [ ] Criar DTOs e Validators para login e cadastros administrativos.
- [ ] Implementar o catálogo de permissões com cache por requisição.
- [ ] Implementar as regras de escopo e de não escalada nos Services.
- [ ] Criar testes unitários das regras de autorização.

**Saída esperada:** regras de domínio testadas e isoladas de Controllers.

### 20.6 Fase 3 — Autenticação e autorização reais

- [ ] Substituir a autenticação de demonstração por `usuarios` com
  `password_hash()` e `password_verify()`.
- [ ] Remover `DEMO_EMAIL` e `DEMO_PASSWORD_HASH` do fluxo e do `.env.example`.
- [ ] Carregar vínculos, perfis e permissões após a autenticação.
- [ ] Criar `PermissionMiddleware` para permissões por rota.
- [ ] Criar `ScopeMiddleware` para validar o contexto ativo.
- [ ] Estender o pipeline para middleware parametrizável ou registrar IDs
  específicos por permissão.
- [ ] Implementar troca de contexto com validação de vínculo e auditoria.
- [ ] Atualizar rotas, layout, sidebar e telas para refletir permissões e
  contexto.
- [ ] Manter CSRF, regeneração de sessão e escape de saída.
- [ ] Criar testes de integração de login, permissão e contexto.
- [ ] Criar testes de isolamento entre redes e de não escalada.

**Saída esperada:** login real, autorização por permissão, contexto validado e
testes de isolamento aprovados.

### 20.7 Fases 4 a 6 — Aguardando comando

Após a conclusão da Fase 3, as fases seguintes ficam pendentes de autorização:

- **Fase 4:** administração mínima de redes, unidades, regiões, usuários,
  perfis, permissões e vínculos.
- **Fase 5:** consolidação da auditoria, regras anti-escalada, invalidação de
  sessão e testes de isolamento.
- **Fase 6:** homologação, seeds de demonstração, atualização documental e
  validação dos critérios de aceite.

### 20.8 Critérios de saída das Fases 0–3

- [ ] Documento 02 revisado e aprovado.
- [ ] ADR registrado.
- [ ] Migrations e seeds aplicadas e reversíveis.
- [ ] Repositories e Services implementados com testes.
- [ ] Autenticação real funcionando.
- [ ] Permissões e escopos aplicados em rotas protegidas.
- [ ] Troca de contexto validada e auditada.
- [ ] Testes de isolamento entre redes aprovados.
- [ ] Testes de não escalada aprovados.
- [ ] Documentação atualizada.

------------------------------------------------------------------------

## 21. Status de execução

**Data:** 2026-11-08
**Situação:** Fases 0–6 executadas.

### Entregas

- 13 migrations e seeds idempotentes (36 permissões, 8 perfis, 8 usuários demo).
- Repositories, Services, AuthorizationService, TenantContextService e AuditService.
- Autenticação real em usuarios, com remoção da autenticação de demonstração.
- PermissionMiddleware, ScopeMiddleware e pipeline parametrizável.
- CRUD administrativo de redes, regiões, unidades, usuários, perfis e vínculos.
- Anti-escalada de privilégio, escopo por rede/unidade/região e auditoria.
- 49 testes e 318 asserções aprovados.
- Documentação atualizada em Manual/00, Manual/01, Manual/03,
  Manual/decisoes-tecnicas/0004 e Manual/decisoes-tecnicas/0005.

### Evidências

- composer migrate, composer seed, composer test e rollback validados.
- Fluxo web validado: login, contexto, permissões, administração, revogação e
  auditoria.
- Isolamento entre unidades e não escalada cobertos por testes de integração.

### Pendências

- Política formal de retenção e mascaramento da auditoria (LGPD).
- Interface para permissões explícitas em usuario_permissao.
- Módulos funcionais de alunos, financeiro, treinos e comunidade.
- Testes externos de carga, segurança e acessibilidade.

------------------------------------------------------------------------
### Correção de escopo — Prioridade 1

Aplicada em 2026-11-08:

- Edição de usuários limitada ao vínculo direto e ao nível do ator.
- Bloqueio de edição/senha de usuários de nível superior.
- Auditoria de unidade limitada à unidade; ações de rede/região/perfis globais sem unidade.
- Migration  015_normalize_audit_scope.php para escopos históricos.
- Listagem de perfis filtrada por nível; edição de perfil superior retorna 403.
- Suíte: 49 testes e 318 asserções.

Permanecem as Prioridades 2–3 do relatório de escopo.
### Prioridades 2–3 — perfis e regiões

Aplicadas em 2026-11-08:

- perfis.rede_id para perfis personalizados por rede; perfis de sistema/globais somente leitura para não Superadmin.
- Concessão estrita: nível inferior ao ator, exceto Superadmin.
- Admin Local não concede ADMIN_LOCAL; Admin Regional não concede ADMIN_REGIONAL.
- Vínculos de unidade somente da unidade; seletor de região oculto no contexto de unidade; perfis filtrados por concessão.
- Contexto regional (context.regiao_id) e escopo regional em unidades, usuários, vínculos, regiões e auditoria.
- Criação de unidade em contexto regional vincula automaticamente a região.
- Regiões passam a ser criadas/editadas apenas pelo Superadmin.
- Dados de teste removidos: 1 rede, 1 região, 2 unidades, 8 usuários e 8 perfis.
- Suíte: 49 testes e 318 asserções.