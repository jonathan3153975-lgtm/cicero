<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditoriaRepository;

final class AuditService
{
    public function __construct(private readonly AuditoriaRepository $repository)
    {
    }

    public function record(
        ?int $usuarioId,
        string $acao,
        string $entidade,
        ?int $entidadeId = null,
        ?array $dadosAnteriores = null,
        ?array $dadosPosteriores = null,
        ?int $redeId = null,
        ?int $unidadeId = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): void {
        $this->repository->record([
            'usuario_id' => $usuarioId,
            'rede_id' => $redeId,
            'unidade_id' => $unidadeId,
            'acao' => $acao,
            'entidade' => $entidade,
            'entidade_id' => $entidadeId,
            'dados_anteriores' => $dadosAnteriores,
            'dados_posteriores' => $dadosPosteriores,
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);
    }
}
