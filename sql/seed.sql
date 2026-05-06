-- =====================================================
-- Iraná Natural — Dados iniciais de demonstração
-- Execute APÓS schema.sql
-- Senha do admin: Iran@2024
-- =====================================================

SET NAMES utf8mb4;

-- Admin (senha gerada pelo setup/install.php)
-- Execute setup/install.php para criar o usuário com hash correto
-- Este INSERT é apenas referência; o install.php sobrescreve
INSERT INTO usuarios (nome, email, senha) VALUES
('Administrador', 'admin@irananatural.com.br', 'RODAR_INSTALL_PHP') ON DUPLICATE KEY UPDATE nome = nome;

-- Configurações padrão
INSERT INTO configuracoes (chave, valor, descricao) VALUES
('site_nome',        'Iraná Natural',                                'Nome do site'),
('site_slogan',      'Natureza em cada detalhe',                    'Slogan do site'),
('whatsapp',         '5551992296036',                               'Número WhatsApp'),
('email_contato',    'contato@irananatural.com.br',                 'E-mail de contato'),
('instagram',        'https://instagram.com/irananatural',          'Link Instagram'),
('horario',          'Seg–Sex: 9h–18h | Sáb: 9h–13h',              'Horário de atendimento'),
('sobre_resumo',     'A Iraná Natural nasceu do amor pelas ervas, pela terra e pela espiritualidade consciente. Cada produto é elaborado com cuidado artesanal, respeitando os ciclos da natureza e honrando a sabedoria ancestral das plantas.', 'Texto resumo da página sobre'),
('endereco',         'Rio Grande do Sul, Brasil',                   'Endereço de contato')
ON DUPLICATE KEY UPDATE valor = VALUES(valor);

-- Categorias
INSERT INTO categorias (nome, slug, descricao, ordem) VALUES
('Banhos',      'banhos',      'Banhos de ervas para proteção, equilíbrio e bem-estar espiritual.',            1),
('Chás',        'chas',        'Blends de ervas medicinais e espirituais para o seu ritual diário.',           2),
('Escalda Pés', 'escalda-pes', 'Escalda pés terapêuticos com ervas e sais naturais para descanso profundo.', 3),
('Incensos',    'incensos',    'Incensos artesanais com resinas e ervas para ambiente e meditação.',           4),
('Tabacos',     'tabacos',     'Tabacos sagrados e misturas rituais preparados com intenção.',                 5)
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- Insumos — Incensos
INSERT INTO insumos (nome, descricao, unidade_medida, estoque_atual, estoque_minimo, custo_medio, fornecedor) VALUES
('Resina Olíbano',      'Resina natural de olíbano (frankincense), grãos',  'g',   500,  100, 0.08,  'Ervas do Sul'),
('Resina Mirra',        'Resina natural de mirra, grãos',                   'g',   300,   50, 0.12,  'Ervas do Sul'),
('Arruda Seca',         'Arruda desidratada e triturada',                   'g',   800,  100, 0.03,  'Produção própria'),
('Alecrim Seco',        'Alecrim desidratado e triturado',                  'g',  1000,  100, 0.02,  'Produção própria'),
('Lavanda Seca',        'Flores de lavanda secas',                          'g',   400,   80, 0.05,  'Floralia'),
('Rosa Vermelha Seca',  'Pétalas de rosa vermelha secas',                   'g',   300,   50, 0.07,  'Floralia'),
('Sândalo em Pó',       'Pó de sândalo branco',                            'g',   200,   50, 0.15,  'Ervas do Sul'),
('Carvão Vegetal Pó',   'Carvão vegetal em pó fino (combustão)',            'g',  1500,  200, 0.01,  'Distribuidora ABC'),
('Nitrato de Potássio', 'Salitre (agente de combustão)',                    'g',   500,  100, 0.02,  'Distribuidora ABC'),
('Embalagem Kraft P',   'Caixinha kraft pequena 5x5x3cm',                   'un',   200,   50, 0.80,  'Embalagens RS'),
-- Insumos — Chás
('Erva-doce',           'Erva-doce desidratada',                            'g',   600,   80, 0.025, 'Produção própria'),
('Camomila',            'Flores de camomila secas',                         'g',   400,   80, 0.04,  'Floralia'),
('Capim-limão',         'Capim-limão desidratado',                          'g',   500,   80, 0.02,  'Produção própria'),
('Embalagem Kraft M',   'Caixinha kraft média 8x5x3cm',                     'un',   150,   30, 1.20,  'Embalagens RS'),
('Rótulo Impresso',     'Rótulo personalizado Iraná Natural',               'un',   500,   50, 0.35,  'Gráfica Local');

-- Produtos
-- Categoria Incensos = ID 4, Chás = ID 2
SET @cat_incensos = (SELECT id FROM categorias WHERE slug = 'incensos');
SET @cat_chas     = (SELECT id FROM categorias WHERE slug = 'chas');

INSERT INTO produtos (categoria_id, nome, slug, descricao_curta, descricao_completa, composicao, modo_uso, cuidados, preco_venda, margem_desejada, estoque_atual, estoque_minimo, ativo) VALUES
(@cat_incensos,
 'Incenso Talismã',
 'incenso-talismo',
 'Incenso artesanal formulado para atrair boas energias, prosperidade e proteção espiritual.',
 'O Incenso Talismã é uma formulação exclusiva da Iraná Natural, preparada com resinas preciosas e ervas cuidadosamente selecionadas. Seu aroma profundo e amadeirado cria uma atmosfera de proteção e elevação espiritual, auxiliando na meditação, nos rituais e na purificação dos ambientes.',
 'Resina olíbano, mirra, sândalo em pó, alecrim, arruda.',
 'Coloque uma pitada do incenso sobre um carvão aceso em um porta-incenso resistente ao calor. Deixe a fumaça perfumar o ambiente com intenção.',
 'Mantenha fora do alcance de crianças. Use em ambientes ventilados. Não deixe o carvão incandescente sem supervisão.',
 18.00, 60.00, 20, 5, 1),

(@cat_incensos,
 'Incenso Sereno',
 'incenso-sereno',
 'Blend calmante de ervas e resinas para trazer paz, serenidade e equilíbrio emocional.',
 'Formulado para momentos de introspecção e cura emocional, o Incenso Sereno combina a suavidade da lavanda com a doçura da camomila e as resinas purificadoras. Ideal para meditação, yoga, rituais de cura e para criar ambientes de paz e acolhimento.',
 'Lavanda, camomila, resina olíbano, rosa vermelha.',
 'Use sobre carvão aceso em recipiente adequado. Respire profundamente e permita que a fumaça leve suas tensões.',
 'Mantenha fora do alcance de crianças. Use em ambientes ventilados.',
 16.00, 55.00, 15, 5, 1),

(@cat_incensos,
 'Incenso Rubro',
 'incenso-rubro',
 'Incenso de resinas vermelhas e pétalas de rosa para rituais de amor e atração.',
 'O Incenso Rubro foi criado para rituais de amor, atração e conexão emocional. A combinação de pétalas de rosa com mirra e sândalo produz um aroma profundamente sensorial, quente e envolvente, ideal para rituais de autoamor e para criar ambientes de intimidade e romance.',
 'Rosa vermelha, mirra, sândalo em pó, lavanda.',
 'Utilize sobre carvão incandescente. Acenda com intenção e deixe a fragrância preencher o espaço.',
 'Mantenha fora do alcance de crianças. Evite inalação direta da fumaça.',
 18.00, 60.00, 10, 5, 1),

(@cat_incensos,
 'Incenso Arruda',
 'incenso-arruda',
 'Incenso purificador de arruda para limpeza energética e proteção espiritual.',
 'A arruda é uma das ervas mais conhecidas na espiritualidade popular brasileira por seu poder de limpeza e proteção. O Incenso Arruda da Iraná Natural concentra todo o poder desta planta sagrada em uma formulação pura e eficaz para defumação e banhos de descarrego.',
 'Arruda seca, resina olíbano, alecrim.',
 'Use sobre carvão aceso para defumação de ambientes, pessoas e objetos. Direcione a fumaça com intenção clara de limpeza e proteção.',
 'Uso ritual. Mantenha fora do alcance de crianças.',
 15.00, 50.00, 25, 5, 1),

(@cat_chas,
 'Chá Desatar',
 'cha-desatar',
 'Blend de ervas medicinais para soltar bloqueios, aliviar tensões e facilitar novos começos.',
 'O Chá Desatar foi elaborado para quem sente o peso de situações travadas, de emoções represadas ou de ciclos que não se fecham. Sua fórmula une ervas com propriedades digestivas, calmantes e energeticamente libertadoras, criando um ritual de chá que cuida do corpo e da alma.',
 'Erva-doce, camomila, capim-limão, alecrim.',
 'Infuse 1 colher de chá em 200ml de água quente (não fervente, ~80°C) por 5 minutos. Beba com presença e intenção.',
 'Produto natural, sem conservantes. Consulte um profissional de saúde em caso de gravidez ou uso de medicamentos. Conserve em local seco.',
 25.00, 65.00, 12, 3, 1);

-- Fichas técnicas (composição dos produtos — por unidade)
-- Produto: Incenso Talismã
SET @p_talisma = (SELECT id FROM produtos WHERE slug = 'incenso-talismo');
SET @i_olibano = (SELECT id FROM insumos WHERE nome = 'Resina Olíbano');
SET @i_mirra   = (SELECT id FROM insumos WHERE nome = 'Resina Mirra');
SET @i_arruda  = (SELECT id FROM insumos WHERE nome = 'Arruda Seca');
SET @i_alecrim = (SELECT id FROM insumos WHERE nome = 'Alecrim Seco');
SET @i_lavanda = (SELECT id FROM insumos WHERE nome = 'Lavanda Seca');
SET @i_rosa    = (SELECT id FROM insumos WHERE nome = 'Rosa Vermelha Seca');
SET @i_sandalo = (SELECT id FROM insumos WHERE nome = 'Sândalo em Pó');
SET @i_carvao  = (SELECT id FROM insumos WHERE nome = 'Carvão Vegetal Pó');
SET @i_salitre = (SELECT id FROM insumos WHERE nome = 'Nitrato de Potássio');
SET @i_embP    = (SELECT id FROM insumos WHERE nome = 'Embalagem Kraft P');
SET @i_erva    = (SELECT id FROM insumos WHERE nome = 'Erva-doce');
SET @i_camom   = (SELECT id FROM insumos WHERE nome = 'Camomila');
SET @i_capim   = (SELECT id FROM insumos WHERE nome = 'Capim-limão');
SET @i_embM    = (SELECT id FROM insumos WHERE nome = 'Embalagem Kraft M');
SET @i_rotulo  = (SELECT id FROM insumos WHERE nome = 'Rótulo Impresso');

INSERT INTO fichas_tecnicas (produto_id, insumo_id, quantidade, unidade) VALUES
(@p_talisma, @i_olibano, 3,  'g'),
(@p_talisma, @i_mirra,   2,  'g'),
(@p_talisma, @i_sandalo, 1,  'g'),
(@p_talisma, @i_alecrim, 2,  'g'),
(@p_talisma, @i_arruda,  2,  'g'),
(@p_talisma, @i_carvao,  3,  'g'),
(@p_talisma, @i_salitre, 1,  'g'),
(@p_talisma, @i_embP,    1,  'un'),
(@p_talisma, @i_rotulo,  1,  'un');

-- Produto: Incenso Sereno
SET @p_sereno = (SELECT id FROM produtos WHERE slug = 'incenso-sereno');
INSERT INTO fichas_tecnicas (produto_id, insumo_id, quantidade, unidade) VALUES
(@p_sereno, @i_lavanda, 4,  'g'),
(@p_sereno, @i_camom,   3,  'g'),
(@p_sereno, @i_olibano, 2,  'g'),
(@p_sereno, @i_rosa,    2,  'g'),
(@p_sereno, @i_carvao,  3,  'g'),
(@p_sereno, @i_salitre, 1,  'g'),
(@p_sereno, @i_embP,    1,  'un'),
(@p_sereno, @i_rotulo,  1,  'un');

-- Produto: Incenso Rubro
SET @p_rubro = (SELECT id FROM produtos WHERE slug = 'incenso-rubro');
INSERT INTO fichas_tecnicas (produto_id, insumo_id, quantidade, unidade) VALUES
(@p_rubro, @i_rosa,    5,  'g'),
(@p_rubro, @i_mirra,   3,  'g'),
(@p_rubro, @i_sandalo, 2,  'g'),
(@p_rubro, @i_lavanda, 2,  'g'),
(@p_rubro, @i_carvao,  3,  'g'),
(@p_rubro, @i_salitre, 1,  'g'),
(@p_rubro, @i_embP,    1,  'un'),
(@p_rubro, @i_rotulo,  1,  'un');

-- Produto: Incenso Arruda
SET @p_arruda = (SELECT id FROM produtos WHERE slug = 'incenso-arruda');
INSERT INTO fichas_tecnicas (produto_id, insumo_id, quantidade, unidade) VALUES
(@p_arruda, @i_arruda,  8,  'g'),
(@p_arruda, @i_olibano, 3,  'g'),
(@p_arruda, @i_alecrim, 3,  'g'),
(@p_arruda, @i_carvao,  4,  'g'),
(@p_arruda, @i_salitre, 1,  'g'),
(@p_arruda, @i_embP,    1,  'un'),
(@p_arruda, @i_rotulo,  1,  'un');

-- Produto: Chá Desatar
SET @p_cha = (SELECT id FROM produtos WHERE slug = 'cha-desatar');
INSERT INTO fichas_tecnicas (produto_id, insumo_id, quantidade, unidade) VALUES
(@p_cha, @i_erva,   10, 'g'),
(@p_cha, @i_camom,  10, 'g'),
(@p_cha, @i_capim,  10, 'g'),
(@p_cha, @i_alecrim, 5, 'g'),
(@p_cha, @i_embM,    1, 'un'),
(@p_cha, @i_rotulo,  1, 'un');

-- Depoimentos de demonstração
INSERT INTO depoimentos (nome, texto, avaliacao, ativo, ordem) VALUES
('Mariana S.',    'Os incensos da Iraná são incríveis! O Talismã virou parte do meu ritual matinal. Cheiro maravilhoso e energia muito positiva.',            5, 1, 1),
('Roberto L.',    'Comprei o Chá Desatar num momento difícil e fez toda a diferença. Produto de qualidade, bem embalado e com uma intenção linda.',            5, 1, 2),
('Ana Paula T.',  'O cuidado com cada produto é visível. Dá pra sentir o amor colocado em cada preparo. Recomendo demais a Iraná Natural para todo mundo!',   5, 1, 3);
