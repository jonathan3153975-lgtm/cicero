# Menus e submenus

**Documento de referência:** `projeto/4 - Menus e submenus.md`
**Status:** implementado e validado
**Data:** 2026-11-09

## 1. Objetivo

Agrupar a navegação administrativa em menu e submenu, manter a disponibilidade
por perfil, ampliar a largura da barra lateral e torná-la recolhível no desktop e
fechada por padrão no mobile.

## 2. Grupos implementados

| Grupo | Itens |
| --- | --- |
| Config. estrutura | Redes, Regiões, Unidades, Contas bancárias, Dispositivos de acesso, Usuários, Perfis, Permissões, Vínculos, Auditoria |
| Cadastros | Pessoas, Modalidades, Planos, Alunos, Matrículas, Turmas |
| Financeiro | Conciliação bancária, Contas a pagar, Cobranças, Pagamentos |
| Administração | Eventos de acesso, Contratos, Reservas, Frequência |

Cada grupo e item continua condicionado às permissões do perfil. Grupos sem
itens visíveis não são renderizados.

## 3. Comportamento da barra lateral

- Largura ampliada para `328px` no desktop.
- Desktop: aberta por padrão, recolhível pelo botão do cabeçalho ou pelo botão
  interno da barra; a preferência é salva em `localStorage`.
- Mobile: inicia fechada; o botão no topo abre a barra.
- Mobile: ao abrir, a barra ocupa `90vw` da tela, com overlay para fechar.
- `Esc` fecha a barra no mobile e o overlay também.
- Submenus são expansíveis individualmente; o grupo da página atual abre
  automaticamente.
- Estado dos submenus é salvo em `localStorage`.

## 4. Módulo Modalidades

Foi criado o cadastro que faltava no menu:

- Listagem com nome, descrição, abrangência e status.
- Criação e edição com validação de nome único por rede/global.
- Modalidades globais são somente leitura para administradores de rede.
- Rotas: `/admin/modalidades`, `/admin/modalidades/nova`,
  `/admin/modalidades/{id}/editar`.
- Permissões: `modalidade.visualizar`, `modalidade.criar` e
  `modalidade.editar`.

## 5. Permissões

O catálogo passou de 72 para **75 permissões**.

- Admin Regional, Admin Local e Gerente: criar/editar/visualizar modalidades.
- Diretor e Recepção: visualizar modalidades.
- Demais perfis: sem acesso ao módulo.

## 6. Arquivos alterados

- `resources/views/layouts/partials/sidebar.php`
- `public/assets/css/layouts/shell.css`
- `public/assets/css/themes/theme-default.css`
- `public/assets/js/modules/nav.js`
- `app/Controllers/Admin/ModalidadeController.php`
- `app/Services/ModalidadeService.php`
- `app/Repositories/ModalidadeRepository.php`
- `resources/views/admin/modalidades/`
- `routes/web.php`
- `database/seeds/data/permissions.php`
- `database/seeds/data/profiles.php`
- `app/Controllers/Admin/AdminDashboardController.php`
- `resources/views/admin/index.php`

## 7. Testes

- Criação e listagem de modalidade por rede.
- Bloqueio de nome duplicado.
- Bloqueio de edição de modalidade global por administrador de rede.
- Fluxo web validado com login, contexto e acesso às telas.
- Suíte atual: **71 testes e 541 asserções aprovados**.

## 8. Pendências

- Recolher a barra para um trilho de ícones em vez de ocultá-la por completo,
  caso o produto prefira esse comportamento.
- Revisar a ordem dos grupos com os usuários-chave.
- Adicionar atalhos de teclado para expandir/recolher grupos.
