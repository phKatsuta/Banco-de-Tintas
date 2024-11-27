-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 27/11/2024 às 14:02
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `banco_de_tintas`
--

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `mesclar_tintas` (IN `id_tinta1` INT, IN `id_tinta2` INT, OUT `nova_tinta_id` INT)   BEGIN
	DECLARE nova_quantidade DECIMAL(5,2); -- Soma das duas quantidades
	DECLARE nova_data_validade DATE; -- Considerar a menor data das duas tintas
	DECLARE historico_mistura VARCHAR(200);

	-- Verifica se as tintas existem e não estão marcadas como excluídas
	IF NOT EXISTS (SELECT * FROM Tintas WHERE id_tintas = id_tinta1 AND excluido = 0) OR
	   NOT EXISTS (SELECT * FROM Tintas WHERE id_tintas = id_tinta2 AND excluido = 0) THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Uma ou ambas as tintas não existem ou estão marcadas como excluídas.';
	END IF;

	 -- Calcula a nova quantidade
	SELECT
		t1.quantidade_tintas_disponivel + t2.quantidade_tintas_disponivel INTO nova_quantidade
	FROM Tintas t1, Tintas t2
	WHERE t1.id_tintas = id_tinta1 AND t2.id_tintas = id_tinta2;

	-- Calcula a menor data de validade
	SELECT LEAST(t1.data_validade_tintas, t2.data_validade_tintas)
	INTO nova_data_validade
	FROM Tintas t1
	JOIN Tintas t2 ON t1.id_tintas = id_tinta1 AND t2.id_tintas = id_tinta2;

	-- Verifica se a nova quantidade é maior que zero
	IF nova_quantidade <= 0 THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A quantidade da nova tinta deve ser maior que zero.';
	END IF;

	-- Constrói o histórico da mistura
	SET historico_mistura = CONCAT(
		'Tinta 1\n',
		(SELECT CONCAT('nome: ', nome_tintas, ', quantidade: ', quantidade_tintas_disponivel) FROM Tintas WHERE id_tintas = id_tinta1),
		'\nTinta 2\n',
		(SELECT CONCAT('nome: ', nome_tintas, ', quantidade: ', quantidade_tintas_disponivel) FROM Tintas WHERE id_tintas = id_tinta2),
		'\nData da Mistura: ', NOW()
	);

	-- Insere o novo registro com o nome fornecido pelo usuário e o histórico da mistura
	INSERT INTO Tintas (nome_tintas, quantidade_tintas_disponivel, data_validade_tintas, mistura, histórico)
	VALUES (
		nome_novo_tinta,
		nova_quantidade,
		nova_data_validade,
		1,
		historico_mistura
	);

	SET nova_tinta_id = LAST_INSERT_ID();
	-- Marca as tintas originais como excluídas
	UPDATE Tintas SET excluido = 1 WHERE id_tintas IN (id_tinta1, id_tinta2);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `procurar_tinta` (IN `nome_buscado` VARCHAR(50))   BEGIN
  SELECT * FROM Tintas
  WHERE nome_tintas LIKE CONCAT('%', nome_buscado, '%');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_enviar_notificacao_aprovacao` (IN `id_solicitacao` INT)   BEGIN
  DECLARE beneficiario_email VARCHAR(100);

  SELECT b.email INTO beneficiario_email
  FROM Analise a
  INNER JOIN Solicitacao s ON a.id_solicitacao = s.id_solicitacao
  INNER JOIN Beneficiario b ON s.id_beneficiario = b.id_beneficiario
  WHERE a.id_solicitacao = id_solicitacao;

  -- Construir a mensagem de notificação
  SET @mensagem = CONCAT('Sua solicitação de número ', id_solicitacao, ' foi aprovada.');
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `acessos`
--

CREATE TABLE `acessos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_acesso` enum('Login','Exclusão') NOT NULL,
  `sucesso` tinyint(1) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `analise`
--

CREATE TABLE `analise` (
  `id_analise` int(11) NOT NULL,
  `id_gestor` int(11) DEFAULT NULL,
  `id_solicitacao` int(11) DEFAULT NULL,
  `status_solicitacao` enum('Em analise','Aprovado','Parcialmente aprovado','Negado') NOT NULL DEFAULT 'Em analise',
  `data_analise` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `justificativa` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Acionadores `analise`
--
DELIMITER $$
CREATE TRIGGER `tr_notificar_aprovacao_solicitacao` AFTER UPDATE ON `analise` FOR EACH ROW BEGIN
  IF NEW.status_solicitacao = 'Aprovado' AND OLD.status_solicitacao != 'Aprovado' THEN
	CALL sp_enviar_notificacao_aprovacao(NEW.id_solicitacao);
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `doacao`
--

CREATE TABLE `doacao` (
  `id_doacao` int(11) NOT NULL,
  `id_doador` int(11) DEFAULT NULL,
  `id_monitor` int(11) DEFAULT NULL,
  `data_doacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `local_doado` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `doacao_tintas`
--

CREATE TABLE `doacao_tintas` (
  `id_doacao` int(11) NOT NULL,
  `id_tintas` int(11) NOT NULL,
  `quantidade_tintas_doada` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `entrega`
--

CREATE TABLE `entrega` (
  `id_entrega` int(11) NOT NULL,
  `id_analise` int(11) DEFAULT NULL,
  `id_monitor` int(11) DEFAULT NULL,
  `dia_semana_entrega` int(11) DEFAULT NULL,
  `horario_entrega` int(11) DEFAULT NULL,
  `status_entrega` enum('Agendado','Concluído','Cancelado') DEFAULT 'Agendado',
  `local_entrega` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `entrega_tintas`
--

CREATE TABLE `entrega_tintas` (
  `id_entrega` int(11) NOT NULL,
  `id_tintas` int(11) NOT NULL,
  `quantidade` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `gestor`
--

CREATE TABLE `gestor` (
  `id_gestor` int(11) NOT NULL,
  `id_monitor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `monitor`
--

CREATE TABLE `monitor` (
  `id_monitor` int(11) NOT NULL,
  `registro` varchar(20) NOT NULL,
  `curso` varchar(100) DEFAULT NULL,
  `monitor_dia` int(5) DEFAULT NULL,
  `monitor_periodo` varchar(100) DEFAULT NULL,
  `eh_gestor` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `organizacao`
--

CREATE TABLE `organizacao` (
  `id_organizacao` int(11) NOT NULL,
  `tipo_organizacao` varchar(100) DEFAULT NULL,
  `area_atuacao` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacao`
--

CREATE TABLE `solicitacao` (
  `id_solicitacao` int(11) NOT NULL,
  `id_beneficiario` int(11) DEFAULT NULL,
  `data_solicitacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `justificativa` varchar(200) DEFAULT NULL,
  `excluido` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacao_tintas`
--

CREATE TABLE `solicitacao_tintas` (
  `id_solicitacao` int(11) NOT NULL,
  `id_tintas` int(11) NOT NULL,
  `quantidade` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tintas`
--

CREATE TABLE `tintas` (
  `id_tintas` int(11) NOT NULL,
  `nome_tintas` varchar(20) DEFAULT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `linha` varchar(50) DEFAULT NULL,
  `acabamento` varchar(50) DEFAULT NULL,
  `quantidade_tintas_disponivel` decimal(5,2) DEFAULT NULL,
  `data_validade_tintas` date NOT NULL,
  `mistura` tinyint(4) NOT NULL DEFAULT 0,
  `historico` varchar(200) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `excluido` tinyint(4) DEFAULT 0,
  `codigo_RGB` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario_nome` varchar(100) NOT NULL,
  `usuario_cep` varchar(8) DEFAULT NULL,
  `usuario_endereco` varchar(200) DEFAULT NULL,
  `usuario_endereco_num` varchar(20) DEFAULT NULL,
  `usuario_endereco_complemento` varchar(100) DEFAULT NULL,
  `usuario_bairro` varchar(100) DEFAULT NULL,
  `usuario_cidade` varchar(100) DEFAULT NULL,
  `usuario_estado` varchar(2) DEFAULT NULL,
  `usuario_email` varchar(100) NOT NULL,
  `senha_hash` varchar(255) DEFAULT NULL,
  `eh_empresa` tinyint(4) DEFAULT 0,
  `usuario_documento` varchar(14) DEFAULT NULL,
  `telefone` varchar(11) DEFAULT NULL,
  `ativo` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario_nome`, `usuario_cep`, `usuario_endereco`, `usuario_endereco_num`, `usuario_endereco_complemento`, `usuario_bairro`, `usuario_cidade`, `usuario_estado`, `usuario_email`, `senha_hash`, `eh_empresa`, `usuario_documento`, `telefone`, `ativo`) VALUES
(1, 'Doador Anônimo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, 0, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_tipos`
--

CREATE TABLE `usuario_tipos` (
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('Gestor','Monitor','Doador','Beneficiario') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario_tipos`
--

INSERT INTO `usuario_tipos` (`usuario_id`, `tipo`) VALUES
(1, 'Doador');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `acessos`
--
ALTER TABLE `acessos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `analise`
--
ALTER TABLE `analise`
  ADD PRIMARY KEY (`id_analise`),
  ADD KEY `id_gestor` (`id_gestor`),
  ADD KEY `id_solicitacao` (`id_solicitacao`);

--
-- Índices de tabela `doacao`
--
ALTER TABLE `doacao`
  ADD PRIMARY KEY (`id_doacao`),
  ADD KEY `id_doador` (`id_doador`),
  ADD KEY `id_monitor` (`id_monitor`),
  ADD KEY `idx_doacao_data` (`data_doacao`);

--
-- Índices de tabela `doacao_tintas`
--
ALTER TABLE `doacao_tintas`
  ADD PRIMARY KEY (`id_doacao`,`id_tintas`),
  ADD KEY `id_tintas` (`id_tintas`);

--
-- Índices de tabela `entrega`
--
ALTER TABLE `entrega`
  ADD PRIMARY KEY (`id_entrega`),
  ADD KEY `id_analise` (`id_analise`),
  ADD KEY `id_monitor` (`id_monitor`);

--
-- Índices de tabela `entrega_tintas`
--
ALTER TABLE `entrega_tintas`
  ADD PRIMARY KEY (`id_entrega`,`id_tintas`),
  ADD KEY `id_tintas` (`id_tintas`);

--
-- Índices de tabela `gestor`
--
ALTER TABLE `gestor`
  ADD PRIMARY KEY (`id_gestor`,`id_monitor`),
  ADD KEY `id_monitor` (`id_monitor`);

--
-- Índices de tabela `monitor`
--
ALTER TABLE `monitor`
  ADD PRIMARY KEY (`id_monitor`);

--
-- Índices de tabela `organizacao`
--
ALTER TABLE `organizacao`
  ADD PRIMARY KEY (`id_organizacao`);

--
-- Índices de tabela `solicitacao`
--
ALTER TABLE `solicitacao`
  ADD PRIMARY KEY (`id_solicitacao`,`data_solicitacao`),
  ADD KEY `id_beneficiario` (`id_beneficiario`);

--
-- Índices de tabela `solicitacao_tintas`
--
ALTER TABLE `solicitacao_tintas`
  ADD PRIMARY KEY (`id_solicitacao`,`id_tintas`),
  ADD KEY `id_tintas` (`id_tintas`);

--
-- Índices de tabela `tintas`
--
ALTER TABLE `tintas`
  ADD PRIMARY KEY (`id_tintas`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_email` (`usuario_email`);

--
-- Índices de tabela `usuario_tipos`
--
ALTER TABLE `usuario_tipos`
  ADD PRIMARY KEY (`usuario_id`,`tipo`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `acessos`
--
ALTER TABLE `acessos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `analise`
--
ALTER TABLE `analise`
  MODIFY `id_analise` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `doacao`
--
ALTER TABLE `doacao`
  MODIFY `id_doacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `entrega`
--
ALTER TABLE `entrega`
  MODIFY `id_entrega` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `solicitacao`
--
ALTER TABLE `solicitacao`
  MODIFY `id_solicitacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tintas`
--
ALTER TABLE `tintas`
  MODIFY `id_tintas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `acessos`
--
ALTER TABLE `acessos`
  ADD CONSTRAINT `acessos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `analise`
--
ALTER TABLE `analise`
  ADD CONSTRAINT `analise_ibfk_1` FOREIGN KEY (`id_gestor`) REFERENCES `monitor` (`id_monitor`),
  ADD CONSTRAINT `analise_ibfk_2` FOREIGN KEY (`id_solicitacao`) REFERENCES `solicitacao` (`id_solicitacao`);

--
-- Restrições para tabelas `doacao`
--
ALTER TABLE `doacao`
  ADD CONSTRAINT `doacao_ibfk_1` FOREIGN KEY (`id_doador`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `doacao_ibfk_2` FOREIGN KEY (`id_monitor`) REFERENCES `monitor` (`id_monitor`);

--
-- Restrições para tabelas `doacao_tintas`
--
ALTER TABLE `doacao_tintas`
  ADD CONSTRAINT `doacao_tintas_ibfk_1` FOREIGN KEY (`id_doacao`) REFERENCES `doacao` (`id_doacao`),
  ADD CONSTRAINT `doacao_tintas_ibfk_2` FOREIGN KEY (`id_tintas`) REFERENCES `tintas` (`id_tintas`);

--
-- Restrições para tabelas `entrega`
--
ALTER TABLE `entrega`
  ADD CONSTRAINT `entrega_ibfk_1` FOREIGN KEY (`id_analise`) REFERENCES `analise` (`id_analise`),
  ADD CONSTRAINT `entrega_ibfk_2` FOREIGN KEY (`id_monitor`) REFERENCES `monitor` (`id_monitor`);

--
-- Restrições para tabelas `entrega_tintas`
--
ALTER TABLE `entrega_tintas`
  ADD CONSTRAINT `entrega_tintas_ibfk_1` FOREIGN KEY (`id_entrega`) REFERENCES `entrega` (`id_entrega`),
  ADD CONSTRAINT `entrega_tintas_ibfk_2` FOREIGN KEY (`id_tintas`) REFERENCES `tintas` (`id_tintas`);

--
-- Restrições para tabelas `gestor`
--
ALTER TABLE `gestor`
  ADD CONSTRAINT `gestor_ibfk_1` FOREIGN KEY (`id_gestor`) REFERENCES `monitor` (`id_monitor`),
  ADD CONSTRAINT `gestor_ibfk_2` FOREIGN KEY (`id_monitor`) REFERENCES `monitor` (`id_monitor`);

--
-- Restrições para tabelas `monitor`
--
ALTER TABLE `monitor`
  ADD CONSTRAINT `monitor_ibfk_1` FOREIGN KEY (`id_monitor`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `organizacao`
--
ALTER TABLE `organizacao`
  ADD CONSTRAINT `organizacao_ibfk_1` FOREIGN KEY (`id_organizacao`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `solicitacao`
--
ALTER TABLE `solicitacao`
  ADD CONSTRAINT `solicitacao_ibfk_1` FOREIGN KEY (`id_beneficiario`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `solicitacao_tintas`
--
ALTER TABLE `solicitacao_tintas`
  ADD CONSTRAINT `solicitacao_tintas_ibfk_1` FOREIGN KEY (`id_solicitacao`) REFERENCES `solicitacao` (`id_solicitacao`),
  ADD CONSTRAINT `solicitacao_tintas_ibfk_2` FOREIGN KEY (`id_tintas`) REFERENCES `tintas` (`id_tintas`);

--
-- Restrições para tabelas `usuario_tipos`
--
ALTER TABLE `usuario_tipos`
  ADD CONSTRAINT `usuario_tipos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
