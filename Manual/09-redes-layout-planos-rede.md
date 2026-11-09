# Redes — layout, máscaras e planos de rede

**Status:** implementado e validado
**Data:** 2026-11-09

## 1. Formulário de Redes

O formulário passou a usar linhas e colunas responsivas com classes no padrão
Bootstrap (`col-sm-12`, `col-md-*`), implementadas no CSS do projeto.

| Linha | Campos | Colunas |
| --- | --- | --- |
| 1 | Razão social / Nome fantasia | `col-sm-12 col-md-6` cada |
| 2 | CNPJ / E-mail / Telefone | `col-sm-12 col-md-4` cada |
| 3 | CEP / Logradouro / Número | `col-md-3` / `col-md-6` / `col-md-3` |
| 4 | Complemento / Bairro / Cidade / Estado | `md-2` / `md-4` / `md-4` / `md-2` |
| 5 | Plano contratado / Status | `col-sm-12 col-md-6` cada |

O complemento passou a ser editável no início da quarta linha.

## 2. Máscaras, validação e CEP

- CNPJ: máscara `00.000.000/0000-00`, validação de dígitos no cliente e no
  servidor.
- Telefone: máscara para fixo e celular.
- CEP: máscara `00000-000`, validação de 8 dígitos e autopreenchimento de
  logradouro, bairro, cidade e estado via ViaCEP.
- E-mail: validação nativa (`type=email`) e validação do servidor.
- Estado: limite de 2 caracteres.

## 3. Planos de rede

Novo módulo exclusivo do superadministrador:

- Tabela `planos_rede`: nome, descrição, status e auditoria básica.
- `redes.plano_rede_id` adicionado com FK para `planos_rede`.
- Select de plano contratado no formulário de Redes.
- CRUD em `/admin/planos-rede`.
- Descrição com editor de texto rico leve (negrito, itálico, listas, título e
  limpeza de formatação).
- HTML sanitizado no servidor com allowlist de tags.
- Permissões: `plano_rede.visualizar`, `plano_rede.criar` e
  `plano_rede.editar`, disponíveis apenas ao Superadmin.

## 4. Seeds

Foram criados os planos DEMO, Básico, Profissional e Enterprise. Redes antigas
com `plano_contratado` textual são vinculadas automaticamente ao plano de mesmo
nome quando existir.

## 5. Arquivos alterados

- `database/migrations/0032_create_planos_rede_table.php`
- `app/Support/HtmlSanitizer.php`
- `app/Repositories/PlanoRedeRepository.php`
- `app/Services/PlanoRedeService.php`
- `app/Controllers/Admin/PlanoRedeController.php`
- `app/Repositories/RedeRepository.php`
- `app/Services/RedeService.php`
- `app/Controllers/Admin/RedeController.php`
- `resources/views/admin/planos-rede/`
- `resources/views/admin/redes/form.php`
- `resources/views/admin/redes/index.php`
- `public/assets/js/components/input-masks.js`
- `public/assets/js/components/rich-text.js`
- `public/assets/js/components/loading-overlay.js`
- `resources/views/layouts/partials/loading.php`
- `public/assets/css/components/forms.css`
- `public/assets/css/components/loading.css`
- `routes/web.php`
- `database/seeds/data/permissions.php`
- `app/Support/Seeder.php`

## 6. Testes

- Criação de plano de rede com descrição sanitizada.
- Bloqueio de nome duplicado.
- Vínculo de plano contratado à rede.
- Validação de CEP inválido.
- Catálogo atualizado para **78 permissões**.
- Suíte atual: **73 testes e 552 asserções aprovados**.

## 7. Tela de carregamento

Foi adicionada uma camada global de bloqueio com spinner central:

- Exibida ao enviar qualquer formulário, com mensagem "Processando..." (ou definida em `data-loading-message`).
- Exibida ao navegar por links internos, com mensagem "Carregando...".
- Bloqueia cliques repetidos enquanto o envio/navegação está em andamento.
- O formulário marca o envio e ignora um segundo submit.
- Botões de envio recebem ria-disabled e feedback visual.
- A camada é escondida em pageshow (inclusive ao voltar do cache do navegador).
- Formulários podem optar por não usar a camada com data-no-loading.