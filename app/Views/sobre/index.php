```php
<?php use App\Core\Helper; ?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="Trilha de navegação">
            <a href="<?= APP_URL ?>/">Início</a>
            <span>›</span>
            <span>Sobre</span>
        </nav>
        <h1>Nossa História</h1>
    </div>
</section>

<section class="section-sobre">
    <div class="container">
        <div class="sobre-intro-grid">
            <div class="sobre-texto">
                <span class="label-small">Quem sou eu</span>
                <!--<h2>Iraná Natural</h2>-->
                <p class="sobre-lead">A Iraná Natural nasceu do meu próprio caminho de autocuidado, reconexão e reencontro com a minha ancestralidade.</p>

                <p>Desde muito cedo, sempre me senti profundamente conectada às ervas, aos aromas e às fragrâncias da natureza. Existe algo de mágico e acolhedor nesse contato — uma sensação de presença, equilíbrio e carinho que transforma o ambiente e também o nosso interior. Com o passar do tempo, percebi que essa conexão fazia parte da minha essência e que eu poderia compartilhá-la com outras pessoas de uma forma verdadeira e afetuosa. Foi assim que a Iraná nasceu.</p>

                <p>A Iraná é muito mais do que uma marca para mim. Ela é uma extensão da minha história, das minhas raízes, dos meus sentimentos e daquilo em que acredito. Cada criação carrega um pouco da minha essência e do meu olhar para o cuidado: simples, sensível, consciente e cheio de significado.</p>

                <p>Aqui, natureza, espiritualidade consciente e afeto caminham juntos, honrando saberes ancestrais e valorizando os pequenos rituais que tornam a vida mais leve, acolhedora e presente.</p>

                <p>Toda a produção é artesanal, feita em pequenos lotes e respeitando o tempo que cada criação merece. Faço questão de acompanhar cada detalhe com carinho e intenção, preparando tudo de forma cuidadosa e consciente para que, ao receber um produto da Iraná, você também possa sentir essa energia de acolhimento e conexão.</p>

                <p>Meu desejo é que cada aroma, cada erva e cada detalhe despertem sensações de bem-estar, presença e reconexão com a sua própria essência — porque acredito que o cuidado mais verdadeiro começa justamente nos pequenos momentos do nosso dia a dia.</p>

                <p style="margin-top:1.5rem;font-weight:600;">
                    Com carinho,<br>
                    Zeli Cabral<br>
                    <span style="font-weight:400;opacity:.8;">Criadora da Iraná Natural</span>
                </p>
            </div>

            <div class="sobre-visual">
                <div class="sobre-card-highlight">
                    <div class="sobre-leaf-icon">🌿</div>
                    <blockquote>"A natureza não tem pressa e ainda assim tudo se realiza."</blockquote>
                    <cite>— Lao Tzu</cite>
                </div>
            </div>
        </div>

        <div class="sobre-valores">
            <div class="valor-item">
                <div class="valor-icon">🌱</div>
                <h3>Naturalidade</h3>
                <p>Utilizamos ingredientes naturais cuidadosamente selecionados, respeitando o tempo, a essência e a sabedoria que a própria natureza oferece.</p>
            </div>

            <div class="valor-item">
                <div class="valor-icon">✨</div>
                <h3>Espiritualidade Consciente</h3>
                <p>Acredito na conexão entre natureza, energia e bem-estar. Cada criação nasce com intenção, respeito e consciência, honrando saberes ancestrais de forma sensível e verdadeira.</p>
            </div>

            <div class="valor-item">
                <div class="valor-icon">🤲</div>
                <h3>Artesanato com Alma</h3>
                <p>Cada produto é preparado artesanalmente, em pequenos lotes, com presença, carinho e atenção aos detalhes. Esse cuidado faz parte da essência da Iraná.</p>
            </div>

            <div class="valor-item">
                <div class="valor-icon">🌍</div>
                <h3>Responsabilidade</h3>
                <p>Busco escolhas mais conscientes em cada etapa, valorizando fornecedores responsáveis, processos respeitosos e embalagens com menor impacto ambiental.</p>
            </div>
        </div>

        <div class="sobre-missao">
            <div class="missao-texto">
                <span class="label-small">Missão</span>
                <h2>O que me move</h2>

                <p>Minha missão é levar para o dia a dia das pessoas mais presença, acolhimento e conexão através das ervas, aromas e elementos naturais. Quero que cada chá, cada incenso e cada banho de ervas se transforme em um pequeno ritual de cuidado e reconexão.</p>

                <p>A Iraná Natural é um convite para desacelerar, sentir e se reconectar com aquilo que realmente importa.</p>

                <a href="<?= APP_URL ?>/produtos" class="btn btn-primary">Conhecer os produtos</a>
                <a href="<?= APP_URL ?>/contato" class="btn btn-outline" style="margin-left:1rem">Entrar em contato</a>
            </div>
        </div>
    </div>
</section>
