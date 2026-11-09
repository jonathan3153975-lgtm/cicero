# Onda 1 — Pessoas e alunos

**Documento de referência:** `projeto/3 - Banco de dados e modelagem do domínio.md`
**Status:** implementada e validada
**Data:** 2026-11-09

## 1. Objetivo

Implementar a base de pessoas, alunos, responsáveis, contatos, endereços,
documentos, consentimentos e o vínculo dos usuários existentes com pessoas.

## 2. Migrations aplicadas

| Migration | Conteúdo |
| --- | --- |
| `0017_create_arquivos_table.php` | `arquivos` |
| `0018_create_pessoas_table.php` | `pessoas` |
| `0019_create_pessoa_detalhes_tables.php` | `pessoa_enderecos`, `pessoa_contatos`, `pessoa_documentos` |
| `0020_create_consentimentos_table.php` | `consentimentos` |
| `0021_create_alunos_tables.php` | `alunos`, `aluno_responsaveis`, `aluno_restricoes_saude` |
| `0022_add_pessoa_id_to_usuarios_table.php` | vínculo `usuarios.pessoa_id` + backfill |

## 3. Modelo aplicado

- `pessoas` unificada para PF/PJ, com `rede_id`, `public_id`, documentos,
  contato principal, endereço principal e status.
- Especialização `alunos` com vínculo único por pessoa.
- `aluno_responsaveis` com parentesco, responsável financeiro e autorização de
  busca.
- `aluno_restricoes_saude` criada para a etapa de saúde; sem tela nesta onda.
- `arquivos` centraliza metadados; `private_local` será usado para arquivos
  personalíssimos, `server` para os demais.
- `consentimentos` preparado para LGPD.
- Os usuários existentes foram convertidos em pessoas e vinculados por
  `usuarios.pessoa_id`.

## 4. Permissões

Novos módulos:

- `pessoa.*`
- `aluno.*`
- `responsavel.*`
- `lgpd.consentimento.*`
- `saude.*` (reservado para a etapa de saúde)

Perfis atualizados:

- Admin Regional e Admin Local: pessoa, aluno, responsável e consentimentos.
- Gerente e Recepção: pessoa, aluno e responsável conforme atuação.
- Diretor: leitura de pessoa, aluno e responsável.
- Funcionário e Contabilidade: sem acesso padrão.
- Superadmin: acesso total.

O catálogo passou de 36 para **72 permissões**.

## 5. Rotas

| Método | Caminho | Permissão |
| --- | --- | --- |
| GET | `/admin/pessoas` | `pessoa.visualizar` |
| GET/POST | `/admin/pessoas/novo` | `pessoa.criar` |
| GET/POST | `/admin/pessoas/{id}/editar` | `pessoa.editar` |
| GET | `/admin/alunos` | `aluno.visualizar` |
| GET/POST | `/admin/alunos/novo` | `aluno.criar` |
| GET/POST | `/admin/alunos/{id}/editar` | `aluno.editar` |
| POST | `/admin/alunos/{id}/responsaveis` | `responsavel.criar` |
| POST | `/admin/alunos/{id}/responsaveis/{id}/remover` | `responsavel.editar` |

## 6. Serviços e regras

- `PessoaService`: validação de PF/PJ, CPF/CNPJ, unicidade, contato e endereço
  principal, escopo e acesso.
- `AlunoService`: criação de aluno com pessoa nova ou existente, responsáveis,
  validação de unidade/rede/região, escopo e unicidade de aluno por pessoa.
- `PessoaRepository`, `AlunoRepository`, `AlunoResponsavelRepository`,
  `PessoaContatoRepository`, `PessoaEnderecoRepository` e
  `ConsentimentoRepository`.
- O escopo de unidade considera alunos e responsáveis da unidade, além de
  pessoas criadas pelo próprio usuário.
- O escopo regional considera as unidades da região.
- O escopo de rede considera a rede e vínculos de alunos.
- Nenhuma exclusão física de pessoa/aluno; status é utilizado.

## 7. Interface

- Tela de pessoas com busca, criação e edição.
- Tela de alunos com busca, criação, edição, dados da pessoa e responsáveis.
- Menu lateral e dashboard administrativo com atalhos para Pessoas e Alunos.
- Pessoa nova pode ser criada diretamente ou junto do aluno.
- Responsável pode ser uma pessoa existente do escopo ou uma nova pessoa.

## 8. Testes

- Backfill de usuários para pessoas.
- Criação/atualização de pessoa, CPF válido, CPF duplicado e CPF inválido.
- Escopo de pessoa por unidade.
- Criação de aluno com pessoa nova e responsável.
- Remoção lógica de responsável.
- Pessoa existente sem duplicar aluno.
- Isolamento de aluno entre unidades.
- Permissões de pessoa/aluno nos perfis operacionais.

Situação atual: **65 testes e 493 asserções aprovados**.

## 9. Pendências da Onda 1

- Upload/tela de documentos e arquivos.
- Tela de consentimentos LGPD.
- Tela de restrições de saúde e autorização de acesso.
- Dados de demonstração de alunos (opcional).
- Evoluir para a Onda 2 (catálogo, planos, contratos e matrículas).

## 10. Comandos

```bash
composer migrate
composer seed
composer test
composer serve
```
