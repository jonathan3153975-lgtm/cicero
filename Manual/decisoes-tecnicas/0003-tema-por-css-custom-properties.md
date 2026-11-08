# 0003 — Tema por CSS Custom Properties

**Data:** 2026-11-08
**Status:** aceita

## Contexto

A identidade visual precisa ser responsiva e configurável por empresa, sem
duplicar componentes e sem permitir que o usuário injete CSS ou JavaScript
arbitrário.

## Decisão

- Os tokens ficam em `public/assets/css/themes/theme-default.css` e
  `theme-light.css`.
- Componentes consomem apenas variáveis (`var(--brand-primary)`, etc.).
- O backend expõe `App\Support\ThemeCompiler`, que:
  - aceita somente cores hexadecimais (`#RGB` ou `#RRGGBB`);
  - converte valores válidos em CSS Custom Properties;
  - ignora qualquer outro formato.
- A personalização atual é opcional via `BRAND_*` no `.env`.
- A troca de tema claro/escuro é feita por `data-theme` no elemento `html` e
  persistida em `localStorage`.

## Consequências

- Trocar as cores não exige alterar componentes.
- A futura persistência por rede (`redes` / `rede_temas`) pode reutilizar o
  compilador de tema.
- Nenhum valor recebido do usuário é inserido como CSS bruto.

## Pendência

Persistir a identidade visual por rede e aplicar o tema resolvido no backend
para cada requisição autenticada.
