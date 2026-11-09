<?php

declare(strict_types=1);

namespace App\Support;

use PDO;
use RuntimeException;
use Throwable;

final class Seeder
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $dataPath
    ) {
    }

    public function run(): array
    {
        $permissions = require $this->dataPath . '/permissions.php';
        $profiles = require $this->dataPath . '/profiles.php';
        $demo = require $this->dataPath . '/demo.php';

        $this->seedPermissions($permissions);
        $this->seedProfiles($profiles);
        $this->seedDemo($demo);
        $this->seedModalities();
        $this->seedFinanceiro();
        $this->seedRedePlans();

        return [
            'permissoes' => count($permissions),
            'perfis' => count($profiles),
            'demo' => 'ok',
        ];
    }

    private function seedPermissions(array $permissions): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO permissoes (slug, modulo, acao, descricao)'
            . ' VALUES (:slug, :modulo, :acao, :descricao)'
            . ' ON DUPLICATE KEY UPDATE modulo = VALUES(modulo), acao = VALUES(acao), descricao = VALUES(descricao)'
        );

        foreach ($permissions as $permission) {
            $statement->execute([
                'slug' => $permission['slug'],
                'modulo' => $permission['modulo'],
                'acao' => $permission['acao'],
                'descricao' => $permission['descricao'] ?? null,
            ]);
        }
    }

    private function seedProfiles(array $profiles): void
    {
        $upsert = $this->pdo->prepare(
            'INSERT INTO perfis (rede_id, slug, nome, nivel, escopo, sistema, status)'
            . ' VALUES (NULL, :slug, :nome, :nivel, :escopo, :sistema, \'ATIVO\')'
            . ' ON DUPLICATE KEY UPDATE rede_id = NULL, nome = VALUES(nome), nivel = VALUES(nivel),'
            . ' escopo = VALUES(escopo), sistema = VALUES(sistema), status = \'ATIVO\''
        );

        $allPermissions = $this->pdo->query('SELECT id, slug FROM permissoes')->fetchAll(PDO::FETCH_KEY_PAIR);
        $permissionIdsBySlug = array_flip($allPermissions);
        $allPermissionIds = array_keys($allPermissions);

        $clear = $this->pdo->prepare('DELETE FROM perfil_permissao WHERE perfil_id = :perfil_id');
        $link = $this->pdo->prepare(
            'INSERT INTO perfil_permissao (perfil_id, permissao_id) VALUES (:perfil_id, :permissao_id)'
        );

        foreach ($profiles as $profile) {
            $upsert->execute([
                'slug' => $profile['slug'],
                'nome' => $profile['nome'],
                'nivel' => $profile['nivel'],
                'escopo' => $profile['escopo'],
                'sistema' => $profile['sistema'] ? 1 : 0,
            ]);

            $profileId = $this->idBy(
                'SELECT id FROM perfis WHERE slug = :slug',
                ['slug' => $profile['slug']]
            );

            $clear->execute(['perfil_id' => $profileId]);

            $slugs = $profile['permissoes'];

            if (in_array('*', $slugs, true)) {
                $permissionIds = $allPermissionIds;
            } else {
                $permissionIds = [];

                foreach ($slugs as $slug) {
                    if (!isset($permissionIdsBySlug[$slug])) {
                        throw new RuntimeException(sprintf('Permissão inexistente no seed: %s.', $slug));
                    }

                    $permissionIds[] = $permissionIdsBySlug[$slug];
                }
            }

            foreach (array_unique($permissionIds) as $permissionId) {
                $link->execute([
                    'perfil_id' => $profileId,
                    'permissao_id' => $permissionId,
                ]);
            }
        }
    }

    private function seedModalities(): void
    {
        $find = $this->pdo->prepare(
            'SELECT id FROM modalidades WHERE rede_id IS NULL AND nome = :nome LIMIT 1'
        );
        $insert = $this->pdo->prepare(
            'INSERT INTO modalidades (rede_id, nome, status) VALUES (NULL, :nome, \'ATIVO\')'
        );

        foreach (['Musculação', 'Funcional', 'Pilates', 'Cross Training', 'Hidroginástica'] as $nome) {
            $find->execute(['nome' => $nome]);

            if ($find->fetchColumn() === false) {
                $insert->execute(['nome' => $nome]);
            }
        }
    }


    private function seedFinanceiro(): void
    {
        $formas = [
            ['Dinheiro', 'DINHEIRO'],
            ['PIX', 'PIX'],
            ['Cartão de Crédito', 'CARTAO_CREDITO'],
            ['Cartão de Débito', 'CARTAO_DEBITO'],
            ['Boleto', 'BOLETO'],
            ['Transferência', 'TRANSFERENCIA'],
        ];

        $findForma = $this->pdo->prepare(
            'SELECT id FROM formas_pagamento WHERE rede_id IS NULL AND nome = :nome LIMIT 1'
        );
        $insertForma = $this->pdo->prepare(
            'INSERT INTO formas_pagamento (rede_id, nome, tipo, status) VALUES (NULL, :nome, :tipo, \'ATIVO\')'
        );

        foreach ($formas as [$nome, $tipo]) {
            $findForma->execute(['nome' => $nome]);

            if ($findForma->fetchColumn() === false) {
                $insertForma->execute(['nome' => $nome, 'tipo' => $tipo]);
            }
        }

        $unidades = $this->pdo->query('SELECT id, rede_id FROM unidades ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
        $insertConta = $this->pdo->prepare(
            'INSERT INTO contas_bancarias (rede_id, unidade_id, banco, agencia, conta, tipo, saldo_inicial, status)'
            . ' VALUES (:rede_id, :unidade_id, \'Caixa Interno\', \'0001\', \'0001\', \'CAIXA\', 0, \'ATIVO\')'
            . ' ON DUPLICATE KEY UPDATE status = \'ATIVO\''
        );
        $insertCentro = $this->pdo->prepare(
            'INSERT INTO centros_custo (rede_id, unidade_id, codigo, nome, status)'
            . ' VALUES (:rede_id, :unidade_id, \'GERAL\', \'Geral\', \'ATIVO\')'
            . ' ON DUPLICATE KEY UPDATE status = \'ATIVO\''
        );

        foreach ($unidades as $unidade) {
            $insertConta->execute([
                'rede_id' => (int) $unidade['rede_id'],
                'unidade_id' => (int) $unidade['id'],
            ]);
            $insertCentro->execute([
                'rede_id' => (int) $unidade['rede_id'],
                'unidade_id' => (int) $unidade['id'],
            ]);
        }
    }

    private function seedRedePlans(): void
    {
        $planos = [
            [
                'nome' => 'DEMO',
                'descricao' => '<p>Plano de demonstração para avaliação da plataforma.</p>',
            ],
            [
                'nome' => 'Básico',
                'descricao' => '<p>Gestão de uma unidade, cadastros essenciais, matrículas e financeiro básico.</p>',
            ],
            [
                'nome' => 'Profissional',
                'descricao' => '<p>Múltiplas unidades, turmas, frequência, controle de acesso e conciliação bancária.</p>',
            ],
            [
                'nome' => 'Enterprise',
                'descricao' => '<p>Rede completa com módulos avançados, relatórios e integrações.</p>',
            ],
        ];

        $insert = $this->pdo->prepare(
            'INSERT INTO planos_rede (nome, descricao, status) VALUES (:nome, :descricao, \'ATIVO\')'
            . ' ON DUPLICATE KEY UPDATE descricao = VALUES(descricao), status = \'ATIVO\''
        );

        foreach ($planos as $plano) {
            $insert->execute(['nome' => $plano['nome'], 'descricao' => $plano['descricao']]);
        }

        $this->pdo->exec(
            'UPDATE redes r SET r.plano_rede_id = ('
            . 'SELECT p.id FROM planos_rede p WHERE p.nome = r.plano_contratado LIMIT 1'
            . ') WHERE r.plano_rede_id IS NULL AND r.plano_contratado IS NOT NULL'
        );
    }

    private function seedDemo(array $demo): void
    {
        $passwordHash = password_hash((string) $demo['senha'], PASSWORD_DEFAULT);

        $this->pdo->beginTransaction();

        try {
            $redeId = $this->upsertRede($demo['rede']);
            $regiaoId = $this->upsertRegiao($redeId, $demo['regiao']);
            $unidadeIds = [];

            foreach ($demo['unidades'] as $unit) {
                $unidadeIds[$unit['codigo_interno']] = $this->upsertUnidade($redeId, $unit);
                $this->linkRegiaoUnidade($regiaoId, $unidadeIds[$unit['codigo_interno']]);
            }

            foreach ($demo['usuarios'] as $user) {
                $usuarioId = $this->upsertUsuario($user['email'], $user['nome'], $passwordHash);
                $perfilId = $this->idBy(
                    'SELECT id FROM perfis WHERE slug = :slug',
                    ['slug' => $user['perfil']]
                );

                $unidadeId = $user['unidade'] !== null
                    ? ($unidadeIds[$user['unidade']] ?? null)
                    : null;

                if ($user['unidade'] !== null && $unidadeId === null) {
                    throw new RuntimeException(sprintf('Unidade inexistente no seed: %s.', $user['unidade']));
                }

                $regiaoIdForUser = $user['regiao'] !== null ? $regiaoId : null;

                if ($user['rede'] === true) {
                    $this->linkUsuarioRede($usuarioId, $redeId);
                }

                if ($unidadeId !== null) {
                    $this->linkUsuarioUnidade($usuarioId, $unidadeId);
                }

                $this->upsertUsuarioPerfil(
                    $usuarioId,
                    $perfilId,
                    $user['rede'] === true ? $redeId : null,
                    $unidadeId,
                    $regiaoIdForUser
                );
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function upsertRede(array $rede): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO redes (razao_social, nome_fantasia, cnpj, email, telefone, cep, logradouro,'
            . ' numero, complemento, bairro, cidade, estado, status, plano_contratado)'
            . ' VALUES (:razao_social, :nome_fantasia, :cnpj, :email, :telefone, :cep, :logradouro,'
            . ' :numero, :complemento, :bairro, :cidade, :estado, :status, :plano_contratado)'
            . ' ON DUPLICATE KEY UPDATE nome_fantasia = VALUES(nome_fantasia), email = VALUES(email),'
            . ' telefone = VALUES(telefone), cidade = VALUES(cidade), estado = VALUES(estado),'
            . ' status = VALUES(status), plano_contratado = VALUES(plano_contratado)'
        );
        $statement->execute($rede);

        return $this->idBy('SELECT id FROM redes WHERE cnpj = :cnpj', ['cnpj' => $rede['cnpj']]);
    }

    private function upsertRegiao(int $redeId, array $regiao): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO regioes (rede_id, nome, status) VALUES (:rede_id, :nome, :status)'
            . ' ON DUPLICATE KEY UPDATE status = VALUES(status)'
        );
        $statement->execute([
            'rede_id' => $redeId,
            'nome' => $regiao['nome'],
            'status' => $regiao['status'] ?? 'ATIVO',
        ]);

        return $this->idBy(
            'SELECT id FROM regioes WHERE rede_id = :rede_id AND nome = :nome',
            ['rede_id' => $redeId, 'nome' => $regiao['nome']]
        );
    }

    private function upsertUnidade(int $redeId, array $unit): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO unidades (rede_id, nome, codigo_interno, situacao, cep, logradouro, numero,'
            . ' complemento, bairro, cidade, estado, telefone, whatsapp, email, capacidade_maxima, metragem)'
            . ' VALUES (:rede_id, :nome, :codigo_interno, :situacao, :cep, :logradouro, :numero,'
            . ' :complemento, :bairro, :cidade, :estado, :telefone, :whatsapp, :email,'
            . ' :capacidade_maxima, :metragem)'
            . ' ON DUPLICATE KEY UPDATE nome = VALUES(nome), situacao = VALUES(situacao),'
            . ' cidade = VALUES(cidade), estado = VALUES(estado), telefone = VALUES(telefone),'
            . ' whatsapp = VALUES(whatsapp), email = VALUES(email)'
        );
        $statement->execute([
            'rede_id' => $redeId,
            'nome' => $unit['nome'],
            'codigo_interno' => $unit['codigo_interno'],
            'situacao' => $unit['situacao'] ?? 'ATIVA',
            'cep' => $unit['cep'] ?? null,
            'logradouro' => $unit['logradouro'] ?? null,
            'numero' => $unit['numero'] ?? null,
            'complemento' => $unit['complemento'] ?? null,
            'bairro' => $unit['bairro'] ?? null,
            'cidade' => $unit['cidade'] ?? null,
            'estado' => $unit['estado'] ?? null,
            'telefone' => $unit['telefone'] ?? null,
            'whatsapp' => $unit['whatsapp'] ?? null,
            'email' => $unit['email'] ?? null,
            'capacidade_maxima' => $unit['capacidade_maxima'] ?? null,
            'metragem' => $unit['metragem'] ?? null,
        ]);

        return $this->idBy(
            'SELECT id FROM unidades WHERE rede_id = :rede_id AND codigo_interno = :codigo_interno',
            ['rede_id' => $redeId, 'codigo_interno' => $unit['codigo_interno']]
        );
    }

    private function upsertUsuario(string $email, string $nome, string $passwordHash): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, email, senha_hash, status) VALUES (:nome, :email, :senha_hash, \'ATIVO\')'
            . ' ON DUPLICATE KEY UPDATE nome = VALUES(nome), senha_hash = VALUES(senha_hash), status = \'ATIVO\''
        );
        $statement->execute([
            'nome' => $nome,
            'email' => $email,
            'senha_hash' => $passwordHash,
        ]);

        return $this->idBy('SELECT id FROM usuarios WHERE email = :email', ['email' => $email]);
    }

    private function linkRegiaoUnidade(int $regiaoId, int $unidadeId): void
    {
        $statement = $this->pdo->prepare(
            'INSERT IGNORE INTO regiao_unidade (regiao_id, unidade_id) VALUES (:regiao_id, :unidade_id)'
        );
        $statement->execute(['regiao_id' => $regiaoId, 'unidade_id' => $unidadeId]);
    }

    private function linkUsuarioRede(int $usuarioId, int $redeId): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO usuario_rede (usuario_id, rede_id, status) VALUES (:usuario_id, :rede_id, \'ATIVO\')'
            . ' ON DUPLICATE KEY UPDATE status = \'ATIVO\''
        );
        $statement->execute(['usuario_id' => $usuarioId, 'rede_id' => $redeId]);
    }

    private function linkUsuarioUnidade(int $usuarioId, int $unidadeId): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO usuario_unidade (usuario_id, unidade_id, status) VALUES (:usuario_id, :unidade_id, \'ATIVO\')'
            . ' ON DUPLICATE KEY UPDATE status = \'ATIVO\''
        );
        $statement->execute(['usuario_id' => $usuarioId, 'unidade_id' => $unidadeId]);
    }

    private function upsertUsuarioPerfil(
        int $usuarioId,
        int $perfilId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): void {
        $existingId = $this->idByNullable(
            'SELECT id FROM usuario_perfil WHERE usuario_id = :usuario_id AND perfil_id = :perfil_id'
            . ' AND rede_id <=> :rede_id AND unidade_id <=> :unidade_id AND regiao_id <=> :regiao_id LIMIT 1',
            [
                'usuario_id' => $usuarioId,
                'perfil_id' => $perfilId,
                'rede_id' => $redeId,
                'unidade_id' => $unidadeId,
                'regiao_id' => $regiaoId,
            ]
        );

        if ($existingId !== null) {
            $statement = $this->pdo->prepare(
                'UPDATE usuario_perfil SET status = \'ATIVO\' WHERE id = :id'
            );
            $statement->execute(['id' => $existingId]);

            return;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO usuario_perfil (usuario_id, perfil_id, rede_id, unidade_id, regiao_id, status)'
            . ' VALUES (:usuario_id, :perfil_id, :rede_id, :unidade_id, :regiao_id, \'ATIVO\')'
        );
        $statement->execute([
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfilId,
            'rede_id' => $redeId,
            'unidade_id' => $unidadeId,
            'regiao_id' => $regiaoId,
        ]);
    }

    private function idBy(string $sql, array $parameters): int
    {
        $id = $this->idByNullable($sql, $parameters);

        if ($id === null) {
            throw new RuntimeException('Registro não encontrado após o seed.');
        }

        return $id;
    }

    private function idByNullable(string $sql, array $parameters): ?int
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);
        $id = $statement->fetchColumn();

        return $id === false ? null : (int) $id;
    }
}
