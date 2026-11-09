# Unidades — layout e vínculos

**Status:** implementado e validado
**Data:** 2026-11-09

## 1. Formulário de Unidades

O formulário usa linhas e colunas responsivas no padrão do formulário de Redes.

| Linha | Campos | Colunas |
| --- | --- | --- |
| 1 | Rede / Região | `col-sm-12 col-md-6` cada |
| 2 | Nome / Código interno / Ativo | `md-6` / `md-3` / `md-3` |
| 3 | CEP / Logradouro / Número | `md-3` / `md-6` / `md-3` |
| 4 | Complemento / Bairro / Cidade / Estado | `md-2` / `md-4` / `md-4` / `md-2` |
| 5 | Telefone / WhatsApp / E-mail | `col-sm-12 col-md-4` cada |
| 6 | Horário / Capacidade / Metragem / Data de abertura | `col-sm-12 col-md-3` cada |

## 2. Rede e região

- O select de Rede retorna as redes disponíveis para o contexto.
- O select de Região é filtrado pela rede selecionada.
- A região é opcional; sem seleção, a unidade fica vinculada apenas à rede.
- A região escolhida precisa pertencer à rede da unidade.
- Ao salvar, o vínculo `regiao_unidade` é sincronizado com a região escolhida.
- O formulário de edição carrega a região atualmente vinculada.

## 3. Código interno e status

- O código interno é gerado automaticamente a partir do nome da unidade.
- A geração remove acentos, normaliza para maiúsculas e adiciona sufixo numérico
  em caso de duplicidade na mesma rede.
- O campo é somente leitura no formulário.
- O status da unidade é controlado pelo checkbox **Ativo**
  (`ATIVA`/`INATIVA`).

## 4. Endereço e contato

- CEP com máscara, validação de 8 dígitos e autopreenchimento via ViaCEP.
- Telefone e WhatsApp com máscara.
- E-mail validado no cliente e no servidor.
- Complemento, bairro, cidade e estado seguem o padrão de Redes.

## 5. Arquivos alterados

- `app/Controllers/Admin/UnidadeController.php`
- `app/Services/UnidadeService.php`
- `app/Repositories/RegiaoRepository.php`
- `resources/views/admin/unidades/form.php`
- `public/assets/js/components/dependent-selects.js`
- `public/assets/js/app.js`
- `Manual/10-unidade-layout.md`

## 6. Testes

- Fluxo web validado com criação de unidade, código automático e vínculo de
  região.
- CEP inválido e região fora da rede são bloqueados.
- Suíte atual: **73 testes e 552 asserções aprovados**.
