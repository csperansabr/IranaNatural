<?php
namespace Admin\Controllers;

use App\Core\CsvParser;
use App\Core\XlsxParser;
use App\Core\Importador;
use App\Core\Session;
use App\Models\ImportHistory;

class ImportacaoController extends AdminController
{
    private const ENTIDADES = ['produtos', 'insumos', 'estoque'];
    private const MAX_SIZE  = 5 * 1024 * 1024; // 5 MB
    private const TEMP_DIR  = ROOT . '/uploads/temp/';

    public function index(): void
    {
        $historico = (new ImportHistory())->recentes(10);
        $flash     = $this->getFlash();
        $this->render('importacao/index', compact('historico', 'flash'));
    }

    // GET /admin/importacao/{entidade}
    public function form(string $entidade): void
    {
        if (!in_array($entidade, self::ENTIDADES, true)) {
            $this->redirect('/admin/importacao');
            return;
        }
        $flash = $this->getFlash();
        $this->render('importacao/form', compact('entidade', 'flash'));
    }

    // POST /admin/importacao/{entidade}/preview → JSON
    public function preview(string $entidade): void
    {
        if (!in_array($entidade, self::ENTIDADES, true)) {
            $this->json(['error' => 'Entidade inválida.'], 400);
            return;
        }

        $file = $_FILES['arquivo'] ?? null;
        $modo = $_POST['modo'] ?? 'criar_atualizar';

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $errMsg = match ($file['error'] ?? -1) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande para o servidor.',
                UPLOAD_ERR_NO_FILE                        => 'Nenhum arquivo enviado.',
                default                                   => 'Erro no upload do arquivo (código: ' . ($file['error'] ?? 'desconhecido') . ').',
            };
            $this->json(['error' => $errMsg], 400);
            return;
        }

        if ($file['size'] > self::MAX_SIZE) {
            $this->json(['error' => 'Arquivo muito grande. Máximo: 5 MB.'], 400);
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'xlsx'], true)) {
            $this->json(['error' => 'Formato inválido. Use CSV ou XLSX.'], 400);
            return;
        }

        // Clean up any leftover temp file from a previous preview of this entity
        $oldTmpPath = Session::get('import_tmp_' . $entidade);
        if ($oldTmpPath && file_exists($oldTmpPath)) {
            @unlink($oldTmpPath);
        }

        // Save temp file
        if (!is_dir(self::TEMP_DIR)) {
            mkdir(self::TEMP_DIR, 0755, true);
        }
        $tmpPath = self::TEMP_DIR . uniqid('imp_') . '_' . $entidade . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $tmpPath);

        try {
            $rows = $ext === 'xlsx'
                ? XlsxParser::parse($tmpPath)
                : CsvParser::parse($tmpPath);
        } catch (\Exception $e) {
            @unlink($tmpPath);
            $this->json(['error' => $e->getMessage()], 422);
            return;
        }

        if (count($rows) < 2) {
            @unlink($tmpPath);
            $this->json(['error' => 'Arquivo vazio ou sem dados após o cabeçalho.'], 422);
            return;
        }

        // Store temp path in session for processar step
        Session::set('import_tmp_'      . $entidade, $tmpPath);
        Session::set('import_modo_'     . $entidade, $modo);
        Session::set('import_arquivo_'  . $entidade, $file['name']);

        $preview = Importador::validar($entidade, $rows, $modo);
        $this->json($preview);
    }

    // POST /admin/importacao/{entidade}/processar → JSON
    public function processar(string $entidade): void
    {
        if (!in_array($entidade, self::ENTIDADES, true)) {
            $this->json(['error' => 'Entidade inválida.'], 400);
            return;
        }

        $tmpPath = Session::get('import_tmp_'     . $entidade);
        $modo    = Session::get('import_modo_'    . $entidade, 'criar_atualizar');
        $nomeArq = Session::get('import_arquivo_' . $entidade, '');

        if (!$tmpPath || !file_exists($tmpPath)) {
            $this->json(['error' => 'Sessão expirada. Faça o upload novamente.'], 422);
            return;
        }

        $ext = strtolower(pathinfo($tmpPath, PATHINFO_EXTENSION));
        try {
            $rows = $ext === 'xlsx'
                ? XlsxParser::parse($tmpPath)
                : CsvParser::parse($tmpPath);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 422);
            return;
        }

        // Create import_history record
        $user      = $this->currentUser();
        $histModel = new ImportHistory();
        $importId  = $histModel->insert([
            'entidade'     => $entidade,
            'modo'         => $modo,
            'arquivo_nome' => $nomeArq,
            'total_linhas' => max(0, count($rows) - 1),
            'usuario_id'   => $user['id'] ?? null,
        ]);

        $result = Importador::processar($entidade, $rows, $modo, (int) $importId);

        // Update history with results
        $histModel->update((int) $importId, [
            'inseridos'  => $result['inseridos'],
            'atualizados'=> $result['atualizados'],
            'erros'      => $result['erros'],
            'ignorados'  => $result['ignorados'],
        ]);

        // Clean up temp file and session
        @unlink($tmpPath);
        Session::delete('import_tmp_'     . $entidade);
        Session::delete('import_modo_'    . $entidade);
        Session::delete('import_arquivo_' . $entidade);

        $this->json($result);
    }

    // GET /admin/importacao/{entidade}/modelo → download CSV
    public function modelo(string $entidade): void
    {
        if (!in_array($entidade, self::ENTIDADES, true)) {
            $this->redirect('/admin/importacao');
            return;
        }
        $csv      = Importador::gerarTemplate($entidade);
        $filename = 'modelo-' . $entidade . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo "\xEF\xBB\xBF" . $csv; // UTF-8 BOM for Excel compatibility
        exit;
    }

    // GET /admin/importacao/historico
    public function historico(): void
    {
        $historico = (new ImportHistory())->recentes(100);
        $flash     = $this->getFlash();
        $this->render('importacao/historico', compact('historico', 'flash'));
    }
}
