<?php
namespace App\Models;

use App\Core\Model;

class Cliente extends Model
{
    protected string $table = 'clientes';

    public function autenticar(string $email, string $senha): ?array
    {
        $cliente = $this->queryOne(
            "SELECT * FROM clientes WHERE email = ? AND ativo = 1",
            [mb_strtolower(trim($email))]
        );
        if (!$cliente || !$cliente['senha'] || !password_verify($senha, $cliente['senha'])) return null;
        return $cliente;
    }

    public function findByEmail(string $email): ?array
    {
        return $this->queryOne(
            "SELECT * FROM clientes WHERE email = ?",
            [mb_strtolower(trim($email))]
        );
    }

    public function findByCpf(string $cpf): ?array
    {
        return $this->queryOne(
            "SELECT * FROM clientes WHERE cpf = ?",
            [self::normalizarCpf($cpf)]
        );
    }

    public function cadastrar(array $data): int
    {
        return $this->insert([
            'nome'            => trim($data['nome']),
            'cpf'             => self::normalizarCpf($data['cpf']),
            'email'           => mb_strtolower(trim($data['email'])),
            'telefone'        => preg_replace('/\D/', '', $data['telefone'] ?? ''),
            'senha'           => password_hash($data['senha'], PASSWORD_BCRYPT),
            'data_nascimento' => !empty($data['data_nascimento']) ? $data['data_nascimento'] : null,
            'origem'          => 'online',
        ]);
    }

    public function cadastrarPeloAdmin(array $data): int
    {
        $payload = [
            'nome'   => trim($data['nome']),
            'origem' => 'admin',
        ];
        if (!empty($data['email'])) {
            $payload['email'] = mb_strtolower(trim($data['email']));
        }
        if (!empty($data['cpf'])) {
            $cpfNums = preg_replace('/\D/', '', $data['cpf']);
            if (strlen($cpfNums) === 11) {
                $payload['cpf'] = self::normalizarCpf($data['cpf']);
            }
        }
        if (!empty($data['telefone'])) {
            $payload['telefone'] = preg_replace('/\D/', '', $data['telefone']);
        }
        if (!empty($data['data_nascimento'])) {
            $payload['data_nascimento'] = $data['data_nascimento'];
        }
        if (!empty($data['senha'])) {
            $payload['senha'] = password_hash($data['senha'], PASSWORD_BCRYPT);
        }
        return $this->insert($payload);
    }

    public function atualizar(int $id, array $data): void
    {
        $payload = [
            'nome'            => trim($data['nome']),
            'cpf'             => !empty($data['cpf']) ? self::normalizarCpf($data['cpf']) : null,
            'email'           => !empty($data['email']) ? mb_strtolower(trim($data['email'])) : null,
            'telefone'        => !empty($data['telefone']) ? preg_replace('/\D/', '', $data['telefone']) : null,
            'data_nascimento' => !empty($data['data_nascimento']) ? $data['data_nascimento'] : null,
        ];
        $this->update($id, $payload);
    }

    public function atualizarPeloAdmin(int $id, array $data): void
    {
        $payload = ['nome' => trim($data['nome'])];
        $payload['cpf']             = !empty($data['cpf']) ? self::normalizarCpf($data['cpf']) : null;
        $payload['email']           = !empty($data['email']) ? mb_strtolower(trim($data['email'])) : null;
        $payload['telefone']        = !empty($data['telefone']) ? preg_replace('/\D/', '', $data['telefone']) : null;
        $payload['data_nascimento'] = !empty($data['data_nascimento']) ? $data['data_nascimento'] : null;
        if (isset($data['ativo'])) $payload['ativo'] = (int)$data['ativo'];
        if (!empty($data['senha'])) $payload['senha'] = password_hash($data['senha'], PASSWORD_BCRYPT);
        $this->update($id, $payload);
    }

    public function allAtivos(): array
    {
        return $this->query(
            "SELECT id, nome, email, cpf, telefone FROM clientes WHERE ativo = 1 ORDER BY nome ASC"
        );
    }

    public function allComFiltros(string $busca = ''): array
    {
        if ($busca !== '') {
            $b = '%' . $busca . '%';
            return $this->query(
                "SELECT c.*, e.cep, e.cidade, e.estado
                 FROM clientes c
                 LEFT JOIN enderecos_clientes e ON e.cliente_id = c.id
                 WHERE c.nome LIKE ? OR c.email LIKE ? OR c.cpf LIKE ? OR c.telefone LIKE ?
                 ORDER BY c.nome ASC",
                [$b, $b, $b, $b]
            );
        }
        return $this->query(
            "SELECT c.*, e.cep, e.cidade, e.estado
             FROM clientes c
             LEFT JOIN enderecos_clientes e ON e.cliente_id = c.id
             ORDER BY c.nome ASC"
        );
    }

    public static function validarDados(array $data, bool $requireSenha = false): array
    {
        $erros = [];
        if (mb_strlen(trim($data['nome'] ?? '')) < 3) {
            $erros[] = 'Nome deve ter ao menos 3 caracteres.';
        }
        if (!empty($data['cpf']) && !self::validarCpf($data['cpf'])) {
            $erros[] = 'CPF inválido.';
        }
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'E-mail inválido.';
        }
        if (!empty($data['cep']) && strlen(preg_replace('/\D/', '', $data['cep'])) !== 8) {
            $erros[] = 'CEP inválido.';
        }
        if ($requireSenha) {
            if (mb_strlen($data['senha'] ?? '') < 8) {
                $erros[] = 'Senha deve ter ao menos 8 caracteres.';
            }
            if (($data['senha'] ?? '') !== ($data['confirmar_senha'] ?? '')) {
                $erros[] = 'As senhas não conferem.';
            }
        }
        return $erros;
    }

    public static function camposObrigatoriosFaltando(array $cliente, ?array $endereco): array
    {
        $faltando = [];
        if (empty($cliente['cpf']))            $faltando[] = 'CPF';
        if (empty($cliente['telefone']))       $faltando[] = 'Telefone';
        if (empty($cliente['data_nascimento'])) $faltando[] = 'Data de Nascimento';
        if (!$endereco || empty($endereco['cep']))       $faltando[] = 'CEP';
        if (!$endereco || empty($endereco['logradouro'])) $faltando[] = 'Endereço';
        if (!$endereco || empty($endereco['numero']))     $faltando[] = 'Número';
        if (!$endereco || empty($endereco['bairro']))     $faltando[] = 'Bairro';
        if (!$endereco || empty($endereco['cidade']))     $faltando[] = 'Cidade';
        if (!$endereco || empty($endereco['estado']))     $faltando[] = 'Estado';
        return $faltando;
    }

    public function alterarSenha(int $id, string $novaSenha): bool
    {
        return $this->update($id, ['senha' => password_hash($novaSenha, PASSWORD_BCRYPT)]);
    }

    public function getEndereco(int $clienteId): ?array
    {
        return $this->queryOne(
            "SELECT * FROM enderecos_clientes WHERE cliente_id = ?",
            [$clienteId]
        );
    }

    public function salvarEndereco(int $clienteId, array $data): void
    {
        $existente = $this->getEndereco($clienteId);
        $payload = [
            'cep'         => preg_replace('/\D/', '', $data['cep']),
            'logradouro'  => trim($data['logradouro']),
            'numero'      => trim($data['numero']),
            'complemento' => trim($data['complemento'] ?? ''),
            'bairro'      => trim($data['bairro']),
            'cidade'      => trim($data['cidade']),
            'estado'      => strtoupper(trim($data['estado'])),
        ];
        if ($existente) {
            $this->exec(
                "UPDATE enderecos_clientes SET cep=?, logradouro=?, numero=?, complemento=?, bairro=?, cidade=?, estado=? WHERE cliente_id=?",
                [...array_values($payload), $clienteId]
            );
        } else {
            $payload['cliente_id'] = $clienteId;
            $this->exec(
                "INSERT INTO enderecos_clientes (cliente_id, cep, logradouro, numero, complemento, bairro, cidade, estado) VALUES (?,?,?,?,?,?,?,?)",
                [$clienteId, $payload['cep'], $payload['logradouro'], $payload['numero'], $payload['complemento'], $payload['bairro'], $payload['cidade'], $payload['estado']]
            );
        }
    }

    public function gerarTokenSenha(int $clienteId): string
    {
        // Invalidar tokens anteriores
        $this->exec("UPDATE tokens_senha SET usado = 1 WHERE cliente_id = ? AND usado = 0", [$clienteId]);

        $token    = bin2hex(random_bytes(32));
        $expiraEm = date('Y-m-d H:i:s', strtotime('+2 hours'));
        $this->exec(
            "INSERT INTO tokens_senha (cliente_id, token, expira_em) VALUES (?, ?, ?)",
            [$clienteId, $token, $expiraEm]
        );
        return $token;
    }

    public function validarTokenSenha(string $token): ?array
    {
        return $this->queryOne(
            "SELECT t.*, c.email, c.nome FROM tokens_senha t
             JOIN clientes c ON c.id = t.cliente_id
             WHERE t.token = ? AND t.usado = 0 AND t.expira_em > NOW()",
            [$token]
        );
    }

    public function consumirTokenSenha(int $tokenId): void
    {
        $this->exec("UPDATE tokens_senha SET usado = 1 WHERE id = ?", [$tokenId]);
    }

    public static function validarCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) return false;

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int)$cpf[$i] * ($t + 1 - $i);
            }
            $rem = ($sum * 10) % 11;
            if ($rem === 10 || $rem === 11) $rem = 0;
            if ($rem !== (int)$cpf[$t]) return false;
        }
        return true;
    }

    public static function normalizarCpf(string $cpf): string
    {
        $nums = preg_replace('/\D/', '', $cpf);
        return substr($nums, 0, 3) . '.' . substr($nums, 3, 3) . '.' . substr($nums, 6, 3) . '-' . substr($nums, 9, 2);
    }

    public static function formatarTelefone(string $tel): string
    {
        $nums = preg_replace('/\D/', '', $tel);
        if (strlen($nums) === 11) {
            return '(' . substr($nums, 0, 2) . ') ' . substr($nums, 2, 5) . '-' . substr($nums, 7);
        }
        if (strlen($nums) === 10) {
            return '(' . substr($nums, 0, 2) . ') ' . substr($nums, 2, 4) . '-' . substr($nums, 6);
        }
        return $tel;
    }
}
