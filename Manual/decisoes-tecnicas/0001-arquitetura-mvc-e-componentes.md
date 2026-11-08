# 0001 — MVC com Services, Repositories e container próprio

**Data:** 2026-11-08
**Status:** aceita

## Contexto

A fundação precisava de uma organização clara, sem introduzir um framework
completo ou dependências estruturais desnecessárias. O documento de referência
define MVC, Clean Code, injeção de dependência e acesso a dados isolado.

## Decisão

- `public/index.php` atua como Front Controller.
- `bootstrap/app.php` monta configuração, logger, container, sessão, roteador e
  tratamento de erros.
- `App\Support\Application` despacha requisições, aplica middleware e converte
  exceções em respostas.
- Rotas usam `nikic/fast-route`.
- Dependências são resolvidas por um container de autowiring simples
  (`App\Support\Container`), registrado no bootstrap.
- Regras de negócio pertencem a Services; persistência pertence a Repositories.
- Controllers apenas coordenam e retornam `Response`.
- Views não acessam banco e escapam toda saída.

## Consequências

- Baixo acoplamento a frameworks e facilidade de leitura.
- O container é intencionalmente simples; recursos avançados (escopos,
  decorators, compilação de container) não fazem parte desta etapa.
- Services e Repositories ainda serão criados conforme os módulos evoluírem.
- A estrutura permite substituir componentes sem reescrever os Controllers.
