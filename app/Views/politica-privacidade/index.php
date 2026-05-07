<?php use App\Core\Helper; ?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Trilha de navegação">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <span>Política de Privacidade</span>
        </nav>
        <h1>Política de Privacidade e Segurança</h1>
        <p class="page-hero-desc">Transparência sobre como tratamos suas informações e protegemos sua privacidade.</p>
    </div>
</section>

<!-- VISÃO GERAL -->
<section class="section-institucional-alt">
    <div class="container">
        <div class="section-header">
            <span class="label-small">Em resumo</span>
            <h2>Nossos compromissos com você</h2>
        </div>

        <div class="sobre-valores">
            <div class="valor-item">
                <div class="valor-icon" aria-hidden="true">🔒</div>
                <h3>Coleta mínima</h3>
                <p>Coletamos apenas o necessário para processar seu pedido e melhorar nossos serviços. Nenhum dado supérfluo é solicitado.</p>
            </div>

            <div class="valor-item">
                <div class="valor-icon" aria-hidden="true">⚖️</div>
                <h3>Conformidade com a LGPD</h3>
                <p>Todos os dados são tratados com fundamentos legais claros, conforme a Lei Geral de Proteção de Dados (Lei nº 13.709/2018).</p>
            </div>

            <div class="valor-item">
                <div class="valor-icon" aria-hidden="true">🛡️</div>
                <h3>Transações seguras</h3>
                <p>Comunicações pelo WhatsApp têm criptografia de ponta a ponta. Não armazenamos dados de cartão ou informações bancárias em nosso sistema.</p>
            </div>

            <div class="valor-item">
                <div class="valor-icon" aria-hidden="true">✉️</div>
                <h3>Você no controle</h3>
                <p>Você pode acessar, corrigir e solicitar a exclusão dos seus dados a qualquer momento, dentro dos limites legais aplicáveis.</p>
            </div>
        </div>
    </div>
</section>

<!-- POLÍTICA COMPLETA -->
<section class="section-institucional">
    <div class="container">
        <div class="institucional-conteudo">

            <p class="politica-data">
                Última atualização: <strong>07 de maio de 2026.</strong>
                Esta política se aplica ao site <strong>irananatural.com.br</strong> e a todas as interações realizadas pelo WhatsApp no âmbito de compras e atendimento ao cliente.
            </p>

            <h2>1. Quem somos</h2>
            <p>A <strong>Iraná Natural</strong> é uma produtora artesanal de produtos naturais, localizada em Porto Alegre (RS). Para fins desta política, atuamos como <strong>Controladora de Dados</strong>, sendo responsáveis pelas decisões sobre como tratamos as informações pessoais dos nossos clientes e visitantes.</p>
            <p>Canal de contato para assuntos de privacidade: <a href="mailto:<?= EMAIL_CONTATO ?>"><?= EMAIL_CONTATO ?></a></p>

            <h2>2. Dados que coletamos</h2>
            <p><strong>Fornecidos diretamente por você:</strong></p>
            <ul>
                <li><strong>Número de telefone e WhatsApp</strong> — para comunicação sobre pedidos, confirmação de pagamento e atualizações de envio;</li>
                <li><strong>Endereço de entrega</strong> — necessário para pedidos enviados pelos Correios ou entregues pessoalmente na sua localidade;</li>
                <li><strong>Nome e mensagem</strong> — informados voluntariamente pelo formulário de contato disponível no site.</li>
            </ul>

            <p><strong>Coletados automaticamente pela navegação:</strong></p>
            <ul>
                <li><strong>Cookies de sessão (PHPSESSID)</strong> — necessários para o funcionamento do site; expiram ao fechar o navegador;</li>
                <li><strong>Cookies de análise (Google Analytics)</strong> — utilizados para entender o comportamento dos visitantes de forma agregada, sem vinculação à sua identidade.</li>
            </ul>

            <p><strong>O que não coletamos:</strong></p>
            <ul>
                <li>Dados de cartão de crédito ou débito;</li>
                <li>Senhas ou credenciais de acesso a sistemas externos;</li>
                <li>Informações bancárias completas (agência, conta corrente);</li>
                <li>Dados pessoais sensíveis, conforme definidos pelo art. 5º, II da LGPD.</li>
            </ul>

            <h2>3. Finalidade e bases legais</h2>
            <p>Todo tratamento de dados possui finalidade definida e respaldo legal, conforme o art. 7º da Lei nº 13.709/2018:</p>

            <div class="politica-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Dado</th>
                            <th>Finalidade</th>
                            <th>Base legal (LGPD, art. 7º)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Telefone / WhatsApp</td>
                            <td>Comunicação, confirmação e suporte ao pedido</td>
                            <td>Execução de contrato (inc. V)</td>
                        </tr>
                        <tr>
                            <td>Endereço de entrega</td>
                            <td>Organização e execução da entrega</td>
                            <td>Execução de contrato (inc. V)</td>
                        </tr>
                        <tr>
                            <td>Nome e mensagem</td>
                            <td>Resposta a dúvidas e atendimento</td>
                            <td>Legítimo interesse (inc. IX)</td>
                        </tr>
                        <tr>
                            <td>Cookies de análise</td>
                            <td>Melhoria da experiência no site</td>
                            <td>Legítimo interesse (inc. IX)</td>
                        </tr>
                        <tr>
                            <td>Histórico de pedidos</td>
                            <td>Suporte pós-venda, defesa em disputas, compliance</td>
                            <td>Obrigação legal (inc. II) e Legítimo interesse (inc. IX)</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h2>4. Uso de cookies</h2>
            <p>Utilizamos dois tipos de cookies neste site:</p>

            <p><strong>Cookies funcionais</strong> (necessários para o funcionamento — não podem ser desativados):</p>
            <div class="politica-table-wrap">
                <table>
                    <thead>
                        <tr><th>Cookie</th><th>Duração</th><th>Finalidade</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>PHPSESSID</code></td>
                            <td>Sessão (encerra com o navegador)</td>
                            <td>Manutenção da sessão do usuário no servidor</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p><strong>Cookies de análise</strong> (Google Analytics — medem o uso do site de forma agregada e anônima):</p>
            <div class="politica-table-wrap">
                <table>
                    <thead>
                        <tr><th>Cookie</th><th>Duração</th><th>Finalidade</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>_ga</code></td>
                            <td>2 anos</td>
                            <td>Distingue usuários únicos para métricas agregadas</td>
                        </tr>
                        <tr>
                            <td><code>_gid</code></td>
                            <td>24 horas</td>
                            <td>Distingue sessões de navegação individuais</td>
                        </tr>
                        <tr>
                            <td><code>_ga_*</code></td>
                            <td>2 anos</td>
                            <td>Mantém o estado da sessão no Google Analytics 4</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p>Para desativar os cookies de análise, utilize o <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" rel="noopener noreferrer">complemento de desativação do Google Analytics</a> ou ajuste as preferências de cookies no seu navegador. A desativação dos cookies de análise não afeta a navegação no site.</p>

            <h2>5. Compartilhamento de dados</h2>
            <p>Não vendemos, alugamos nem cedemos seus dados pessoais a terceiros para fins comerciais. O compartilhamento ocorre apenas quando necessário para a prestação do serviço:</p>
            <ul>
                <li><strong>Correios</strong> — endereço de entrega, exclusivamente para execução do envio contratado;</li>
                <li><strong>Google (Analytics)</strong> — dados de navegação anônimos e agregados, conforme a <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Política de Privacidade do Google</a>;</li>
                <li><strong>Operadoras de pagamento</strong> — quando o pagamento é realizado via link externo, os dados são processados diretamente pela operadora, sem trânsito pelo nosso sistema;</li>
                <li><strong>Autoridades competentes</strong> — quando exigido por lei, ordem judicial ou procedimento administrativo com fundamento legal.</li>
            </ul>

            <h2>6. Retenção de dados</h2>
            <p>Mantemos seus dados pelo tempo necessário para cumprir as finalidades que motivaram sua coleta, respeitando os prazos mínimos legais:</p>

            <div class="politica-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tipo de dado</th>
                            <th>Período de retenção</th>
                            <th>Fundamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Dados de contato sem vínculo a compra (formulário)</td>
                            <td>Até 2 anos após o último contato</td>
                            <td>Legítimo interesse</td>
                        </tr>
                        <tr>
                            <td>Dados vinculados a pedidos e histórico de compras</td>
                            <td>Mínimo de <strong>5 anos</strong></td>
                            <td>Defesa em litígios (CPC, art. 206) e CDC</td>
                        </tr>
                        <tr>
                            <td>Cookies funcionais</td>
                            <td>Duração da sessão no navegador</td>
                            <td>Funcionalidade essencial</td>
                        </tr>
                        <tr>
                            <td>Cookies de análise</td>
                            <td>Até 2 anos (conforme política do Google)</td>
                            <td>Legítimo interesse</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="alerta-info">
                <strong>Retenção obrigatória do histórico de compras:</strong> dados vinculados a pedidos e transações não podem ser completamente excluídos durante o período mínimo de 5 anos. Esse prazo existe para resguardar tanto o consumidor quanto a Iraná Natural em eventuais disputas, reclamações e procedimentos legais, com base no art. 206 do CPC e nas disposições do Código de Defesa do Consumidor. Caso exista processo em curso envolvendo os dados em questão, o prazo de retenção é automaticamente estendido até o encerramento definitivo do processo. Findo esse período, os dados serão excluídos ou anonimizados integralmente.
            </div>

            <h2>7. Segurança das transações e dos dados</h2>
            <p>Adotamos medidas técnicas e organizacionais para proteger suas informações contra acesso não autorizado, perda ou alteração:</p>
            <ul>
                <li><strong>HTTPS</strong> — toda a comunicação entre seu navegador e nosso servidor é criptografada via SSL/TLS;</li>
                <li><strong>Criptografia E2E no WhatsApp</strong> — as mensagens trocadas no processo de compra são protegidas por criptografia de ponta a ponta, sem acesso de terceiros ao conteúdo;</li>
                <li><strong>Sem armazenamento de dados de pagamento</strong> — não registramos dados de cartão, senhas ou informações bancárias completas em nosso sistema;</li>
                <li><strong>Acesso restrito</strong> — as informações dos pedidos são acessadas somente pelas pessoas envolvidas no atendimento e produção da Iraná Natural;</li>
                <li><strong>Senhas administrativas protegidas</strong> — as credenciais de acesso ao painel administrativo são armazenadas com algoritmo de hash seguro (bcrypt), nunca em texto legível.</li>
            </ul>
            <p>Embora adotemos boas práticas de segurança, nenhum sistema é completamente inviolável. Em caso de incidente de segurança que possa afetar seus dados, adotaremos as medidas previstas pela LGPD e comunicaremos os titulares afetados dentro dos prazos aplicáveis.</p>

            <h2>8. Seus direitos como titular de dados</h2>
            <p>A LGPD (art. 18) garante a você os seguintes direitos em relação aos seus dados pessoais. Para exercê-los, entre em contato pelo e-mail <a href="mailto:<?= EMAIL_CONTATO ?>"><?= EMAIL_CONTATO ?></a>:</p>
            <ul>
                <li><strong>Confirmação</strong> — verificar se realizamos o tratamento dos seus dados;</li>
                <li><strong>Acesso</strong> — receber cópia dos dados que mantemos sobre você;</li>
                <li><strong>Correção</strong> — solicitar a atualização de dados incompletos, inexatos ou desatualizados;</li>
                <li><strong>Anonimização ou bloqueio</strong> — solicitar que dados desnecessários ou excessivos sejam anonimizados ou bloqueados para uso;</li>
                <li><strong>Eliminação</strong> — solicitar a exclusão dos dados tratados com base em consentimento (sujeito às limitações legais de retenção descritas na seção 6);</li>
                <li><strong>Portabilidade</strong> — receber seus dados em formato estruturado para uso em outros serviços;</li>
                <li><strong>Informação sobre compartilhamento</strong> — saber com quais entidades seus dados são compartilhados;</li>
                <li><strong>Revogação do consentimento</strong> — retirar, a qualquer momento, o consentimento para tratamentos que se baseiem nessa base legal.</li>
            </ul>

            <div class="alerta-info">
                <strong>Limitação ao direito de eliminação do histórico de compras:</strong> o direito de eliminação aplica-se plenamente a dados tratados com base exclusiva em consentimento. Dados vinculados ao histórico de pedidos são tratados com base em <em>execução de contrato</em> (LGPD, art. 7º, V) e <em>obrigação legal</em> (art. 7º, II), razão pela qual são mantidos pelo período mínimo de 5 anos, mesmo mediante solicitação de exclusão. Após esse prazo, os dados serão excluídos ou anonimizados integralmente, salvo existência de processo em curso. Todas as solicitações serão respondidas em até <strong>15 dias úteis</strong>.
            </div>

            <h2>9. Canal de contato para privacidade</h2>
            <p>Para dúvidas, solicitações ou reclamações relacionadas ao tratamento dos seus dados pessoais e a esta política, entre em contato:</p>
            <p>
                <strong>Iraná Natural</strong><br>
                E-mail: <a href="mailto:<?= EMAIL_CONTATO ?>"><?= EMAIL_CONTATO ?></a><br>
                WhatsApp: <a href="<?= Helper::whatsapp() ?>" target="_blank" rel="noopener">Clique aqui para conversar</a>
            </p>
            <p>Nos comprometemos a responder solicitações relacionadas à privacidade em até <strong>72 horas</strong> (3 dias corridos). Poderemos solicitar confirmação de identidade antes de processar pedidos de acesso ou alteração de dados.</p>
            <p>Você também pode registrar reclamações perante a <strong>Autoridade Nacional de Proteção de Dados (ANPD)</strong>, conforme previsto no art. 18, § 1º da LGPD.</p>

            <h2>10. Alterações nesta política</h2>
            <p>Esta política pode ser revisada a qualquer momento para refletir mudanças em nossos processos, nos serviços ofertados ou na legislação aplicável. A data de última atualização, exibida no início desta página, será sempre atualizada quando houver alterações relevantes.</p>
            <p>O uso continuado do site após a publicação de alterações implica ciência e aceitação das novas condições. Para mudanças substanciais, procuraremos comunicar os clientes ativos pelo WhatsApp.</p>

        </div>
    </div>
</section>

<!-- CTA -->
<section class="section-cta">
    <div class="container">
        <div class="cta-box">
            <div class="cta-leaf" aria-hidden="true">🌿</div>
            <h2>Dúvidas sobre privacidade?</h2>
            <p>Entre em contato pelo WhatsApp ou pelo formulário de contato. Respondemos com atenção e dentro dos prazos estabelecidos pela LGPD.</p>
            <a href="<?= Helper::whatsapp() ?>" class="btn btn-light btn-lg" target="_blank" rel="noopener">
                Falar pelo WhatsApp
            </a>
        </div>
    </div>
</section>
