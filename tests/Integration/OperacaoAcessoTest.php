<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\AlunoRepository;
use App\Repositories\AlunoResponsavelRepository;
use App\Repositories\DispositivoAcessoRepository;
use App\Repositories\EventoAcessoRepository;
use App\Repositories\FrequenciaRepository;
use App\Repositories\PessoaContatoRepository;
use App\Repositories\PessoaEnderecoRepository;
use App\Repositories\PessoaRepository;
use App\Repositories\ReservaRepository;
use App\Repositories\TurmaHorarioRepository;
use App\Repositories\TurmaRepository;
use App\Repositories\UnidadeRepository;
use App\Services\AcessoService;
use App\Services\AlunoService;
use App\Services\FrequenciaService;
use App\Services\PessoaService;
use App\Services\ReservaService;
use App\Services\TurmaService;

final class OperacaoAcessoTest extends IntegrationTestCase
{
    private const EMAILS = ['wave3.student@local.test', 'wave3.student2@local.test'];
    private const TURMA_PREFIX = 'WAVE3 ';
    private const DEVICE_PREFIX = 'WAVE3-';

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
    }

    public function testTurmaComHorarioERegraDeSobreposicao(): void
    {
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');
        $service = $this->turmaService();

        $turmaId = $service->create($actorId, $redeId, $centroId, null, [
            'nome' => self::TURMA_PREFIX . 'Funcional',
            'capacidade' => '10',
            'sala' => 'Sala 1',
            'status' => 'ATIVA',
        ]);

        $service->addHorario($turmaId, $redeId, $centroId, null, [
            'dia_semana' => '1',
            'hora_inicio' => '08:00',
            'hora_fim' => '09:00',
        ]);

        self::assertCount(1, $service->horariosForTurma($turmaId));

        try {
            $service->addHorario($turmaId, $redeId, $centroId, null, [
                'dia_semana' => '1',
                'hora_inicio' => '08:30',
                'hora_fim' => '09:30',
            ]);
            self::fail('Horário sobreposto deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('hora_inicio', $exception->getErrors());
        }
    }

    public function testTurmaDeOutraUnidadeNaoEhAcessivel(): void
    {
        $adminId = $this->userId('admin@local.test');
        $localId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $shoppingId = $this->unidadeId('SHOPPING');

        $turmaId = $this->turmaService()->create($adminId, $redeId, null, null, [
            'nome' => self::TURMA_PREFIX . 'Shopping',
            'unidade_id' => (string) $shoppingId,
            'status' => 'ATIVA',
        ]);

        try {
            $this->turmaService()->assertCanAccess($turmaId, $redeId, $this->unidadeId('CENTRO'), null);
            self::fail('Turma de outra unidade deveria falhar.');
        } catch (ForbiddenException) {
            self::assertTrue(true);
        }
    }

    public function testFrequenciaRegistraEImpedeDuplicidade(): void
    {
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');
        $alunoId = $this->createStudent('wave3.student@local.test');
        $turmaId = $this->createTurma();

        $service = $this->frequenciaService();
        $id = $service->register($actorId, $redeId, $centroId, null, [
            'aluno_id' => (string) $alunoId,
            'turma_id' => (string) $turmaId,
            'data' => '2026-02-02',
            'presenca' => '1',
            'origem' => 'MANUAL',
        ]);

        self::assertGreaterThan(0, $id);
        self::assertNotEmpty($service->forScope($redeId, $centroId, null));

        try {
            $service->register($actorId, $redeId, $centroId, null, [
                'aluno_id' => (string) $alunoId,
                'turma_id' => (string) $turmaId,
                'data' => '2026-02-02',
                'presenca' => '1',
            ]);
            self::fail('Frequência duplicada deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('data', $exception->getErrors());
        }
    }

    public function testReservaRespeitaDuplicidadeECapacidade(): void
    {
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');
        $aluno1 = $this->createStudent('wave3.student@local.test');
        $aluno2 = $this->createStudent('wave3.student2@local.test');
        $turmaId = $this->createTurma(1);
        $horarioId = $this->turmaService()->addHorario($turmaId, $redeId, $centroId, null, [
            'dia_semana' => '1',
            'hora_inicio' => '08:00',
            'hora_fim' => '09:00',
        ]);

        $service = $this->reservaService();
        $id = $service->create($actorId, $redeId, $centroId, null, [
            'aluno_id' => (string) $aluno1,
            'turma_horario_id' => (string) $horarioId,
            'data' => '2026-02-02',
        ]);

        self::assertGreaterThan(0, $id);

        try {
            $service->create($actorId, $redeId, $centroId, null, [
                'aluno_id' => (string) $aluno1,
                'turma_horario_id' => (string) $horarioId,
                'data' => '2026-02-02',
            ]);
            self::fail('Reserva duplicada deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('data', $exception->getErrors());
        }

        try {
            $service->create($actorId, $redeId, $centroId, null, [
                'aluno_id' => (string) $aluno2,
                'turma_horario_id' => (string) $horarioId,
                'data' => '2026-02-02',
            ]);
            self::fail('Capacidade esgotada deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('turma_horario_id', $exception->getErrors());
        }

        $service->cancel($id, $redeId, $centroId, null);
        self::assertSame('CANCELADA', (new ReservaRepository($this->database))->findById($id)['status']);
    }

    public function testDispositivoEEventoDeAcesso(): void
    {
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');
        $alunoId = $this->createStudent('wave3.student@local.test');
        $service = $this->acessoService();

        $deviceId = $service->createDevice($actorId, $redeId, $centroId, null, [
            'nome' => 'Catraca WAVE3',
            'tipo' => 'CATRACA',
            'identificador' => self::DEVICE_PREFIX . 'CATRACA-1',
            'direcao' => 'AMBOS',
            'status' => 'ATIVO',
        ]);

        $eventId = $service->recordEvent($actorId, $redeId, $centroId, null, [
            'dispositivo_id' => (string) $deviceId,
            'aluno_id' => (string) $alunoId,
            'tipo' => 'ENTRADA',
            'ocorrido_em' => '2026-02-02 08:00:00',
            'autorizado' => '1',
        ]);

        self::assertGreaterThan(0, $eventId);
        self::assertNotEmpty($service->eventsForScope($redeId, $centroId, null));
    }

    private function createStudent(string $email): int
    {
        return $this->alunoService()->create(
            $this->userId('local@local.test'),
            $this->redeId(),
            $this->unidadeId('CENTRO'),
            null,
            [
                'nome' => 'Aluno WAVE3',
                'email_principal' => $email,
                'cpf' => $email === 'wave3.student@local.test' ? '93541134780' : null,
                'codigo' => 'WAVE3-' . md5($email),
                'data_entrada' => '2026-01-01',
                'status' => 'ATIVO',
            ]
        );
    }

    private function createTurma(int $capacidade = 10): int
    {
        return $this->turmaService()->create(
            $this->userId('local@local.test'),
            $this->redeId(),
            $this->unidadeId('CENTRO'),
            null,
            [
                'nome' => self::TURMA_PREFIX . 'Funcional ' . $capacidade,
                'capacidade' => (string) $capacidade,
                'sala' => 'Sala WAVE3',
                'status' => 'ATIVA',
            ]
        );
    }

    private function turmaService(): TurmaService
    {
        return new TurmaService(
            new TurmaRepository($this->database),
            new TurmaHorarioRepository($this->database),
            new UnidadeRepository($this->database)
        );
    }

    private function frequenciaService(): FrequenciaService
    {
        return new FrequenciaService(
            $this->alunoService(),
            $this->turmaService(),
            new TurmaHorarioRepository($this->database),
            new FrequenciaRepository($this->database)
        );
    }

    private function reservaService(): ReservaService
    {
        return new ReservaService(
            $this->alunoService(),
            $this->turmaService(),
            new TurmaHorarioRepository($this->database),
            new ReservaRepository($this->database),
            new UnidadeRepository($this->database)
        );
    }

    private function acessoService(): AcessoService
    {
        return new AcessoService(
            new DispositivoAcessoRepository($this->database),
            new EventoAcessoRepository($this->database),
            $this->alunoService(),
            new UnidadeRepository($this->database)
        );
    }

    private function pessoaService(): PessoaService
    {
        return new PessoaService(
            new PessoaRepository($this->database),
            new PessoaContatoRepository($this->database),
            new PessoaEnderecoRepository($this->database),
            $this->database
        );
    }

    private function alunoService(): AlunoService
    {
        return new AlunoService(
            new AlunoRepository($this->database),
            new AlunoResponsavelRepository($this->database),
            $this->pessoaService(),
            new PessoaRepository($this->database),
            new UnidadeRepository($this->database),
            $this->database
        );
    }

    private function cleanup(): void
    {
        if (!isset($this->pdo)) {
            return;
        }

        $emails = implode(', ', array_fill(0, count(self::EMAILS), '?'));

        $pessoaStmt = $this->pdo->prepare(
            'SELECT id FROM pessoas WHERE email_principal IN (' . $emails . ')'
        );
        $pessoaStmt->execute(self::EMAILS);
        $pessoaIds = array_map('intval', $pessoaStmt->fetchAll(\PDO::FETCH_COLUMN));
        $alunoIds = [];

        if ($pessoaIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($pessoaIds), '?'));
            $alunoStmt = $this->pdo->prepare('SELECT id FROM alunos WHERE pessoa_id IN (' . $placeholders . ')');
            $alunoStmt->execute($pessoaIds);
            $alunoIds = array_map('intval', $alunoStmt->fetchAll(\PDO::FETCH_COLUMN));
        }

        $turmaStmt = $this->pdo->prepare('SELECT id FROM turmas WHERE nome LIKE :prefix');
        $turmaStmt->execute(['prefix' => self::TURMA_PREFIX . '%']);
        $turmaIds = array_map('intval', $turmaStmt->fetchAll(\PDO::FETCH_COLUMN));

        $deviceStmt = $this->pdo->prepare(
            'SELECT id FROM dispositivos_acesso WHERE identificador LIKE :prefix'
        );
        $deviceStmt->execute(['prefix' => self::DEVICE_PREFIX . '%']);
        $deviceIds = array_map('intval', $deviceStmt->fetchAll(\PDO::FETCH_COLUMN));

        if ($alunoIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($alunoIds), '?'));
            $this->pdo->prepare('DELETE FROM eventos_acesso WHERE aluno_id IN (' . $placeholders . ')')->execute($alunoIds);
            $this->pdo->prepare('DELETE FROM frequencias WHERE aluno_id IN (' . $placeholders . ')')->execute($alunoIds);
            $this->pdo->prepare('DELETE FROM reservas WHERE aluno_id IN (' . $placeholders . ')')->execute($alunoIds);
        }

        if ($deviceIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($deviceIds), '?'));
            $this->pdo->prepare('DELETE FROM eventos_acesso WHERE dispositivo_id IN (' . $placeholders . ')')->execute($deviceIds);
            $this->pdo->prepare('DELETE FROM dispositivos_acesso WHERE id IN (' . $placeholders . ')')->execute($deviceIds);
        }

        if ($turmaIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($turmaIds), '?'));
            $this->pdo->prepare('DELETE FROM frequencias WHERE turma_id IN (' . $placeholders . ')')->execute($turmaIds);
            $this->pdo->prepare('DELETE FROM reservas WHERE turma_horario_id IN'
                . ' (SELECT id FROM turma_horarios WHERE turma_id IN (' . $placeholders . '))')->execute($turmaIds);
            $this->pdo->prepare('DELETE FROM turma_alunos WHERE turma_id IN (' . $placeholders . ')')->execute($turmaIds);
            $this->pdo->prepare('DELETE FROM turma_horarios WHERE turma_id IN (' . $placeholders . ')')->execute($turmaIds);
            $this->pdo->prepare('DELETE FROM turmas WHERE id IN (' . $placeholders . ')')->execute($turmaIds);
        }

        if ($alunoIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($alunoIds), '?'));
            $this->pdo->prepare('DELETE FROM aluno_responsaveis WHERE aluno_id IN (' . $placeholders . ')')->execute($alunoIds);
            $this->pdo->prepare('DELETE FROM alunos WHERE id IN (' . $placeholders . ')')->execute($alunoIds);
        }

        if ($pessoaIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($pessoaIds), '?'));
            $this->pdo->prepare('DELETE FROM pessoas WHERE id IN (' . $placeholders . ')')->execute($pessoaIds);
        }
    }
}
