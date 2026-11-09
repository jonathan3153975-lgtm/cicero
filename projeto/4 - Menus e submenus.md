Vamos ajustar o visual e agrupar os menus, trabalhando com menu e submenu, para tornar a navegação do usuário mais simples.
Obs. Mantenha a regra de disponibilidade de acordo com o perfil.

## Config. estrutura;
-- Redes                    ### Empresas contratantes e seus planos.
-- Regiões                  ### Agrupamento administrativo opcional de unidades próximas.
-- Unidades                 ### Academias físicas vinculadas às redes.
-- Contas bancárias         ### Caixas e contas usadas nos recebimentos e pagamentos.
-- Dispositivos de acesso   ### Catracas e leitores vinculados às unidades.
-- Usuários                 ### Cadastros únicos e vínculos com redes e unidades.
-- Perfis                   ### Perfis de sistema e perfis personalizados.
-- Permissões               ### Catálogo modulo.acao. Alterações entram por seed ou migration.
-- Vínculos                 ### Perfis atribuídos com escopo de rede, unidade ou região.
-- Auditoria                ### Últimas ações administrativas registradas.

## Cadastros
-- Pessoas                  ### Cadastro unificado de alunos, responsáveis, funcionários e fornecedores
-- Modalidades              (**Atenção! Esse módulo não foi encontrado no menu. Criá-lo antes**)
-- Planos                   ### Dados do plano e modalidades incluídas.
-- Alunos                   ### Alunos vinculados à rede e à unidade.
-- Matrícula                ### Matrículas ativas, suspensas e encerradas.
-- Turmas                   ### Turmas, capacidade e horários por unidade.

## Financeiro;
-- Conciliação bancária     ### Importe o extrato e concilie com os movimentos de caixa.
-- Contas a pagar           ### Despesas por competência, fornecedor e centro de custo.
-- Cobranças                ### Mensalidades e cobranças avulsas por aluno.
-- Pagamentos               ### Recebimentos confirmados e estornos.


## Administração
-- Eventos de acesso        ### Entradas, saídas e tentativas registradas pelas catracas.
-- Contratos                ### Contratos gerados pelas matrículas.
-- Reservas                 ### Reservas de horários de turma por aluno.
-- Frequência

