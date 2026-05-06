<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class ContatoController extends Controller
{
    public function index(): void
    {
        $flash = Session::flash('contato_ok');
        $erro  = Session::flash('contato_erro');

        $meta = [
            'title'       => 'Contato — ' . APP_NAME,
            'description' => 'Entre em contato com a Iraná Natural via WhatsApp, e-mail ou formulário. Estamos aqui para atender você.',
            'url'         => APP_URL . '/contato',
        ];
        $this->render('contato/index', compact('meta', 'flash', 'erro'));
    }

    public function enviar(): void
    {
        $nome    = trim($_POST['nome']    ?? '');
        $email   = trim($_POST['email']   ?? '');
        $assunto = trim($_POST['assunto'] ?? '');
        $mensagem= trim($_POST['mensagem']?? '');

        if (!$nome || !$email || !$mensagem || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('contato_erro', 'Preencha todos os campos corretamente.');
            $this->redirect(APP_URL . '/contato');
            return;
        }

        $nome     = htmlspecialchars($nome,    ENT_QUOTES, 'UTF-8');
        $assunto  = htmlspecialchars($assunto, ENT_QUOTES, 'UTF-8');
        $mensagem = htmlspecialchars($mensagem,ENT_QUOTES, 'UTF-8');

        $corpo = "Nome: {$nome}\nE-mail: {$email}\nAssunto: {$assunto}\n\nMensagem:\n{$mensagem}";

        $headers  = "From: " . EMAIL_NOREPLY . "\r\n";
        $headers .= "Reply-To: {$email}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $enviado = mail(
            EMAIL_CONTATO,
            "[Site] {$assunto}",
            $corpo,
            $headers
        );

        if ($enviado) {
            Session::flash('contato_ok', 'Mensagem enviada com sucesso! Em breve entraremos em contato.');
        } else {
            Session::flash('contato_erro', 'Não foi possível enviar a mensagem. Tente pelo WhatsApp.');
        }

        $this->redirect(APP_URL . '/contato');
    }
}
