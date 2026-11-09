<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\UnidadeRepository;
use App\Repositories\UsuarioRepository;
use App\Repositories\VinculoRepository;
use App\Validators\Validator;
use RuntimeException;

final class UsuarioService
{
    private const STATUSES = ['ATIVO', 'INATIVO'];

    public function __construct(
        private readonly UsuarioRepository $usuarios,
        private readonly VinculoRepository $vinculos,
        private readonly UnidadeRepository $unidades,
        private readonly AuthorizationService $authorization
    ) {
    }

    public function assertCanManage(
        int $actorId,
        int $targetUserId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): void {
        if (!$this->vinculos->userInDirectScope($targetUserId, $redeId, $unidadeId, $regiaoId)) {
            throw new ForbiddenException('Usuário fora do escopo direto da sua unidade.');
        }

        $actorLevel = $this->authorization->highestLevelForContext($actorId, $redeId, $unidadeId, $regiaoId);
        $targetLevel = $this->authorization->highestLevelForContext($targetUserId, $redeId, $unidadeId, $regiaoId);

        if ($targetLevel > $actorLevel) {
            throw new ForbiddenException('Não é permitido gerenciar um usuário de nível superior ao seu.');
        }
    }

    public function createWithMembership(
        array $data,
        ?int $redeId = null,
        ?int $unidadeId = null
    ): int {
        $normalized = $this->normalize($data);
        $this->validate($normalized, true);

        if ($this->usuarios->findByEmail($normalized['email']) !== null) {
            throw new ValidationException(['email' => ['Já existe um usuário com este e-mail.']]);
        }

        if ($unidadeId !== null) {
            $unidade = $this->unidades->findById($unidadeId);

            if ($unidade === null) {
                throw new ValidationException(['unidade_id' => ['Unidade inexistente.']]);
            }

            if ($redeId !== null && (int) $unidade['rede_id'] !== $redeId) {
                throw new ValidationException(['unidade_id' => ['A unidade não pertence à rede informada.']]);
            }

            $redeId = (int) $unidade['rede_id'];
        }

        $this->assertPassword($normalized['password']);

        $id = $this->usuarios->create(
            $normalized['nome'],
            $normalized['email'],
            password_hash($normalized['password'], PASSWORD_DEFAULT),
            $normalized['status']
        );

        if ($redeId !== null) {
            $this->vinculos->linkRede($id, $redeId);
        }

        if ($unidadeId !== null) {
            $this->vinculos->linkUnidade($id, $unidadeId);
        }

        return $id;
    }

    public function updateValidated(int $id, array $data): void
    {
        $normalized = $this->normalize($data);
        $this->validate($normalized, false);

        $existing = $this->usuarios->findByEmail($normalized['email']);

        if ($existing !== null && (int) $existing['id'] !== $id) {
            throw new ValidationException(['email' => ['Já existe um usuário com este e-mail.']]);
        }

        $this->usuarios->update($id, $normalized['nome'], $normalized['email'], $normalized['status']);
    }

    public function create(string $nome, string $email, string $password, string $status = 'ATIVO'): int
    {
        $email = mb_strtolower(trim($email));

        if ($this->usuarios->findByEmail($email) !== null) {
            throw new RuntimeException('Já existe um usuário com este e-mail.');
        }

        $this->assertPassword($password);

        return $this->usuarios->create(
            trim($nome),
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $status
        );
    }

    public function update(int $id, string $nome, string $email, string $status = 'ATIVO'): void
    {
        $email = mb_strtolower(trim($email));
        $existing = $this->usuarios->findByEmail($email);

        if ($existing !== null && (int) $existing['id'] !== $id) {
            throw new RuntimeException('Já existe um usuário com este e-mail.');
        }

        $this->usuarios->update($id, trim($nome), $email, $status);
    }

    public function changePassword(int $id, string $password): void
    {
        $this->assertPassword($password);
        $this->usuarios->updatePassword($id, password_hash($password, PASSWORD_DEFAULT));
    }

    private function normalize(array $data): array
    {
        return [
            'nome' => trim((string) ($data['nome'] ?? '')),
            'email' => mb_strtolower(trim((string) ($data['email'] ?? ''))),
            'password' => (string) ($data['password'] ?? ''),
            'status' => strtoupper(trim((string) ($data['status'] ?? 'ATIVO'))),
        ];
    }

    private function validate(array $data, bool $requirePassword): void
    {
        $validator = (new Validator($data))
            ->required('nome')
            ->maxLength('nome', 180)
            ->required('email')
            ->email('email')
            ->maxLength('email', 180)
            ->in('status', self::STATUSES);

        if ($requirePassword) {
            $validator->required('password');
        }

        $errors = $validator->errors();

        if ($requirePassword && $data['password'] !== '' && mb_strlen($data['password']) < 8) {
            $errors['password'][] = 'A senha deve ter ao menos 8 caracteres.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }

    private function assertPassword(string $password): void
    {
        if (mb_strlen($password) < 8) {
            throw new RuntimeException('A senha deve ter ao menos 8 caracteres.');
        }
    }
}
