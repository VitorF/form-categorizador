CREATE TABLE `campos` (
  `id` int(11) NOT NULL,
  `nome` varchar(25) DEFAULT NULL,
  `tipodedado` varchar(75) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `campos`
--

INSERT INTO `campos` (`id`, `nome`, `tipodedado`) VALUES
(1, 'Voltagem', 'numerico'),
(2, 'Padrão de tomada', 'texto'),
(3, 'Tela', 'texto'),
(4, 'Tamanho', 'texto'),
(5, 'Material', 'texto'),
(6, 'Modelo', 'texto'),
(7, 'Sexo', 'texto'),
(8, 'Vencimento', 'data'),
(9, 'Tipo', 'texto');

-- --------------------------------------------------------

--
-- Estrutura para tabela `camposcategorias`
--

CREATE TABLE `camposcategorias` (
  `idcampo` int(11) NOT NULL,
  `idcategoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `camposcategorias`
--

INSERT INTO `camposcategorias` (`idcampo`, `idcategoria`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(8, 3),
(9, 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`) VALUES
(1, 'Celulares'),
(2, 'Roupas'),
(3, 'Alimentos');

-- --------------------------------------------------------

--
-- Estrutura para tabela `prod`
--

CREATE TABLE `prod` (
  `id` int(11) NOT NULL,
  `nome` varchar(25) DEFAULT NULL,
  `idcategoria` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtosdados`
--

CREATE TABLE `produtosdados` (
  `idproduto` int(11) NOT NULL,
  `idcampo` int(11) NOT NULL,
  `valor` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `campos`
--
ALTER TABLE `campos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `camposcategorias`
--
ALTER TABLE `camposcategorias`
  ADD PRIMARY KEY (`idcampo`,`idcategoria`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `prod`
--
ALTER TABLE `prod`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idcategoria` (`idcategoria`);

--
-- Índices de tabela `produtosdados`
--
ALTER TABLE `produtosdados`
  ADD PRIMARY KEY (`idproduto`,`idcampo`),
  ADD KEY `idcampo` (`idcampo`);

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `prod`
--
ALTER TABLE `prod`
  ADD CONSTRAINT `prod_ibfk_1` FOREIGN KEY (`idcategoria`) REFERENCES `categorias` (`id`);

--
-- Restrições para tabelas `produtosdados`
--
ALTER TABLE `produtosdados`
  ADD CONSTRAINT `produtosdados_ibfk_1` FOREIGN KEY (`idproduto`) REFERENCES `prod` (`id`),
  ADD CONSTRAINT `produtosdados_ibfk_2` FOREIGN KEY (`idcampo`) REFERENCES `campos` (`id`);