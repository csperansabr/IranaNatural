<?php
/**
 * Iraná Natural — Importador CLI de Insumos
 * Logger com saída colorida no console e gravação em arquivo.
 */
declare(strict_types=1);

class Logger
{
    /** @var resource|false */
    private $fh      = false;
    private int $errors   = 0;
    private int $warnings = 0;

    public function __construct(string $logFile)
    {
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->fh = fopen($logFile, 'a');
        if ($this->fh) {
            fwrite($this->fh, "\n" . str_repeat('─', 70) . "\n");
            fwrite($this->fh, 'Sessão iniciada: ' . date('Y-m-d H:i:s') . "\n");
            fwrite($this->fh, str_repeat('─', 70) . "\n");
        }
    }

    public function title(string $msg): void
    {
        $this->out('');
        $this->out(c('▶ ' . mb_strtoupper($msg, 'UTF-8'), 'bold'));
        $this->file('');
        $this->file('═══ ' . $msg . ' ═══');
    }

    public function info(string $msg): void
    {
        $this->out(c('  · ' . $msg, 'cyan'));
        $this->file('[INFO] ' . $msg);
    }

    public function success(string $msg): void
    {
        $this->out(c('  ✓ ' . $msg, 'green'));
        $this->file('[OK]   ' . $msg);
    }

    public function warn(string $msg): void
    {
        $this->warnings++;
        $this->out(c('  ⚠ ' . $msg, 'yellow'));
        $this->file('[WARN] ' . $msg);
    }

    public function error(string $msg): void
    {
        $this->errors++;
        $this->out(c('  ✗ ' . $msg, 'red'));
        $this->file('[ERR]  ' . $msg);
    }

    public function line(string $msg = ''): void
    {
        $this->out($msg);
        $this->file($msg);
    }

    public function errorCount(): int { return $this->errors; }
    public function warnCount(): int  { return $this->warnings; }

    public function close(): void
    {
        if ($this->fh) {
            fwrite($this->fh, 'Sessão finalizada: ' . date('Y-m-d H:i:s') . "\n");
            fclose($this->fh);
            $this->fh = false;
        }
    }

    private function out(string $msg): void
    {
        fwrite(STDOUT, $msg . "\n");
    }

    private function file(string $msg): void
    {
        if ($this->fh) {
            fwrite($this->fh, date('[H:i:s] ') . stripAnsi($msg) . "\n");
        }
    }
}
