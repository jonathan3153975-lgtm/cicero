# Perfis, Níveis e Unidades

Documento: 02 - Perfis, níveis e unidades.md
Versão: 1.0
Status: Especificação estrutural
Dependência: 01-estrutura-inicial.md

1. Objetivo

Definir a estrutura organizacional da plataforma de gestão de academias, estabelecendo:

Hierarquia administrativa da plataforma.
Perfis de acesso.
Papéis e responsabilidades.
Estrutura multiempresa.
Estrutura multiunidade.
Regras de vinculação entre usuários, redes e unidades.
Fundamentos do sistema de permissões.

Este documento não implementa funcionalidades específicas.

Seu objetivo é definir a base de autorização e administração da plataforma para suportar todos os módulos futuros.

2. Conceitos Fundamentais

A plataforma possuirá três níveis organizacionais principais:

Plataforma (SaaS)
│
├── Rede
│   │
│   ├── Unidade
│   ├── Unidade
│   └── Unidade
│
├── Rede
│   │
│   ├── Unidade
│   └── Unidade
│
└── Rede


Plataforma

Representa todo o sistema SaaS.

Administrada pelos Super Administradores da plataforma.

Responsável por:

Gestão de redes.
Controle global.
Configurações gerais.
Auditoria.
Licenciamento.
Monitoramento.

Rede

Representa uma empresa contratante.

Exemplos:
Rede Alpha Fitness
Rede Power Gym
Rede Corpo em Movimento

Unidade

Representa uma academia física.

Exemplos:
Alpha Fitness Centro
Alpha Fitness Zona Sul
Alpha Fitness Shopping

Cada unidade possui:

Equipe própria.
Custos próprios.
Contratos próprios.
Contabilidade própria.
Clientes vinculados.
Receitas e despesas próprias.


3. Hierarquia Administrativa

A estrutura inicial será organizada da seguinte forma:

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
Um usuário nunca poderá conceder permissões superiores às suas.



4. Tipos de Usuários Administrativos
4.1 SUPERADMIN

Nível máximo da plataforma.

Possui acesso irrestrito.

Responsabilidades:

Criar redes.
Editar redes.
Bloquear redes.
Criar regiões administrativas.
Criar unidades.
Criar administradores regionais.
Acessar qualquer informação.
Consultar auditorias.
Gerenciar permissões globais.
Definir configurações da plataforma.

Pode criar:
SUPERADMIN
ADMIN_REGIONAL
ADMIN_LOCAL


4.2 ADMIN_REGIONAL

Responsável por um conjunto de unidades.

Exemplo:
Região Sul
├── Unidade A
├── Unidade B
├── Unidade C


Permissões:

Visualizar unidades da região.
Gerenciar unidades da região.
Consultar relatórios.
Gerenciar administradores locais.
Aprovar determinadas operações.

Não possui acesso a unidades fora de sua região.


4.3 ADMIN_LOCAL

Responsável por uma unidade específica.

Exemplo:
Unidade Centro
Permissões:

Gerenciar usuários locais.
Gerenciar planos.
Gerenciar clientes.
Gerenciar pagamentos.
Gerenciar funcionários.
Gerenciar configurações da unidade.

Não possui acesso a outras unidades.



5. Perfis Operacionais da Unidade

Os perfis abaixo atuam dentro da unidade.



5.1 DIRETOR

Responsável pela supervisão da unidade.

Permissões sugeridas:

Acesso financeiro completo.
Relatórios operacionais.
Gestão de equipe.
Aprovação de operações críticas.

5.2 GERENTE

Responsável pela operação diária.

Permissões sugeridas:

Gestão de alunos.
Gestão de funcionários.
Gestão de planos.
Gestão de turmas.
Relatórios operacionais.


5.3 CONTABILIDADE

Responsável pelas informações financeiras.

Permissões sugeridas:

Contas a pagar.
Contas a receber.
Folha de pagamento.
Impostos.
Demonstrativos financeiros.

Não possui acesso administrativo completo.



5.4 RECEPÇÃO

Responsável pelo atendimento.

Permissões sugeridas:

Cadastro de clientes.
Matrículas.
Renovação de planos.
Controle de presença.
Atendimento.

Sem acesso a informações estratégicas.



5.5 FUNCIONÁRIO

Perfil genérico destinado à equipe.

Exemplos:
Professor
Instrutor
Personal interno
Nutricionista
Avaliador físico
As permissões dependerão de sua função.



6. Arquitetura de Perfis e Permissões

O sistema não deverá depender exclusivamente de cargos fixos.

Será adotado um modelo híbrido:

Usuário
 ↓
Perfil
 ↓
Permissões

Exemplo:

Plain Text
Usuário
└── João Silva
 
Perfil
└── Gerente
 
Permissões
├── aluno.visualizar
├── aluno.editar
├── pagamento.visualizar
├── plano.editar
└── relatorio.visualizar



Dessa forma será possível:

Criar novos perfis.
Alterar permissões sem alterar código.
Reutilizar perfis.
Evoluir o sistema futuramente.

7. Estrutura Inicial de Permissões

As permissões deverão seguir o padrão:

Plain Text
modulo.acao

Exemplos:

Plain Text
usuario.criar
usuario.editar
usuario.excluir
 
cliente.criar
cliente.editar
cliente.visualizar
 
plano.criar
plano.editar
 
financeiro.visualizar
financeiro.editar
 
unidade.criar
unidade.editar
 
relatorio.visualizar
8. Herança de Permissões

A autorização deverá considerar:

Nível Hierárquico

Exemplo:

Plain Text
SUPERADMIN
> ADMIN_REGIONAL
> ADMIN_LOCAL
> DIRETOR
> GERENTE
> RECEPCAO

Um usuário superior pode visualizar informações dos níveis inferiores respeitando as regras de isolamento empresarial.

Permissões Explícitas

Além do cargo:

Plain Text
Cargo:
Gerente
 
Permissão adicional:
financeiro.visualizar

Dessa forma o sistema torna-se mais flexível.

9. Estrutura de Redes

Cada empresa contratante será cadastrada como uma Rede.

Exemplo:

Plain Text
Rede Alpha Fitness

Informações mínimas:

Razão social.
Nome fantasia.
CNPJ.
E-mail principal.
Telefone.
Endereço.
Status.
Plano contratado.
Status possíveis
Plain Text
ATIVA
SUSPENSA
EM_IMPLANTACAO
CANCELADA
10. Estrutura de Unidades

Uma unidade sempre pertence a uma rede.

Relacionamento:

Plain Text
Rede
└── Unidade
Informações mínimas
Identificação
Nome da unidade.
Código interno.
Situação.
Endereço
CEP.
Logradouro.
Número.
Complemento.
Bairro.
Cidade.
Estado.
Contato
Telefone.
WhatsApp.
E-mail.
Operação
Data de abertura.
Horário de funcionamento.
Capacidade máxima.
Metragem.
11. Agrupamento Regional

As unidades poderão ser agrupadas em regiões.

Exemplo:

Plain Text
Região Sul
├── Unidade Pelotas
├── Unidade Rio Grande
└── Unidade Bagé

Objetivos:

Facilitar administração.
Delegar responsabilidade.
Gerar relatórios regionais.
12. Vínculo de Usuários

Um usuário poderá estar vinculado a:

Uma Rede
Plain Text
João
└── Rede Alpha
Múltiplas Unidades
Plain Text
João
├── Unidade Centro
├── Unidade Shopping
└── Unidade Sul
Um ou Mais Perfis
Plain Text
João
├── Gerente
└── Recepção

O sistema deverá permitir múltiplos vínculos sem duplicar cadastros de usuário.

13. Alternância de Contexto

Usuários autorizados poderão trocar de contexto.

Exemplo:

Plain Text
ADMIN_REGIONAL
 
Unidade Atual:
Centro
 
Trocar para:
Shopping
Zona Sul

A mudança deverá atualizar:

Escopo de consultas.
Permissões aplicadas.
Dados exibidos.

Sempre validando os vínculos autorizados.

14. Auditoria de Administração

Toda ação administrativa relevante deverá ser registrada.

Exemplos:

Plain Text
Criação de rede
Criação de unidade
Alteração de perfil
Mudança de permissões
Criação de usuário
Bloqueio de usuário
Troca de administrador

Informações mínimas do log:

Usuário.
Data.
Hora.
IP.
Operação.
Recurso alterado.
Valores anteriores.
Valores posteriores.
15. Diretrizes de Segurança

Regras obrigatórias:

Nenhum usuário pode promover outro acima de seu nível.
Nenhum usuário pode conceder permissões que não possua.
O backend sempre valida autorização.
O frontend nunca é considerado fonte de verdade.
O contexto de rede e unidade deve ser validado.
Alterações de permissões devem ser auditadas.
Perfis críticos exigirão confirmação adicional no futuro.
16. Estrutura Inicial de Banco de Dados (Conceitual)

As tabelas abaixo são apenas referência de modelagem inicial.

Plain Text
redes
unidades
 
usuarios
 
perfis
permissoes
 
usuario_perfil
perfil_permissao
 
usuario_rede
usuario_unidade
 
regioes
regiao_unidade
 
auditoria

A modelagem detalhada será especificada em:

Plain Text
03-banco-de-dados.md
17. Critérios de Aceite

Esta etapa será considerada concluída quando:

Existir definição formal dos níveis administrativos.
Existir definição formal dos perfis operacionais.
Existir estrutura de permissões baseada em perfis.
Existir definição de redes e unidades.
Existir definição de agrupamento regional.
Existir definição de vínculos de usuários.
Existir estratégia de auditoria inicial.
Existir base para modelagem do banco de dados.
18. Próximo Documento

Documento: 03-banco-de-dados.md

Conteúdo previsto:

Modelagem física inicial.
Estrutura multiempresa.
Estrutura multiunidade.
Usuários.
Perfis.
Permissões.
Redes.
Unidades.
Regiões.
Auditoria.
Índices.
Chaves estrangeiras.
Estratégia de migrations.
Primeiros scripts SQL da plataforma.