<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\ModalidadeRepository;
use App\Services\ModalidadeService;

final class ModalidadeTest extends IntegrationTestCase
{
    private const PREFIX = 'WAVE-MENU ';

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
    }

    public function testCriaEPesquisaModalidadeDaRede(): void
    {
        $service = $this->service();
        $redeId = $this->redeId();

        $id = $service->create($redeId, [
            'nome' => self::PREFIX . 'Spinning',
            'descricao' => 'Aula de spinning',
            'status' => 'ATIVO',
        ]);

        $modalidades = $service->forScope($redeId);
        $ids = array_map('intval', array_column($modalidades, 'id'));
        self::assertContains($id, $ids);

        try {
            $service->create($redeId, [
                'nome' => self::PREFIX . 'Spinning',
                'status' => 'ATIVO',
            ]);
            self::fail('Nome duplicado deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('nome', $exception->getErrors());
        }
    }

    public function testAdminDeRedeNaoEditaModalidadeGlobal(): void
    {
        $service = $this->service();
        $id = $service->create(null, [
            'nome' => self::PREFIX . 'Global',
            'status' => 'ATIVO',
        ]);

        try {
            $service->update($this->redeId(), null, null, $id, [
                'nome' => self::PREFIX . 'Global alterada',
                'status' => 'ATIVO',
            ]);
            self::fail('Modalidade global deveria ser somente leitura para a rede.');
        } catch (ForbiddenException $exception) {
            self::assertStringContainsString('fora da sua rede', $exception->getMessage());
        }
    }

    private function service(): ModalidadeService
    {
        return new ModalidadeService(new ModalidadeRepository($this->database));
    }

    private function cleanup(): void
    {
        if (!isset($this->pdo)) {
            return;
        }

        $statement = $this->pdo->prepare('DELETE FROM modalidades WHERE nome LIKE :prefix');
        $statement->execute(['prefix' => self::PREFIX . '%']);
    }
}
