# Iraná Natural — Registro de Implementações

---

## [2026-05-07] Melhorias Prioritárias (Segurança, UX, SEO)

### 1. CSRF Protection no formulário de contato

**Arquivos modificados:**
- `app/Controllers/ContatoController.php` — `index()` passa `$csrf = Session::csrfToken()` para a view; `enviar()` verifica `Session::verifyCsrf($_POST['_csrf'])` antes de qualquer processamento
- `app/Views/contato/index.php` — campo `<input type="hidden" name="_csrf" value="...">` adicionado logo após `<form>`

**Implementação:** usa `Session::csrfToken()` (já existia na classe) — token por sessão, gerado com `bin2hex(random_bytes(32))`. Rejeição redireciona para `/contato` com flash de erro.

---

### 2. robots.txt

**Arquivo criado:** `robots.txt` (raiz do projeto — servido diretamente pelo Apache via .htaccess `!-f`)

**Regras:**
- `Allow: /` — indexação pública de todas as páginas relevantes
- `Disallow: /admin/` — painel administrativo bloqueado
- `Disallow: /config/` — arquivos de configuração PHP bloqueados
- `Disallow: /app/` — código-fonte PHP bloqueado
- `Disallow: /uploads/temp/` — diretório temporário bloqueado; `/uploads/` (imagens) permanece acessível
- `Sitemap: https://irananatural.com.br/sitemap.xml`

---

### 3. sitemap.xml

**Arquivo criado:** `sitemap.xml` (raiz do projeto — servido diretamente pelo Apache)

**10 URLs incluídas** com `lastmod`, `changefreq` e `priority`:
- `/` — priority 1.0, weekly
- `/produtos` — priority 0.9, weekly
- `/sobre` — priority 0.7, monthly
- `/como-comprar` — priority 0.8, monthly
- `/pagamento`, `/envio`, `/garantia`, `/trocas` — priority 0.6, monthly
- `/contato` — priority 0.5, monthly
- `/politica-privacidade` — priority 0.4, yearly

**Nota:** URLs de produtos individuais (`/produtos/{cat}/{slug}`) não incluídas — são dinâmicas e dependem de conteúdo no banco. Podem ser adicionadas com um SitemapController dinâmico futuramente.

---

### 4. Active state no menu de navegação

**Arquivo modificado:** `app/Views/layouts/default.php`

**Implementação:** bloco PHP antes do `<header>` define `$_navPath` a partir de `$_GET['url']` e closure `$_active(string $seg): string` que retorna `' active'` quando:
- `$seg === '/'` → match exato (home)
- outros → `str_starts_with($_navPath, $seg)` (cobre subpáginas como `/produtos/cat/slug`)

**CSS:** `.nav-link.active` já existia com `color: var(--verde-floresta); border-bottom-color: var(--verde-floresta)` — nenhum estilo novo necessário.

---

### 5. Cross-links internos

**Arquivos modificados:**
- `app/Views/como-comprar/index.php` — `.section-crosslink` ao final das seções de pagamento (→ `/pagamento`) e entrega (→ `/envio`)
- `app/Views/envio/index.php` — link inline para `/trocas` no alerta de avaria no transporte
- `app/Views/pagamento/index.php` — link inline para `/trocas` no final da seção de recusas
- `app/Views/garantia/index.php` — link inline para `/trocas` na seção de resolução aprovada
- `app/Views/trocas/index.php` — link inline para `/garantia` na seção de defeito de fabricação
- `assets/css/style.css` — nova classe `.section-crosslink` com estilo de texto pequeño + link verde

---

## [2026-05-07] Revisão Geral — Páginas Institucionais (2ª rodada)

### Escopo da revisão
Todas as páginas institucionais implementadas nesta sessão: Como Comprar, Envio, Pagamento, Garantia, Trocas, Política de Privacidade, Contato.

### Problemas encontrados e corrigidos

| # | Severidade | Problema | Correção aplicada |
|---|---|---|---|
| 1 | 🔴 Visual | `garantia`: seções "Prazo" e "Procedimento" tinham fundos idênticos (off-white consecutivo) | Reorganizados os `class` das 4 seções de conteúdo para alternância bege→off-white perfeita; adicionada regra `.section-institucional-alt .passo-numero` no CSS para o box-shadow do número no fundo bege |
| 2 | 🟠 Copy | `politica-privacidade` seção 9: "72 horas úteis" (= 9 dias úteis) conflitava com "15 dias úteis" da seção 8 | Corrigido para "72 horas (3 dias corridos)" — prazo adequado para dúvidas gerais de privacidade |
| 3 | 🟠 Conteúdo | `como-comprar`: Jadlog ausente do grid de entrega (presente em `/envio` mas omitido aqui) | Card "Correios — PAC ou SEDEX" atualizado para "Correios e Jadlog" com descrição cobrindo ambas as transportadoras |
| 4 | 🟡 CSS | `.contato-aviso` usava `!important` para sobrescrever `.contato-item p` | Substituído por seletor mais específico `.contato-item .contato-aviso` sem `!important` |

### Arquivos modificados
- `app/Views/garantia/index.php` — 4 classes de seção trocadas para alternância correta
- `app/Views/politica-privacidade/index.php` — "72 horas úteis" → "72 horas (3 dias corridos)"
- `app/Views/como-comprar/index.php` — card Correios inclui Jadlog
- `assets/css/style.css` — nova regra `.section-institucional-alt .passo-numero`; seletor `.contato-item .contato-aviso` sem `!important`

### Validações confirmadas ✓
- **Alternância de fundos**: todas as páginas alternam bege-claro ↔ off-white corretamente, sem seções consecutivas de mesmo fundo
- **Rotas**: 9 rotas registradas e operacionais (`/`, `/produtos`, `/sobre`, `/contato`, `/como-comprar`, `/politica-privacidade`, `/envio`, `/pagamento`, `/garantia`, `/trocas`)
- **Menus**: navbar com 5 links (Início, Produtos, Sobre, Como Comprar, Contato); sem duplicações
- **Rodapé**: 4 colunas (Marca, Navegação, Informações, Contato); `footer-bottom` com link "Política de Privacidade" → `/politica-privacidade`
- **Breadcrumbs**: todos presentes com `aria-label="Trilha de navegação"`
- **SEO**: `title`, `description`, `canonical`, Open Graph em todas as 9 páginas
- **Acessibilidade**: `aria-hidden` em ícones decorativos; `aria-label` em botões; `role="alert"` nos flashes do formulário; labels associados a inputs
- **Links externos**: todos com `target="_blank" rel="noopener"` (ou `noopener noreferrer` em links não-WhatsApp)
- **Constantes**: `WHATSAPP`, `EMAIL_CONTATO`, `INSTAGRAM_URL`, `APP_URL`, `APP_NAME` usadas consistentemente via constantes e `Helper::whatsapp()`
- **Responsividade**: `.contato-grid` colapsa em 1024px; `.passos-grid` em 768px; `.pagamentos-grid` em 2 col a 480px; `.entrega-grid` em 1 col a 480px; `.footer-grid` em 2 col (1024px) e 1 col (768px)
- **Consistência de componentes**: todos usam `.page-hero`, `.breadcrumb`, `.section-header`, `.label-small`, `.section-cta`, `.btn`, `.btn-light`, `.btn-lg`, `Helper::whatsapp()`
- **Tipografia**: Cormorant Garamond + Lato; paleta verde/bege/marrom; CSS variables em toda a extensão

### Melhorias futuras recomendadas
1. **Cross-links internos**: em "Como Comprar", adicionar "Saiba mais →" nos cards de Pagamento e Entrega apontando para `/pagamento` e `/envio`
2. **Nav active state**: marcar o link da página atual no menu com classe `active` — requer lógica PHP no layout comparando a URL atual
3. **robots.txt e sitemap.xml**: criar para garantir indexação correta das rotas
4. **Fontes auto-hospedadas**: substituir Google Fonts por arquivos locais (melhora LCP e elimina dependência externa)
5. **Schema.org BreadcrumbList**: JSON-LD de breadcrumb nas páginas institucionais para enriquecer snippets no Google
6. **CSRF no formulário de contato**: adicionar token CSRF ao `ContatoController@enviar` para prevenir envios automatizados
7. **og:image por página**: imagens Open Graph específicas por página em vez de uma única imagem padrão

---

## [2026-05-07] Página "Contato" — versão completa

### Rota
`GET /contato` → `ContatoController@index` (rota já existia — view reescrita)
`POST /contato/enviar` → `ContatoController@enviar` (inalterado)

### Arquivos modificados
- `app/Views/contato/index.php` — reescrita completa com mensagem institucional, 5 canais/items e formulário aprimorado
- `app/Controllers/ContatoController.php` — meta description atualizada
- `assets/css/style.css` — novas classes: `.contato-intro`, `.canal-badge`, `.contato-aviso`, `.form-intro`, `.form-obrigatorio`, `.form-privacidade`

### Estrutura da página
1. **Hero** — título + breadcrumb + descrição acolhedora
2. **Mensagem institucional** — `.contato-intro` em `.section-institucional-alt`: posiciona a marca como atendimento humano e artesanal; indica WhatsApp como canal principal
3. **Grid de canais + formulário** — `.section-contato` com `.contato-grid` (2 colunas, colapsa em 1024px):
   - **Esquerda** — 5 itens: WhatsApp (badge "Mais rápido"), E-mail, Instagram, Horário de Atendimento, Retirada Pessoal
   - **Direita** — formulário com intro, campos (nome, e-mail, assunto, mensagem), submit, nota de privacidade com link para `/politica-privacidade`

### Regras de negócio aplicadas
- **WhatsApp**: canal principal — (51) 99229-6036, indicado para pedidos, dúvidas e suporte ágil
- **E-mail**: EMAIL_CONTATO; resposta em até **2 dias úteis**
- **Instagram**: @irananatural; DMs respondidas quando possível (sem SLA)
- **Horário**: Seg–Sex 9h–18h; Sáb 9h–13h; mensagens fora do horário respondidas no próximo período
- **Endereço físico**: não publicado — combinado via WhatsApp após confirmação do pedido
- **FAQ**: não incluído (decisão do cliente)
- **Formulário**: campos com `maxlength`, `autocomplete`, `novalidate` (validação server-side pelo ContatoController existente); nota de privacidade linká à `/politica-privacidade`
- **Estorno cartão**: N/A nesta página
- **Canais adicionais**: nenhum além de WhatsApp, Instagram e e-mail

---

## [2026-05-07] Página "Trocas e Devoluções" — versão completa

### Rota
`GET /trocas` → `TrocasController@index` (rota já existia — view e controller reescritos)

### Arquivos modificados
- `app/Views/trocas/index.php` — reescrita completa com política de trocas e devoluções detalhada
- `app/Controllers/TrocasController.php` — meta description atualizada

### Estrutura da página
1. **Hero** — título + breadcrumb
2. **Motivos aceitos** — `.sobre-valores` com 4 cards: arrependimento, defeito, avaria, produto divergente
3. **Procedimento** — `.passos-grid` com 3 etapas: acionamento WhatsApp → devolução física → análise e resolução
4. **Detalhes por motivo** — `.section-institucional-alt`: regras específicas de cada motivo (prazos, condições, frete)
5. **Frete reverso** — `.section-institucional`: tabela textual de responsabilidade por motivo
6. **Reembolso e estorno** — `.section-institucional-alt`: por forma de pagamento (PIX/TED 3 dias úteis, cartão estorno InfinitePay, dinheiro presencial), troca por equivalente
7. **Recusas** — `.section-institucional`: fora do prazo, produto com uso (arrependimento), mau uso, vencido, alergia a ingrediente listado
8. **CTA** — botão WhatsApp

### Regras de negócio aplicadas
- **Arrependimento** (CDC art. 49): 7 dias corridos do recebimento; produto lacrado sem uso; frete retorno por conta do cliente; reembolso somente valor do produto (frete original não reembolsado)
- **Defeito de fabricação** (CDC art. 26, I): 30 dias corridos; frete retorno por conta da Iraná Natural
- **Avaria no transporte**: 30 dias corridos; frete retorno por conta da Iraná Natural
- **Produto divergente**: 30 dias corridos; frete retorno por conta da Iraná Natural
- **Análise**: 2 dias úteis após recebimento do produto devolvido
- **Reembolso**: até 3 dias úteis após aprovação; PIX/TED direto; estorno no cartão via InfinitePay (prazo adicional conforme banco emissor)
- **Troca**: produto equivalente enviado sem custo de frete adicional; prazo informado pelo WhatsApp
- **Recusas**: fora do prazo, produto aberto/com uso (arrependimento), mau uso, armazenamento inadequado, após validade, reação a ingrediente listado
- **Exceção alergia**: coberta se o ingrediente causador NÃO estava listado na composição (erro de descrição)
- **Produtos personalizados**: não existem — todos são de linha padrão; nenhuma exclusão adicional de arrependimento

---

## [2026-05-07] Página "Garantia de Qualidade" — versão completa

### Rota
`GET /garantia` → `GarantiaController@index` (rota já existia — view e controller reescritos)

### Arquivos modificados
- `app/Views/garantia/index.php` — reescrita completa com política de garantia detalhada
- `app/Controllers/GarantiaController.php` — meta description atualizada

### Estrutura da página
1. **Hero** — título + breadcrumb
2. **Compromisso de qualidade** — `.sobre-valores` com 3 cards: ingredientes naturais, produção artesanal, embalagem protetora
3. **Prazo e elegibilidade** — `.institucional-conteudo`: prazo 30 dias CDC art. 26 I (não duráveis), vícios ocultos (CDC art. 26 §3º), produtos elegíveis
4. **Procedimento** — `.passos-grid` com 3 etapas: contato WhatsApp → devolução física → análise e resolução
5. **Análise e condições de aprovação** — `.section-institucional-alt`: o que é avaliado, condições para aprovação, opções de resolução (troca ou reembolso CDC art. 18)
6. **Exclusões** — `.section-institucional`: mau uso, armazenamento inadequado, produto vencido, reações a ingredientes listados, resultado estético subjetivo
7. **Responsabilidades do cliente** — `.section-institucional-alt`: inspeção no recebimento, armazenamento correto, uso dentro do prazo, leitura de ingredientes, acionamento no prazo
8. **CTA** — botão WhatsApp

### Regras de negócio aplicadas
- **Classificação CDC**: produtos não duráveis (cosméticos, sabonetes, óleos) → prazo de 30 dias corridos (CDC art. 26, I)
- **Vícios ocultos**: prazo começa da descoberta do defeito (CDC art. 26, §3º)
- **Garantia adicional**: não há prazo contratual adicional fixo — análise caso a caso com boa-fé
- **Devolução física**: obrigatória antes da aprovação — análise não é feita apenas por fotos
- **Frete de retorno**: Iraná Natural arca com os custos em casos de defeito de fabricação ou dano no transporte
- **Prazo de análise**: até 2 dias úteis após o recebimento do produto devolvido
- **Resolução**: troca por produto equivalente ou reembolso via PIX/TED — definido caso a caso (CDC art. 18)
- **Reações alérgicas**: cobertas apenas se o ingrediente causador NÃO estava listado na descrição (erro de omissão); reações a ingredientes listados são responsabilidade do cliente
- **Exclusões**: mau uso, armazenamento inadequado, uso após validade, resultado estético subjetivo, reação a ingredientes listados

---

## [2026-05-07] Página "Pagamento" — versão completa

### Rota
`GET /pagamento` → `PagamentoController@index` (rota já existia — apenas a view foi reescrita)

### Arquivo modificado
- `app/Views/pagamento/index.php` — reescrita completa com conteúdo financeiro e operacional detalhado
- `app/Controllers/PagamentoController.php` — meta description atualizada

### Estrutura da página
1. **Hero** — título + breadcrumb
2. **Métodos aceitos** — `.pagamentos-grid` com 4 cards: PIX, Transferência Bancária, Cartão Crédito/Débito, Dinheiro
3. **Fluxo de pagamento** — `.passos-grid` com 3 etapas: escolha → informe como pagar → pedido confirmado
4. **Detalhes por método** — `.section-institucional-alt` / `.institucional-conteudo`: PIX (chave aleatória, 24h), TED/DOC (mesmo dia / próximo dia útil), Cartão (link InfinitePay + maquininha + parcelamento com juros), Dinheiro (retirada)
5. **Segurança das transações** — `.section-institucional`: InfinitePay PCI DSS, E2E WhatsApp, sem armazenamento de dados de cartão
6. **Confirmação e recusas** — `.section-institucional-alt`: prazos por método, pedido não pago no prazo, recusa de cartão (motivos + orientações)
7. **CTA** — botão WhatsApp

### Regras de negócio aplicadas
- **PIX**: chave aleatória enviada pelo WhatsApp; válida por **24 horas**; após o prazo a reserva é cancelada
- **TED**: compensação no mesmo dia útil (dentro do horário bancário)
- **DOC**: compensação no próximo dia útil
- **Cartão de crédito**: link InfinitePay ou maquininha na retirada; parcelamento em até 12x **com juros** (taxas informadas antes de confirmar — valores serão detalhados em atualização futura)
- **Cartão de débito**: à vista, via link InfinitePay ou maquininha presencial
- **Dinheiro**: exclusivamente na retirada pessoal, troco disponível se informado antes
- **Dados de cartão**: processados integralmente pela InfinitePay (PCI DSS); Iraná Natural não tem acesso
- **Recusa de cartão**: cliente deve contatar o banco emissor; alternativas (PIX, TED) oferecidas pelo WhatsApp
- **Pedido não pago**: reserva cancelada após 24h; reaberto via WhatsApp se disponível

---

## [2026-05-07] Página "Envio" — versão completa

### Rota
`GET /envio` → `EnvioController@index` (rota já existia — apenas a view foi reescrita)

### Arquivo modificado
- `app/Views/envio/index.php` — reescrita completa com conteúdo logístico detalhado

### Estrutura da página
1. **Hero** — título + breadcrumb
2. **Jornada do pedido** — `.passos-grid` com 3 etapas: confirmação → embalagem → postagem e rastreamento
3. **Formas de envio** — `.entrega-grid` com 6 cards: Correios PAC, Correios SEDEX, Jadlog, Motoboy/Uber Flash, Entrega local, Retirada pessoal
4. **Frete e rastreamento** — `.institucional-conteudo`: cálculo via WhatsApp, links de rastreamento Correios e Jadlog
5. **Políticas de entrega** — `.institucional-conteudo`: prazo de postagem, possíveis atrasos, endereço incorreto, tentativas, pacote devolvido
6. **CTA** — botão WhatsApp

### Regras de negócio aplicadas
- **Prazo de postagem**: sem prazo fixo — combinado caso a caso via WhatsApp antes da confirmação
- **Frete grátis**: não existe — custo sempre calculado e aprovado antes de confirmar
- **Endereço incorreto**: custo de reenvio é responsabilidade do cliente
- **Tentativas Correios**: 3 tentativas; se sem sucesso, objeto retorna ao remetente
- **Jadlog**: tentativas conforme política interna da transportadora (não fixado)
- **Pacote devolvido**: cliente paga novo frete; produto reservado por 30 dias aguardando contato
- **Rastreamento**: código enviado via WhatsApp após postagem; links para rastreamento.correios.com.br e Jadlog

---

## [2026-05-07] Política de Privacidade e Segurança (LGPD robusta)

### Rota
`GET /politica-privacidade` → `PoliticaPrivacidadeController@index`
Rota anterior `GET /seguranca` **removida** (controller e view de `/seguranca` permanecem no disco como arquivos órfãos — podem ser excluídos manualmente quando conveniente).

### Arquivos criados
- `app/Controllers/PoliticaPrivacidadeController.php`
- `app/Views/politica-privacidade/index.php`

### Arquivos modificados
- `index.php` — rota `/seguranca` substituída por `/politica-privacidade`
- `app/Views/layouts/default.php` — link do rodapé atualizado de `/seguranca` para `/politica-privacidade`; link "Política de Privacidade" adicionado ao `footer-bottom`
- `assets/css/style.css` — novos estilos: `.politica-data`, `.politica-table-wrap`, `table`/`th`/`td` dentro de `.institucional-conteudo`, `code` dentro de `.institucional-conteudo`, `.footer-bottom a`

### Estrutura da página
1. **Hero** — título + breadcrumb
2. **Visão geral** — 4 cards de compromisso (coleta mínima, LGPD, transações seguras, direitos)
3. **Política completa** (10 seções em `.institucional-conteudo`):
   - Seção 1: Quem somos / Controladora
   - Seção 2: Dados coletados (fornecidos + automáticos + o que NÃO coletamos)
   - Seção 3: Finalidade e bases legais — tabela com base LGPD art. 7º por tipo de dado
   - Seção 4: Cookies — tabela funcionais (PHPSESSID) + analytics (GA4: _ga, _gid, _ga_*)
   - Seção 5: Compartilhamento — Correios, Google, operadoras, autoridades
   - Seção 6: Retenção — tabela com prazos + alerta sobre 5 anos de histórico de compras
   - Seção 7: Segurança das transações — HTTPS, E2E WhatsApp, bcrypt, sem dados de cartão
   - Seção 8: Direitos do titular — 8 direitos LGPD art. 18 + alerta de limitação para histórico
   - Seção 9: Canal de contato — e-mail + WhatsApp + menção à ANPD
   - Seção 10: Alterações da política
4. **CTA** — botão WhatsApp

### Regras de negócio críticas aplicadas
- **Sem CNPJ**: controladora identificada apenas como "Iraná Natural" com e-mail de contato
- **Google Analytics (GA4)**: cookies _ga, _gid, _ga_* documentados com duração e opt-out via Google
- **DPO**: não há DPO designado — contato@irananatural.com.br responde por assuntos de privacidade
- **Retenção de histórico de compras**: mínimo de **5 anos** por defesa em litígios (CPC art. 206) e CDC
- **Limitação de exclusão**: dados vinculados a histórico de pedidos não podem ser excluídos durante os 5 anos; política explica a limitação e o fundamento legal (LGPD art. 7º, II e V); solicitações respondidas em até 15 dias úteis
- **Menção à ANPD**: titular pode recorrer à Autoridade Nacional de Proteção de Dados (LGPD art. 18, § 1º)

---

## [2026-05-07] Página "Como Comprar"

### Rota
`GET /como-comprar` → `ComoComprarController@index`

### Arquivos criados
- `app/Controllers/ComoComprarController.php`
- `app/Views/como-comprar/index.php`

### Arquivos modificados
- `index.php` — rota `como-comprar` registrada
- `app/Views/layouts/default.php` — link "Como Comprar" adicionado ao menu principal e ao rodapé
- `assets/css/style.css` — estilos das seções: `.passos-grid`, `.pagamento-card`, `.entrega-card` e responsivos

### Estrutura da página
1. **Hero** — título + breadcrumb (padrão `.page-hero`)
2. **Passos** — grid 3 colunas com linha conectora decorativa, colapsa em 1 coluna no mobile (≤768px)
3. **Pagamentos** — 4 cards: PIX, Transferência Bancária, Cartão, Dinheiro
4. **Entrega** — 4 cards: Correios, Motoboy/Uber Flash, Entrega Local, Retirada Pessoal
5. **CTA** — botão WhatsApp (reutiliza `.section-cta` existente)

### Regras de negócio aplicadas
- Processo de compra exclusivamente via WhatsApp (sem carrinho)
- Pagamentos: PIX, TED/DOC, cartão (link ou maquininha), dinheiro (só retirada)
- Entrega: Correios (todo Brasil), motoboy/Uber, entrega local e retirada — região Porto Alegre / Grande POA
- Prazos e valores de frete não fixados na página — combinados pelo WhatsApp

---

## [2026-05-07] Páginas Institucionais (5 páginas)

### Rotas criadas
| Rota | Controller |
|---|---|
| `GET /seguranca` | `SegurancaController@index` |
| `GET /envio` | `EnvioController@index` |
| `GET /pagamento` | `PagamentoController@index` |
| `GET /garantia` | `GarantiaController@index` |
| `GET /trocas` | `TrocasController@index` |

### Arquivos criados
- `app/Controllers/SegurancaController.php`
- `app/Controllers/EnvioController.php`
- `app/Controllers/PagamentoController.php`
- `app/Controllers/GarantiaController.php`
- `app/Controllers/TrocasController.php`
- `app/Views/seguranca/index.php`
- `app/Views/envio/index.php`
- `app/Views/pagamento/index.php`
- `app/Views/garantia/index.php`
- `app/Views/trocas/index.php`

### Arquivos modificados
- `index.php` — 5 novas rotas registradas
- `app/Views/layouts/default.php` — nova coluna "Informações" no rodapé com links para as 5 páginas
- `assets/css/style.css` — classes `.institucional-conteudo`, `.alerta-info`, `.section-institucional`, `.section-institucional-alt`; footer-grid expandido de 3 para 4 colunas

### Regras de negócio aplicadas
- **Segurança/LGPD**: sem CNPJ — usa apenas "Iraná Natural" e e-mail de contato; dados coletados: telefone/WhatsApp e endereço de entrega
- **Envio**: Iraná Natural arca com frete de retorno em caso de defeito ou dano no transporte
- **Pagamento**: cartão de crédito em até 12x (link ou maquininha); dinheiro apenas na retirada
- **Garantia**: satisfação garantida, análise caso a caso pelo WhatsApp
- **Trocas**: prazo de 7 dias corridos (CDC); Iraná arca com frete de retorno em caso de defeito; devolução por arrependimento: produto lacrado, frete por conta do cliente
- **Contato**: página já existia em `/contato` — ignorada nesta implementação

---

## [2026-05-07] Revisão Geral — Páginas Institucionais

### Problemas encontrados e corrigidos

| # | Severidade | Problema | Correção aplicada |
|---|---|---|---|
| 1 | 🔴 Visual | "Como Comprar" duplicado no rodapé (Navegação e Informações) | Removido de `footer-info`; mantido apenas em `footer-links` |
| 2 | 🔴 Visual | `.alerta-info` invisível dentro de `.section-institucional-alt` (mesma cor de fundo) | Adicionada regra `.section-institucional-alt .alerta-info { background: var(--branco) }` |
| 3 | 🟠 UX | `.passos-grid` colapsava em 1 coluna em 1024px — tablets têm espaço para 3 colunas | Regra movida de `@media (max-width: 1024px)` para `@media (max-width: 768px)` |
| 4 | 🟠 Consistência | `seguranca/index.php` era a única página sem CTA ao final | Adicionada `.section-cta` com botão WhatsApp |
| 5 | 🟡 Copy | CTA em `como-comprar` usava "Fale" (imperativo); outras usam "Falar" (infinitivo) | Padronizado para "Falar pelo WhatsApp" |

### Validações confirmadas ✓
- Rotas: todas as 6 rotas registradas e operacionais em `index.php`
- Menus: navbar com 5 links corretos (Início, Produtos, Sobre, Como Comprar, Contato); sem itens duplicados
- Rodapé: 4 colunas (Marca, Navegação, Informações, Contato); sem duplicações após correção
- Breadcrumbs: todos corretos e com `aria-label="Trilha de navegação"`
- SEO: `title`, `description`, `canonical` e Open Graph em todas as 6 páginas
- Acessibilidade: `aria-hidden` em ícones decorativos; `aria-label` em botões de ação
- Links externos (WhatsApp): todos com `target="_blank" rel="noopener"`
- Identidade visual: Cormorant Garamond + Lato, paleta verde/bege/marrom, CSS variables em toda a extensão
- Responsividade: breakpoints 1024px, 768px, 480px cobrindo todas as novas seções

### Melhorias futuras recomendadas
1. **Cross-links internos**: adicionar links de "Saiba mais" em "Como Comprar" apontando para `/pagamento` e `/envio`
2. **robots.txt e sitemap.xml**: criar para garantir indexação correta das novas rotas
3. **Fontes auto-hospedadas**: substituir Google Fonts por arquivos locais para melhorar LCP e eliminar dependência externa
4. **Schema.org BreadcrumbList**: adicionar JSON-LD de breadcrumb nas páginas institucionais para enriquecer SERP
5. **Política de cookies**: se o site crescer e usar analytics, adicionar aviso de cookies integrado à `/seguranca`
6. **Link "Política de Privacidade" no footer-bottom**: adicionar link para `/seguranca` na barra inferior do rodapé (boa prática legal)

---

## [2026-05-07] Auditoria de Resíduos — Limpeza de Arquivos Órfãos

### Escopo
Auditoria completa de arquivos, pastas, assets, scripts e estruturas não utilizados.

### REMOVIDOS (seguro — confiança alta)

| Arquivo/Pasta | Motivo |
|---|---|
| `app/Controllers/SegurancaController.php` | Controlador órfão — rota `/seguranca` removida do `index.php` em implementação anterior; substituído por `PoliticaPrivacidadeController` |
| `app/Views/seguranca/index.php` + diretório | View órfã — carregada exclusivamente pelo controlador acima; conteúdo superseded por `politica-privacidade/index.php` |
| `error_log` (raiz) | Log de erros PHP em runtime, 1.885 linhas — não é código do projeto; expõe caminhos absolutos e detalhes do servidor se acessado |

### CORRIGIDO — Bug crítico de imagens

**`assets/images/` estava completamente vazio.** O sistema de templates referencia:
- `assets/images/logo.png` — header, footer, admin login, admin layout
- `assets/images/favicon.png` — aba do browser
- `assets/images/og-default.jpg` — OG:image de fallback para produtos sem foto

**Correções aplicadas:**
- `assets/img/logo.png` copiado → `assets/images/logo.png`
- `img/ico/favicon_32x32.png` copiado → `assets/images/favicon.png`

**Pendência:** `assets/images/og-default.jpg` ainda não existe. Criar uma imagem OG de fallback (1200×630px, JPEG) com identidade visual da marca e salvar neste caminho.

### PROTEGIDO — setup/

`setup/.htaccess` criado com `Deny from all` — impede acesso web direto aos scripts de instalação/migração (`install.php`, `migrate_v1_1.php`, `migrate_v1_2.php`). `robots.txt` atualizado com `Disallow: /setup/`.

### SIMPLIFICADO — JS active nav

Bloco "Ativar link de navegação corrente" removido de `assets/js/main.js` (linhas 89–99). Lógica idêntica já existia via closure PHP `$_active()` no `default.php` — remoção elimina redundância e fonte de verdade duplicada.

### PRECISA VALIDAÇÃO MANUAL (não removido automaticamente)

| Arquivo/Pasta | Situação |
|---|---|
| `assets/img/` (logo.png, logo1.png) | Nenhuma referência de código aponta para `assets/img/`. Após cópia para `assets/images/`, pode ser removido. |
| `img/` (raiz — ico/ e logo/) | Nenhuma referência de código. `ico/` tem 8 arquivos de favicon; `logo/` tem 2 logos circulares. Após resolver o og-default.jpg, pode ser removido. |


---

## [2026-05-07] Correção do Sistema de Imagens e Banner

### Causa raiz identificada

4 problemas distintos, todos com causas estruturais:

| # | Problema | Localização | Causa |
|---|---|---|---|
| 1 | Imagens quebradas em dev | `config/app.php` | `APP_URL` hardcoded para `https://irananatural.com.br`; em ambiente local, `Helper::upload()` gera URLs para domínio de produção → browser recebe página PHP de 404 como conteúdo da imagem |
| 2 | Sem fallback para arquivo ausente | `home/index.php`, `produtos/index.php`, `produtos/show.php` | `<img>` sem `onerror`; quando o arquivo existe no banco mas não no disco, exibe ícone de imagem quebrada ao invés do placeholder |
| 3 | Banner com slider (errado) | `home/index.php` | Multi-slide com prev/next/dots/animação automática; comportamento correto é banner estático único, sem controles |
| 4 | Galeria de produto sem navegação | `produtos/show.php` e `main.js` | Galeria de detalhe tinha apenas miniaturas com `onclick` global; sem setas, dots ou suporte a swipe |

### Solução aplicada

**`config/app.php`** — APP_URL dinâmico:
```php
if (isset($_SERVER['HTTP_HOST'])) {
    $scheme = ... ? 'https' : 'http';
    define('APP_URL', $scheme . '://' . $_SERVER['HTTP_HOST']);
} else {
    define('APP_URL', 'https://irananatural.com.br'); // fallback CLI
}
```
Funciona em dev (localhost/qualquer host) e produção sem configuração adicional.

**`assets/images/placeholder.svg`** — criado SVG neutral com paleta da marca (bege #F5EFE3, stroke #C5B9AA).

**`onerror` em todos os `<img>` de upload**:
```html
onerror="this.onerror=null;this.src='/assets/images/placeholder.svg'"
```
Cobertura: home destaques, listagem de produtos, galeria principal do produto, miniaturas, produtos relacionados.

**`home/index.php`** — banner simplificado:
- Removido `banner-slider` com múltiplos slides e JS
- Novo `.banner-hero` estático: exibe apenas `$banners[0]` sem controles
- Fallback `hero-default` preservado para quando não há banners cadastrados

**`produtos/show.php`** — galeria de detalhe redesenhada:
- Setas `#galeria-prev` / `#galeria-next` sobrepostas no `.galeria-main` (apenas se > 1 imagem)
- `.galeria-dots` com `role="tablist"` e `aria-selected` por dot
- Miniaturas mantidas para desktop
- `onclick="trocarImagem(this)"` removido (substituído por event listeners)

**`assets/js/main.js`** — limpeza e novo módulo:
- Removido: IIFE do banner slider (morto)
- Removido: função global `trocarImagem` (substituída)
- Adicionado: IIFE `Galeria de produto` com prev/next, dots sync, thumb sync e swipe touch (threshold 40px)

**`assets/css/style.css`** — novos estilos:
- `.banner-hero` — banner estático com `background-size: cover`, alturas responsivas (520/360/300px)
- `.galeria-prev`, `.galeria-next` — setas sobre `.galeria-main` (position absolute, z-index 5)
- `.galeria-dot` / `.galeria-dot.active` — indicadores com transição de escala
- `.galeria-main { position: relative }` e `img { transition: opacity 0.18s }`
- Media queries atualizadas para `.banner-hero`

### Componentes alterados

| Arquivo | Tipo de alteração |
|---|---|
| `config/app.php` | APP_URL dinâmico |
| `app/Views/home/index.php` | Banner estático + onerror |
| `app/Views/produtos/index.php` | onerror na listagem |
| `app/Views/produtos/show.php` | Galeria completa: prev/next/dots/swipe/onerror |
| `assets/css/style.css` | .banner-hero + galeria nav |
| `assets/js/main.js` | Remove slider/trocarImagem, adiciona galeria IIFE |
| `assets/images/placeholder.svg` | Criado — SVG de fallback para imagens ausentes |

### Comportamento após correção

- **Home banner**: 1 imagem estática, sem controles, sem animação. Fallback gradient se sem banner
- **Listagem**: card-gallery com prev/next/dots em hover (comportamento inalterado — era correto)
- **Produto detalhe (1 imagem)**: imagem principal estática, sem setas/dots
- **Produto detalhe (N imagens)**: seta ← imagem → seta + dots + miniaturas + swipe mobile
- **Imagem ausente em qualquer página**: placeholder SVG neutro ao invés de ícone quebrado
- **Ambiente dev**: URLs geradas com `http://localhost` (ou host atual), não mais `https://irananatural.com.br`

### Melhorias futuras recomendadas

1. **Lazy loading na galeria de detalhe**: pré-carregar imagem seguinte em background para transição sem flash
2. **Zoom na imagem principal**: modal ou zoom in-place ao clicar (melhora UX desktop)
3. **Aspect ratio automático**: galeria adaptar altura conforme dimensão real das imagens (evitar crop agressivo em imagens não-quadradas)
4. **WebP**: converter uploads para WebP no upload via admin (redução de 30-40% do tamanho)
5. **og-default.jpg**: criar imagem OG de fallback 1200×630 com identidade visual para produtos sem foto

