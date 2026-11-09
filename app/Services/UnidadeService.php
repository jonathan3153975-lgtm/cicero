<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Repositories\RedeRepository;
use App\Repositories\UnidadeRepository;
use App\Validators\Validator;

final class UnidadeService
{
    private const SITUACOES = ['ATIVA', 'INATIVA', 'EM_IMPLANTACAO'];

    public function __construct(
        private readonly UnidadeRepository $unidades,
        private readonly RedeRepository $redes
    ) {
    }

    public function create(array $data): int
    {
        $data = $this->normalize($data);

        if ($data['codigo_interno'] === '') {
            $data['codigo_interno'] = $this->generateCode($data['rede_id'], $data['nome']);
        }

        $this->validate($data);

        if ($this->unidades->findByRedeAndCode($data['rede_id'], $data['codigo_interno']) !== null) {
            throw new ValidationException(['codigo_interno' => ['Já existe uma unidade com este código nesta rede.']]);
        }

        return $this->unidades->create($data);
    }

    public function update(int $id, array $data): void
    {
        $data = $this->normalize($data);

        if ($data['codigo_interno'] === '') {
            $data['codigo_interno'] = $this->generateCode($data['rede_id'], $data['nome'], $id);
        }

        $this->validate($data);

        $existing = $this->unidades->findByRedeAndCode($data['rede_id'], $data['codigo_interno']);

        if ($existing !== null && (int) $existing['id'] !== $id) {
            throw new ValidationException(['codigo_interno' => ['Já existe uma unidade com este código nesta rede.']]);
        }

        $this->unidades->update($id, $data);
    }

    private function normalize(array $data): array
    {
        return [
            'rede_id' => (int) ($data['rede_id'] ?? 0),
            'nome' => trim((string) ($data['nome'] ?? '')),
            'codigo_interno' => strtoupper(trim((string) ($data['codigo_interno'] ?? ''))),
            'situacao' => array_key_exists('ativo', $data)
                ? ((string) $data['ativo'] === '1' ? 'ATIVA' : 'INATIVA')
                : strtoupper(trim((string) ($data['situacao'] ?? 'ATIVA'))),
            'cep' => trim((string) ($data['cep'] ?? '')),
            'logradouro' => trim((string) ($data['logradouro'] ?? '')),
            'numero' => trim((string) ($data['numero'] ?? '')),
            'complemento' => trim((string) ($data['complemento'] ?? '')),
            'bairro' => trim((string) ($data['bairro'] ?? '')),
            'cidade' => trim((string) ($data['cidade'] ?? '')),
            'estado' => strtoupper(trim((string) ($data['estado'] ?? ''))),
            'telefone' => trim((string) ($data['telefone'] ?? '')),
            'whatsapp' => trim((string) ($data['whatsapp'] ?? '')),
            'email' => trim((string) ($data['email'] ?? '')),
            'data_abertura' => trim((string) ($data['data_abertura'] ?? '')),
            'horario_funcionamento' => trim((string) ($data['horario_funcionamento'] ?? '')),
            'capacidade_maxima' => trim((string) ($data['capacidade_maxima'] ?? '')),
            'metragem' => trim((string) ($data['metragem'] ?? '')),
        ];
    }

    private function validate(array $data): void
    {
        $validator = (new Validator($data))
            ->required('nome')
            ->maxLength('nome', 180)
            ->required('codigo_interno')
            ->maxLength('codigo_interno', 40)
            ->in('situacao', self::SITUACOES)
            ->email('email')
            ->maxLength('email', 180);

        $errors = $validator->errors();

        if ($data['rede_id'] <= 0) {
            $errors['rede_id'][] = 'Selecione a rede.';
        }

        if ($data['estado'] !== '' && mb_strlen($data['estado']) !== 2) {
            $errors['estado'][] = 'Informe a sigla do estado com 2 letras.';
        }

        $cep = preg_replace('/\D/', '', $data['cep']) ?? '';

        if ($data['cep'] !== '' && strlen($cep) !== 8) {
            $errors['cep'][] = 'Informe um CEP válido.';
        }

        if (
            $data['capacidade_maxima'] !== ''
            && (!ctype_digit($data['capacidade_maxima']) || (int) $data['capacidade_maxima'] < 0)
        ) {
            $errors['capacidade_maxima'][] = 'Informe uma capacidade válida.';
        }

        if ($data['metragem'] !== '' && (!is_numeric($data['metragem']) || (float) $data['metragem'] < 0)) {
            $errors['metragem'][] = 'Informe uma metragem válida.';
        }

        if ($data['data_abertura'] !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['data_abertura']) !== 1) {
            $errors['data_abertura'][] = 'Informe a data no formato AAAA-MM-DD.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        if ($this->redes->findById($data['rede_id']) === null) {
            throw new ValidationException(['rede_id' => ['Rede inexistente.']]);
        }
    }

    private function generateCode(int $redeId, string $nome, ?int $ignoreId = null): string
    {
        $base = mb_strtoupper($nome, 'UTF-8');
        $base = strtr($base, [
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C',
        ]);
        $base = preg_replace('/[^A-Z0-9]+/', '-', $base) ?? '';
        $base = trim(substr($base, 0, 30), '-');

        if ($base === '') {
            $base = 'UNIDADE';
        }

        $code = $base;
        $suffix = 2;

        while (true) {
            $existing = $this->unidades->findByRedeAndCode($redeId, $code);

            if ($existing === null || ($ignoreId !== null && (int) $existing['id'] === $ignoreId)) {
                return $code;
            }

            $code = substr($base, 0, 27) . '-' . $suffix;
            $suffix++;
        }
    }
}
