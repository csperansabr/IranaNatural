<?php
namespace Admin\Controllers;

use App\Models\Pagamento;

class WebhookLogsController extends AdminController
{
    private Pagamento $pagamentoModel;

    public function __construct()
    {
        $this->pagamentoModel = new Pagamento();
    }

    public function index(): void
    {
        $filtros = [
            'order_nsu'  => trim($_GET['order_nsu']  ?? ''),
            'status'     => trim($_GET['status']     ?? ''),
            'processado' => $_GET['processado']      ?? '',
            'data_ini'   => trim($_GET['data_ini']   ?? ''),
            'data_fim'   => trim($_GET['data_fim']   ?? ''),
        ];

        $logs           = $this->pagamentoModel->getAllWebhookLogs($filtros);
        $flash          = $this->getFlash();
        $user           = $this->currentUser();
        $pageTitle      = 'Logs de Webhook';
        $pageBreadcrumb = 'E-commerce / Logs de Webhook';

        $this->render('webhook_logs/index', compact(
            'logs', 'flash', 'filtros', 'pageTitle', 'pageBreadcrumb', 'user'
        ));
    }

    public function ver(int $id): void
    {
        $log = $this->pagamentoModel->findWebhookLogById($id);
        if (!$log) {
            $this->flash('error', 'Log não encontrado.');
            $this->redirect('/admin/webhook_logs');
            return;
        }

        $flash          = $this->getFlash();
        $user           = $this->currentUser();
        $pageTitle      = 'Webhook Log #' . $id;
        $pageBreadcrumb = 'E-commerce / <a href="/admin/webhook_logs">Logs de Webhook</a> / #' . $id;

        $this->render('webhook_logs/ver', compact(
            'log', 'flash', 'pageTitle', 'pageBreadcrumb', 'user'
        ));
    }
}
