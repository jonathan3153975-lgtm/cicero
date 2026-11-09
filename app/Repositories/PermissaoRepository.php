<?php

declare(strict_types=1);

namespace App\Repositories;

final class PermissaoRepository extends Repository
{
    public function forProfile(int $perfilId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT p.slug FROM permissoes p'
            . ' INNER JOIN perfil_permissao pp ON pp.permissao_id = p.id'
            . ' WHERE pp.perfil_id = :perfil_id ORDER BY p.slug ASC'
        );
        $statement->execute(['perfil_id' => $perfilId]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function all(): array
    {
        $statement = $this->pdo()->query(
            'SELECT id, slug, modulo, acao, descricao FROM permissoes ORDER BY modulo ASC, acao ASC'
        );

        return $statement->fetchAll();
    }

    public function idsBySlugs(array $slugs): array
    {
        $slugs = array_values(array_unique(array_filter($slugs, static fn ($slug): bool => is_string($slug) && $slug !== '')));

        if ($slugs === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($slugs), '?'));
        $statement = $this->pdo()->prepare('SELECT id, slug FROM permissoes WHERE slug IN (' . $placeholders . ')');
        $statement->execute($slugs);

        $map = [];

        foreach ($statement->fetchAll() as $row) {
            $map[(string) $row['slug']] = (int) $row['id'];
        }

        return $map;
    }
}
