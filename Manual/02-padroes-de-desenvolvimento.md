# Padrões de desenvolvimento

## 1. Nomenclatura

| Elemento | Convenção | Exemplo |
| --- | --- | --- |
| Classes | `PascalCase` | `AlunoService` |
| Métodos e variáveis | `camelCase` | `findByRede()` |
| Constantes | `UPPER_SNAKE_CASE` | `MAX_TENTATIVAS` |
| Tabelas e colunas | `snake_case` | `rede_id` |
| Arquivos de classe | mesmo nome da classe | `AlunoService.php` |
| Controllers | sufixo `Controller` | `AlunoController` |
| Services | sufixo `Service` | `MatriculaService` |
| Repositories | sufixo `Repository` | `PagamentoRepository` |

## 2. Separação de responsabilidades

```text
Controller → Service → Repository → Banco
     │
     └→ View (HTML) ou JSON
```

### Controllers

- Recebem parâmetros da requisição.
- Fazem validação básica de formato.
- Acionam Services.
- Retornam `Response` (HTML, JSON ou redirect).
- Não contêm SQL, cálculos financeiros ou regras de negócio complexas.

### Services

- Concentram regras de negócio e orquestração.
- Recebem dependências pelo construtor.
- Não conhecem detalhes de HTTP.
- Devem ser testáveis isoladamente.

### Repositories

- Concentram consultas e persistência.
- Não contêm regras de negócio.
- Aplicam o escopo da rede em consultas empresariais.
- Usam prepared statements.

### Views

- Exibem apenas os dados recebidos.
- Escapam todo valor dinâmico com `e()`.
- Não acessam banco nem executam regras de negócio.
- Consomem componentes e tokens CSS reutilizáveis.

## 3. Injeção de dependência

Serviços são resolvidos pelo `App\Support\Container`, que instancia classes por
autowiring de construtor. Dependências compartilhadas são registradas como
singletons em `bootstrap/app.php`.

```php
final class AlunoController extends Controller
{
    public function __construct(View $view, private readonly AlunoService $alunos)
    {
        parent::__construct($view);
    }
}
```

## 4. Respostas da API

Sucesso:

```json
{ "success": true, "message": "Operação realizada com sucesso.", "data": {} }
```

Erro:

```json
{ "success": false, "message": "Não foi possível realizar a operação.", "errors": {} }
```

Códigos HTTP utilizados: `200`, `201`, `204`, `400`, `401`, `403`, `404`,
`409`, `422`, `429` e `500`. Detalhes internos de exceção só aparecem quando
`APP_DEBUG=true`.

## 5. Segurança

- Toda entrada é validada no backend, independentemente do JavaScript.
- Toda operação protegida exige autorização.
- Escapar saída HTML com `e()`.
- Nunca concatenar entrada do usuário em SQL.
- Não registrar senhas, tokens ou dados financeiros sensíveis.
- CSRF obrigatório em requisições de estado das rotas web.
- Cookies de sessão `HttpOnly`, `SameSite` e `Secure` em HTTPS.
- Arquivos privados ficam em `storage/`, fora de `public/`.

## 6. Identidade visual

- Componentes usam apenas CSS Custom Properties.
- Cores institucionais não são fixadas nos componentes.
- Valores de marca recebidos do banco/ambiente passam por
  `App\Support\ThemeCompiler`, que aceita somente hexadecimal.
- Nunca permitir CSS ou JavaScript arbitrário fornecido pelo usuário.

## 7. Testes

- `tests/Unit`: regras isoladas, sem banco.
- `tests/Integration`: banco real; ignorados quando indisponível.
- Nomes descritivos em inglês, seguindo o padrão `testIt...`.
- Executar `composer test` antes de concluir alterações.

## 8. Formatação e análise

```bash
composer format
composer test
```

O padrão é PSR-12, com `declare(strict_types=1)`, arrays curtos e imports
ordenados (`.php-cs-fixer.dist.php`).

## 9. Versionamento

- `composer.lock` é versionado.
- `.env` nunca é versionado.
- `.env.example` é atualizado sempre que uma variável nova é necessária.
- Alterações estruturais relevantes são registradas em
  `Manual/decisoes-tecnicas/`.
