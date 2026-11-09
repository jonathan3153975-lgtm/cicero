<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Exceptions\ValidationException;
use App\Repositories\PlanoRedeRepository;
use App\Repositories\RedeRepository;
use App\Services\PlanoRedeService;
use App\Services\RedeService;

final class PlanoRedeTest extends IntegrationTestCase
{
    private const PREFIX = 'TEST PLANO REDE ';
    private const CNPJ = '11222333000181';

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
    }

    public function testCriaPlanoComDescricaoSanitizada(): void
    {
        $service = $this->planoRedeService();
        $actorId = $this->userId('admin@local.test');

        $id = $service->create($actorId, [
            'nome' => self::PREFIX . 'Profissional',
            'descricao' => '<p>Plano <strong>completo</strong></p><script>alert(1)</script>',
            'status' => 'ATIVO',
        ]);

        $plano = (new PlanoRedeRepository($this->database))->findById($id);
        self::assertNotNull($plano);
        self::assertStringContainsString('<strong>completo</strong>', (string) $plano['descricao']);
        self::assertStringNotContainsString('<script', (string) $plano['descricao']);

        try {
            $service->create($actorId, [
                'nome' => self::PREFIX . 'Profissional',
                'status' => 'ATIVO',
            ]);
            self::fail('Nome duplicado deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('nome', $exception->getErrors());
        }
    }

    public function testRedeRecebePlanoContratadoEValidaCep(): void
    {
        $actorId = $this->userId('admin@local.test');
        $planoId = $this->planoRedeService()->create($actorId, [
            'nome' => self::PREFIX . 'Básico',
            'descricao' => '<p>Plano básico</p>',
            'status' => 'ATIVO',
        ]);

        $redeService = new RedeService(
            new RedeRepository($this->database),
            new PlanoRedeRepository($this->database)
        );

        $redeId = $redeService->create($this->redeData([
            'plano_rede_id' => (string) $planoId,
            'plano_contratado' => 'ignorado',
        ]));

        $rede = (new RedeRepository($this->database))->findById($redeId);
        self::assertSame($planoId, (int) $rede['plano_rede_id']);
        self::assertSame(self::PREFIX . 'Básico', $rede['plano_contratado']);
        self::assertSame(self::PREFIX . 'Básico', $rede['plano_nome']);

        try {
            $redeService->create($this->redeData([
                'cnpj' => '11444777000161',
                'cep' => '123',
            ]));
            self::fail('CEP inválido deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('cep', $exception->getErrors());
        }
    }

    private function planoRedeService(): PlanoRedeService
    {
        return new PlanoRedeService(new PlanoRedeRepository($this->database));
    }

    private function redeData(array $overrides = []): array
    {
        return array_merge([
            'razao_social' => 'Rede Plano Teste LTDA',
            'nome_fantasia' => 'Rede Plano Teste',
            'cnpj' => self::CNPJ,
            'email' => 'plano.rede@local.test',
            'telefone' => '(51) 99999-0000',
            'cep' => '90000-000',
            'logradouro' => 'Rua Teste',
            'numero' => '10',
            'complemento' => '',
            'bairro' => 'Centro',
            'cidade' => 'Porto Alegre',
            'estado' => 'RS',
            'status' => 'ATIVA',
        ], $overrides);
    }

    private function cleanup(): void
    {
        if (!isset($this->pdo)) {
            return;
        }

        $this->pdo->prepare('DELETE FROM redes WHERE cnpj = :cnpj')
            ->execute(['cnpj' => self::CNPJ]);
        $this->pdo->prepare('DELETE FROM planos_rede WHERE nome LIKE :prefix')
            ->execute(['prefix' => self::PREFIX . '%']);
    }
}
