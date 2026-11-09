# Onda 3 — Operação e controle de acesso

**Documento de referência:** `projeto/3 - Banco de dados e modelagem do domínio.md`
**Status:** implementada e validada
**Data:** 2026-11-09

## 1. Objetivo

Implementar turmas, horários, reservas, frequência, dispositivos de acesso,
eventos de acesso e a base de ocorrências.

## 2. Migrations aplicadas

| Migration | Conteúdo |
| --- | --- |
| `0026_create_turmas_tables.php` | `turmas`, `turma_horarios`, `turma_alunos` |
| `0027_create_reservas_frequencias_tables.php` | `reservas`, `frequencias` |
| `0028_create_acesso_ocorrencias_tables.php` | `dispositivos_acesso`, `eventos_acesso`, `ocorrencias` |

## 3. Modelo aplicado

- Turmas por rede/unidade, com modalidade, capacidade e sala.
- Horários por turma, dia da semana, início/fim e professor opcional.
- Vínculo turma ↔ matrícula (`turma_alunos`).
- Reservas por aluno, horário e data, com status.
- Frequência por aluno, turma, horário opcional, data, presença e origem.
- Dispositivos de acesso (catracas/leitores) por unidade.
- Eventos de acesso com pessoa/aluno, tipo, autorização e payload.
- Ocorrências por unidade, aluno/pessoa, tipo, descrição e status.

## 4. Regras de negócio

- Horários da mesma turma não podem se sobrepor.
- O professor não pode ter horários sobrepostos.
- Frequência exige aluno e turma da mesma unidade.
- Frequência não pode ser duplicada para o mesmo aluno/turma/data/horário.
- Reserva exige aluno e turma da mesma unidade.
- A data da reserva deve corresponder ao dia da semana do horário.
- Reserva duplicada ativa é bloqueada.
- Capacidade da turma limita reservas confirmadas na data.
- Eventos de acesso exigem dispositivo, pessoa/aluno e vínculo de unidade.
- Escopo por rede, unidade e região em todas as operações.

## 5. Permissões

Novos módulos:

- `turma.*`
- `frequencia.*`
- `reserva.*`
- `dispositivo.*`
- `evento_acesso.*`
- `ocorrencia.*`

Perfis:

- Admin Regional/Local: operação completa.
- Gerente: turmas, frequência, reservas, dispositivos e ocorrências.
- Recepção: turmas (leitura), frequência, reservas e eventos.
- Diretor: leitura de operação.
- Funcionário: turmas (leitura), frequência e reservas (leitura/registro).
- Superadmin: acesso total.

Total atual: **72 permissões**.

## 6. Rotas

| Método | Caminho | Permissão |
| --- | --- | --- |
| GET | `/admin/turmas` | `turma.visualizar` |
| GET/POST | `/admin/turmas/novo` | `turma.criar` |
| GET/POST | `/admin/turmas/{id}/editar` | `turma.editar` |
| GET/POST | `/admin/turmas/{id}/horarios` | `turma.editar` |
| POST | `/admin/turmas/{id}/horarios/{horarioId}/remover` | `turma.editar` |
| GET | `/admin/frequencias` | `frequencia.visualizar` |
| GET/POST | `/admin/frequencias/registrar` | `frequencia.registrar` |
| GET | `/admin/reservas` | `reserva.visualizar` |
| GET/POST | `/admin/reservas/nova` | `reserva.criar` |
| POST | `/admin/reservas/{id}/cancelar` | `reserva.cancelar` |
| GET | `/admin/dispositivos` | `dispositivo.visualizar` |
| GET/POST | `/admin/dispositivos/novo` | `dispositivo.gerenciar` |
| GET/POST | `/admin/dispositivos/{id}/editar` | `dispositivo.gerenciar` |
| GET | `/admin/acessos` | `evento_acesso.visualizar` |

## 7. Serviços e repositórios

- `TurmaService`, `FrequenciaService`, `ReservaService`, `AcessoService`.
- `TurmaRepository`, `TurmaHorarioRepository`, `FrequenciaRepository`,
  `ReservaRepository`, `DispositivoAcessoRepository`, `EventoAcessoRepository`.
- Transações e validações de escopo, conflito de horário, duplicidade,
  capacidade e dia da semana.

## 8. Interface

- Tela de turmas com criação/edição.
- Tela de horários por turma com inclusão/remoção.
- Tela de frequência com registro e listagem.
- Tela de reservas com criação, listagem e cancelamento.
- Tela de dispositivos de acesso com criação/edição.
- Tela de eventos de acesso.
- Menu lateral e dashboard com atalhos de operação.

## 9. Testes

- Turma com horário e bloqueio de sobreposição.
- Turma de outra unidade inacessível.
- Frequência registrada e duplicidade bloqueada.
- Reserva com duplicidade e capacidade controladas.
- Dispositivo e evento de acesso por unidade.

Situação atual: **65 testes e 493 asserções aprovados**.

## 10. Pendências da Onda 3

- Tela de vínculo `turma_alunos`.
- Associação visual de professor ao horário.
- Tela de ocorrências.
- Registro manual de eventos de acesso pela interface.
- Integração real com catracas e dispositivos.
- Utilização da catraca como fonte de ponto de funcionários (Onda 6).

## 11. Próximo passo

Onda 4 — financeiro: cobranças, pagamentos, contas a pagar, conciliação e
integração com matrículas/contratos.
