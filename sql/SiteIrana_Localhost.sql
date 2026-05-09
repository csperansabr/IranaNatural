-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           8.4.3 - MySQL Community Server - GPL
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Copiando estrutura para tabela irananatural.banners
CREATE TABLE IF NOT EXISTS `banners` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitulo` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Caminho relativo em uploads/banners/',
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL de destino ao clicar',
  `ordem` smallint NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_banners_ativo_ordem` (`ativo`,`ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Banners do carrossel na página inicial';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.carrinhos
CREATE TABLE IF NOT EXISTS `carrinhos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sessao_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_id` int unsigned DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sessao` (`sessao_id`),
  KEY `idx_cliente` (`cliente_id`),
  CONSTRAINT `carrinhos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.carrinho_itens
CREATE TABLE IF NOT EXISTS `carrinho_itens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `carrinho_id` int unsigned NOT NULL,
  `produto_id` int unsigned NOT NULL,
  `quantidade` int NOT NULL DEFAULT '1',
  `preco_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_carrinho_produto` (`carrinho_id`,`produto_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `carrinho_itens_ibfk_1` FOREIGN KEY (`carrinho_id`) REFERENCES `carrinhos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `carrinho_itens_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(110) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL amigável, único',
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Caminho relativo em uploads/categorias/',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `ordem` smallint NOT NULL DEFAULT '0' COMMENT 'Ordem de exibição no site',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_categorias_slug` (`slug`),
  KEY `idx_categorias_ativo_ordem` (`ativo`,`ordem`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorias de produtos (Banhos, Chás, Incensos, etc.)';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.categorias_insumos
CREATE TABLE IF NOT EXISTS `categorias_insumos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cat_insumos_nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Agrupamento de insumos por tipo (Resinas, Ervas, Embalagens, etc.)';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpf` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `origem` enum('online','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'online',
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_cpf` (`cpf`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.compras_insumos
CREATE TABLE IF NOT EXISTS `compras_insumos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `data_compra` date NOT NULL,
  `fornecedor` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insumo_id` int unsigned NOT NULL,
  `quantidade` decimal(12,4) NOT NULL,
  `valor_total` decimal(12,2) NOT NULL,
  `valor_unitario` decimal(12,6) NOT NULL COMMENT 'valor_total / quantidade (calculado)',
  `custo_medio_ant` decimal(12,6) NOT NULL DEFAULT '0.000000' COMMENT 'CMA do insumo antes da compra (auditoria)',
  `custo_medio_novo` decimal(12,6) NOT NULL DEFAULT '0.000000' COMMENT 'CMA do insumo após a compra (auditoria)',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_compras_insumos_data` (`data_compra`),
  KEY `idx_compras_insumos_insumo` (`insumo_id`),
  CONSTRAINT `fk_compras_insumos_insumo_id` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Histórico de compras de insumos com rastreio de custo médio';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.configuracoes
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `chave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Parâmetros configuráveis do site (nome, slogan, contato, etc.)';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.depoimentos
CREATE TABLE IF NOT EXISTS `depoimentos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `texto` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `avaliacao` tinyint NOT NULL DEFAULT '5' COMMENT '1 a 5 estrelas',
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Caminho relativo em uploads/depoimentos/',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `ordem` smallint NOT NULL DEFAULT '0',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_depoimentos_ativo_ordem` (`ativo`,`ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Depoimentos de clientes exibidos na página inicial';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.enderecos_clientes
CREATE TABLE IF NOT EXISTS `enderecos_clientes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int unsigned NOT NULL,
  `cep` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logradouro` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `complemento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cidade` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cliente_id` (`cliente_id`),
  CONSTRAINT `enderecos_clientes_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.fichas_tecnicas
CREATE TABLE IF NOT EXISTS `fichas_tecnicas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `produto_id` int unsigned NOT NULL,
  `insumo_id` int unsigned NOT NULL,
  `quantidade` decimal(12,4) NOT NULL COMMENT 'Quantidade de insumo por unidade do produto',
  `unidade` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unidade usada na ficha (pode diferir do insumo — haverá conversão)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ficha_produto_insumo` (`produto_id`,`insumo_id`),
  KEY `idx_fichas_insumo_id` (`insumo_id`),
  CONSTRAINT `fk_fichas_tecnicas_insumo_id` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`),
  CONSTRAINT `fk_fichas_tecnicas_produto_id` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Composição de insumos por produto (base para custo e produção)';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.imagens_produtos
CREATE TABLE IF NOT EXISTS `imagens_produtos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `produto_id` int unsigned NOT NULL,
  `caminho` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Caminho relativo em uploads/produtos/',
  `principal` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = imagem principal do produto',
  `ordem` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_imagens_produto_id` (`produto_id`),
  KEY `idx_imagens_principal` (`produto_id`,`principal`),
  CONSTRAINT `fk_imagens_produtos_produto_id` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Galeria de imagens dos produtos';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.import_errors
CREATE TABLE IF NOT EXISTS `import_errors` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `import_id` int unsigned NOT NULL,
  `linha` int NOT NULL COMMENT 'Número da linha do arquivo com erro',
  `campo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nome da coluna com erro',
  `valor` text COLLATE utf8mb4_unicode_ci COMMENT 'Valor que causou o erro',
  `mensagem` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_import_errors_import_id` (`import_id`),
  CONSTRAINT `fk_import_errors_import_id` FOREIGN KEY (`import_id`) REFERENCES `import_history` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Erros linha a linha registrados durante importações';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.import_history
CREATE TABLE IF NOT EXISTS `import_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entidade` enum('produtos','insumos','estoque') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de dado importado',
  `modo` enum('criar','atualizar','criar_atualizar') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'criar_atualizar',
  `arquivo_nome` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nome original do arquivo enviado',
  `total_linhas` int NOT NULL DEFAULT '0',
  `inseridos` int NOT NULL DEFAULT '0',
  `atualizados` int NOT NULL DEFAULT '0',
  `erros` int NOT NULL DEFAULT '0',
  `ignorados` int NOT NULL DEFAULT '0',
  `usuario_id` int unsigned DEFAULT NULL COMMENT 'FK para usuarios.id (sem constraint — permite registros históricos)',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_import_history_entidade` (`entidade`),
  KEY `idx_import_history_criado` (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de importações em lote (CSV / XLSX)';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.insumos
CREATE TABLE IF NOT EXISTS `insumos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tipo_id` int unsigned DEFAULT NULL COMMENT 'FK para categorias_insumos.id',
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `unidade_medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'kg, g, mg, l, ml, un, pct, cx',
  `estoque_atual` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `estoque_minimo` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `custo_medio` decimal(12,6) NOT NULL DEFAULT '0.000000' COMMENT 'Custo médio ponderado por unidade',
  `fornecedor` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `data_conferencia` date DEFAULT NULL COMMENT 'Última data de conferência física do estoque',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_insumos_tipo_id` (`tipo_id`),
  KEY `idx_insumos_ativo` (`ativo`),
  CONSTRAINT `fk_insumos_tipo_id` FOREIGN KEY (`tipo_id`) REFERENCES `categorias_insumos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Matérias-primas e insumos utilizados na produção';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.mov_insumos
CREATE TABLE IF NOT EXISTS `mov_insumos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `insumo_id` int unsigned NOT NULL,
  `tipo` enum('entrada','saida','ajuste','perda') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade` decimal(12,4) NOT NULL,
  `saldo_antes` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `saldo_apos` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `ref_tipo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Origem: compra | producao | ajuste | importacao',
  `ref_id` int unsigned DEFAULT NULL COMMENT 'ID do registro de origem',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mov_insumos_insumo_id` (`insumo_id`),
  KEY `idx_mov_insumos_tipo` (`tipo`),
  KEY `idx_mov_insumos_criado` (`criado_em`),
  CONSTRAINT `fk_mov_insumos_insumo_id` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Histórico de todas as movimentações de estoque de insumos';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.mov_produtos
CREATE TABLE IF NOT EXISTS `mov_produtos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `produto_id` int unsigned NOT NULL,
  `tipo` enum('entrada','saida','ajuste','perda') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade` int NOT NULL,
  `saldo_antes` int NOT NULL DEFAULT '0',
  `saldo_apos` int NOT NULL DEFAULT '0',
  `ref_tipo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Origem: producao | venda | ajuste',
  `ref_id` int unsigned DEFAULT NULL COMMENT 'ID do registro de origem',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mov_produtos_produto_id` (`produto_id`),
  KEY `idx_mov_produtos_tipo` (`tipo`),
  KEY `idx_mov_produtos_criado` (`criado_em`),
  CONSTRAINT `fk_mov_produtos_produto_id` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Histórico de todas as movimentações de estoque de produtos acabados';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.pagamentos
CREATE TABLE IF NOT EXISTS `pagamentos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` int unsigned NOT NULL,
  `order_nsu` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Número do pedido usado como order_nsu',
  `invoice_slug` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_nsu` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkout_url` text COLLATE utf8mb4_unicode_ci,
  `metodo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `parcelas` tinyint unsigned NOT NULL DEFAULT '1',
  `valor_original` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Subtotal sem desconto',
  `valor_desconto` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Valor do desconto PIX',
  `valor_cobrado` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Valor enviado à InfinitePay',
  `valor_pago` decimal(10,2) DEFAULT NULL COMMENT 'Confirmado no webhook',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payload_criacao` json DEFAULT NULL,
  `payload_webhook` json DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pago_em` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pag_pedido` (`pedido_id`),
  KEY `idx_pag_order_nsu` (`order_nsu`),
  KEY `idx_pag_transaction` (`transaction_nsu`),
  KEY `idx_pag_status` (`status`),
  CONSTRAINT `fk_pagamentos_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.pedidos
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_slug` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'InfinitePay invoice_slug',
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'InfinitePay transaction_nsu',
  `cliente_id` int unsigned NOT NULL,
  `status` enum('aguardando_pagamento','pagamento_expirado','pagamento_recusado','pago','separando','enviado','entregue','cancelado','pendente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aguardando_pagamento',
  `forma_pagamento` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `parcelas` tinyint unsigned NOT NULL DEFAULT '1' COMMENT 'Nº parcelas no cartão',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `frete` decimal(10,2) NOT NULL DEFAULT '0.00',
  `desconto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `desconto_pix_pct` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '% desconto PIX aplicado',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `entrega_cep` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entrega_logradouro` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entrega_numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entrega_complemento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entrega_bairro` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entrega_cidade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entrega_estado` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `idx_numero` (`numero`),
  KEY `idx_cliente` (`cliente_id`),
  KEY `idx_status` (`status`),
  KEY `idx_criado` (`criado_em`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.pedidos_historico
CREATE TABLE IF NOT EXISTS `pedidos_historico` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` int unsigned NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observacao` text COLLATE utf8mb4_unicode_ci,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pedido` (`pedido_id`),
  CONSTRAINT `pedidos_historico_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.pedido_itens
CREATE TABLE IF NOT EXISTS `pedido_itens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` int unsigned NOT NULL,
  `produto_id` int unsigned NOT NULL,
  `nome_produto` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade` int NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `pedido_itens_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedido_itens_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.producoes
CREATE TABLE IF NOT EXISTS `producoes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `produto_id` int unsigned NOT NULL,
  `quantidade_produzida` int NOT NULL,
  `quantidade_perda` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'Perda de insumo durante o processo',
  `motivo_perda` text COLLATE utf8mb4_unicode_ci,
  `data_producao` date NOT NULL,
  `responsavel` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custo_real` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Custo real do lote na data de produção',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_producoes_produto_id` (`produto_id`),
  KEY `idx_producoes_data` (`data_producao`),
  CONSTRAINT `fk_producoes_produto_id` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de lotes de produção com rastreio de custo real';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.produtos
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `categoria_id` int unsigned NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL amigável única',
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código interno do produto (opcional)',
  `descricao_curta` text COLLATE utf8mb4_unicode_ci COMMENT 'Exibida em cards e listagens',
  `descricao_completa` text COLLATE utf8mb4_unicode_ci COMMENT 'Exibida na página do produto',
  `composicao` text COLLATE utf8mb4_unicode_ci COMMENT 'Lista de ingredientes/componentes',
  `modo_uso` text COLLATE utf8mb4_unicode_ci,
  `cuidados` text COLLATE utf8mb4_unicode_ci,
  `seo_titulo` varchar(70) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Title tag para SEO (migration v1.1)',
  `seo_descricao` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Meta description para SEO (migration v1.1)',
  `tags` text COLLATE utf8mb4_unicode_ci COMMENT 'Palavras-chave separadas por vírgula (migration v1.1)',
  `preco_venda` decimal(10,2) NOT NULL DEFAULT '0.00',
  `margem_desejada` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Margem de lucro desejada em %',
  `custo_calculado` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Custo calculado via ficha técnica',
  `lucro_calculado` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'preco_venda - custo_calculado',
  `margem_real` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Margem real em %',
  `estoque_atual` int NOT NULL DEFAULT '0',
  `estoque_minimo` int NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_produtos_slug` (`slug`),
  UNIQUE KEY `uk_produtos_sku` (`sku`),
  KEY `idx_produtos_categoria` (`categoria_id`),
  KEY `idx_produtos_ativo` (`ativo`),
  KEY `idx_produtos_ativo_categoria` (`ativo`,`categoria_id`),
  CONSTRAINT `fk_produtos_categoria_id` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Produtos acabados disponíveis para venda no site';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.tokens_senha
CREATE TABLE IF NOT EXISTS `tokens_senha` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int unsigned NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expira_em` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT '0',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `cliente_id` (`cliente_id`),
  KEY `idx_token` (`token`),
  CONSTRAINT `tokens_senha_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hash bcrypt gerado pelo PHP',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuarios_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuários com acesso ao painel administrativo';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.vendas
CREATE TABLE IF NOT EXISTS `vendas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int unsigned DEFAULT NULL,
  `cliente_nome` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_venda` date NOT NULL,
  `forma_pagamento` enum('pix','dinheiro','debito','credito','transferencia','outro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pix',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `desconto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `valor_final` decimal(12,2) NOT NULL DEFAULT '0.00',
  `lucro_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vendas_data` (`data_venda`),
  KEY `fk_vendas_cliente` (`cliente_id`),
  CONSTRAINT `fk_vendas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de vendas (cabeçalho)';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.vendas_itens
CREATE TABLE IF NOT EXISTS `vendas_itens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `venda_id` int unsigned NOT NULL,
  `produto_id` int unsigned NOT NULL,
  `quantidade` int NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL COMMENT 'Preço no momento da venda',
  `custo_unitario` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Custo calculado no momento da venda',
  `subtotal` decimal(12,2) NOT NULL,
  `lucro` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `idx_vendas_itens_venda_id` (`venda_id`),
  KEY `idx_vendas_itens_produto_id` (`produto_id`),
  CONSTRAINT `fk_vendas_itens_produto_id` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`),
  CONSTRAINT `fk_vendas_itens_venda_id` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Itens individuais de cada venda';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela irananatural.webhook_logs
CREATE TABLE IF NOT EXISTS `webhook_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'infinitepay',
  `order_nsu` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pedido_id` int unsigned DEFAULT NULL,
  `transaction_nsu` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `raw_body` mediumtext COLLATE utf8mb4_unicode_ci,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `receipt_url` text COLLATE utf8mb4_unicode_ci,
  `capture_method` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installments` tinyint unsigned DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `processado` tinyint(1) NOT NULL DEFAULT '0',
  `erro` text COLLATE utf8mb4_unicode_ci,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_wh_order_nsu` (`order_nsu`),
  KEY `idx_wh_transaction` (`transaction_nsu`),
  KEY `idx_wh_criado` (`criado_em`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
