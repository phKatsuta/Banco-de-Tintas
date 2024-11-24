-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 15/11/2024 às 21:45
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
-- Banco de dados: `banco_de_tintas_4`
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
-- Estrutura para tabela `analise`
--

CREATE TABLE `analise` (
  `id_analise` int(11) NOT NULL,
  `id_monitor` int(11) DEFAULT NULL,
  `id_solicitacao` int(11) DEFAULT NULL,
  `status_solicitacao` enum('Em analise','Aprovado','Parcialmente aprovado','Negado') NOT NULL DEFAULT 'Em analise',
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
-- Estrutura para tabela `beneficiario`
--

CREATE TABLE `beneficiario` (
  `id_beneficiario` int(11) NOT NULL,
  `nome_beneficiario` varchar(100) DEFAULT NULL,
  `endereco_beneficiario` varchar(100) DEFAULT NULL,
  `email_beneficiario` varchar(100) DEFAULT NULL,
  `senha_hash` varchar(255) DEFAULT NULL,
  `eh_empresa` tinyint(4) DEFAULT 0,
  `cnpj` char(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `beneficiario_telefones`
--

CREATE TABLE `beneficiario_telefones` (
  `id_beneficiario` int(11) NOT NULL,
  `telefone` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `doacao`
--

CREATE TABLE `doacao` (
  `id_doacao` int(11) NOT NULL,
  `id_doador` int(11) DEFAULT NULL,
  `data_doacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `local_doado` enum('Fatec','Posto de coleta') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `doacao_feedback`
--

CREATE TABLE `doacao_feedback` (
  `id_feedback` int(11) NOT NULL,
  `id_doacao` int(11) DEFAULT NULL,
  `avaliacao` int(1) DEFAULT NULL,
  `feedback` varchar(200) DEFAULT NULL,
  `sugestoes` varchar(200) DEFAULT NULL
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
-- Estrutura para tabela `doador`
--

CREATE TABLE `doador` (
  `id_doador` int(11) NOT NULL,
  `nome_doador` varchar(100) DEFAULT NULL,
  `endereco_doador` varchar(100) DEFAULT NULL,
  `email_doador` varchar(100) DEFAULT NULL,
  `senha_hash` varchar(255) DEFAULT NULL,
  `eh_empresa` tinyint(4) DEFAULT 0,
  `cnpj` char(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `doador`
--

INSERT INTO `doador` (`id_doador`, `nome_doador`, `endereco_doador`, `email_doador`, `senha_hash`, `eh_empresa`, `cnpj`) VALUES
(1, 'Anonimo', NULL, 'anonimo@anonimo', 'anonimo', 0, NULL),
(2, 'Lojas Saci', NULL, 'sacitintas@sacitintas', 'anonimo', 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `doador_telefones`
--

CREATE TABLE `doador_telefones` (
  `id_doador` int(11) NOT NULL,
  `telefone` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `entrega`
--

CREATE TABLE `entrega` (
  `id_entrega` int(11) NOT NULL,
  `id_analise` int(11) DEFAULT NULL,
  `id_solicitacao` int(11) DEFAULT NULL,
  `id_monitor` int(11) DEFAULT NULL,
  `id_beneficiario` int(11) DEFAULT NULL,
  `dia_semana_entrega` int(11) DEFAULT NULL,
  `horario_entrega` int(11) DEFAULT NULL,
  `status_entrega` enum('Agendado','Concluído','Cancelado') DEFAULT 'Agendado',
  `local_entrega` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `entrega_feedback`
--

CREATE TABLE `entrega_feedback` (
  `id_feedback` int(11) NOT NULL,
  `id_entrega` int(11) DEFAULT NULL,
  `avaliacao` int(1) DEFAULT NULL,
  `feedback` varchar(200) DEFAULT NULL,
  `sugestoes` varchar(200) DEFAULT NULL
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
-- Estrutura para tabela `monitor`
--

CREATE TABLE `monitor` (
  `id_monitor` int(11) NOT NULL,
  `registro` varchar(20) DEFAULT NULL,
  `nome_monitor` varchar(100) DEFAULT NULL,
  `curso` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `senha_hash` varchar(255) DEFAULT NULL,
  `gestor` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `monitor_telefone`
--

CREATE TABLE `monitor_telefone` (
  `id_monitor` int(11) NOT NULL,
  `telefone` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `organizacao`
--

CREATE TABLE `organizacao` (
  `cnpj` char(14) NOT NULL,
  `tipo_organizacao` varchar(100) DEFAULT NULL,
  `area_atuacao` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `recebe`
--

CREATE TABLE `recebe` (
  `id_doacao` int(11) NOT NULL,
  `id_monitor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `responsavel_monitor`
--

CREATE TABLE `responsavel_monitor` (
  `id_gestor` int(11) NOT NULL,
  `id_monitor` int(11) NOT NULL
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
  `linha` enum('Premium','Standart','Econômica') DEFAULT NULL,
  `acabamento` enum('Fosco','Acetinado','Brilhante') DEFAULT NULL,
  `quantidade_tintas_disponivel` decimal(5,2) DEFAULT NULL,
  `data_validade_tintas` date NOT NULL,
  `mistura` tinyint(4) NOT NULL DEFAULT 0,
  `historico` varchar(200) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `excluido` tinyint(4) DEFAULT 0,
  `foto` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `analise`
--
ALTER TABLE `analise`
  ADD PRIMARY KEY (`id_analise`),
  ADD KEY `id_monitor` (`id_monitor`),
  ADD KEY `id_solicitacao` (`id_solicitacao`);

--
-- Índices de tabela `beneficiario`
--
ALTER TABLE `beneficiario`
  ADD PRIMARY KEY (`id_beneficiario`),
  ADD KEY `cnpj` (`cnpj`);

--
-- Índices de tabela `beneficiario_telefones`
--
ALTER TABLE `beneficiario_telefones`
  ADD PRIMARY KEY (`id_beneficiario`,`telefone`);

--
-- Índices de tabela `doacao`
--
ALTER TABLE `doacao`
  ADD PRIMARY KEY (`id_doacao`),
  ADD KEY `id_doador` (`id_doador`);

--
-- Índices de tabela `doacao_feedback`
--
ALTER TABLE `doacao_feedback`
  ADD PRIMARY KEY (`id_feedback`),
  ADD KEY `id_doacao` (`id_doacao`);

--
-- Índices de tabela `doacao_tintas`
--
ALTER TABLE `doacao_tintas`
  ADD PRIMARY KEY (`id_doacao`,`id_tintas`),
  ADD KEY `id_tintas` (`id_tintas`);

--
-- Índices de tabela `doador`
--
ALTER TABLE `doador`
  ADD PRIMARY KEY (`id_doador`),
  ADD KEY `cnpj` (`cnpj`);

--
-- Índices de tabela `doador_telefones`
--
ALTER TABLE `doador_telefones`
  ADD PRIMARY KEY (`id_doador`,`telefone`);

--
-- Índices de tabela `entrega`
--
ALTER TABLE `entrega`
  ADD PRIMARY KEY (`id_entrega`),
  ADD KEY `id_analise` (`id_analise`),
  ADD KEY `id_solicitacao` (`id_solicitacao`),
  ADD KEY `id_monitor` (`id_monitor`),
  ADD KEY `id_beneficiario` (`id_beneficiario`);

--
-- Índices de tabela `entrega_feedback`
--
ALTER TABLE `entrega_feedback`
  ADD PRIMARY KEY (`id_feedback`),
  ADD KEY `id_entrega` (`id_entrega`);

--
-- Índices de tabela `entrega_tintas`
--
ALTER TABLE `entrega_tintas`
  ADD PRIMARY KEY (`id_entrega`,`id_tintas`),
  ADD KEY `id_tintas` (`id_tintas`);

--
-- Índices de tabela `monitor`
--
ALTER TABLE `monitor`
  ADD PRIMARY KEY (`id_monitor`);

--
-- Índices de tabela `monitor_telefone`
--
ALTER TABLE `monitor_telefone`
  ADD PRIMARY KEY (`id_monitor`,`telefone`);

--
-- Índices de tabela `organizacao`
--
ALTER TABLE `organizacao`
  ADD PRIMARY KEY (`cnpj`);

--
-- Índices de tabela `recebe`
--
ALTER TABLE `recebe`
  ADD PRIMARY KEY (`id_doacao`,`id_monitor`),
  ADD KEY `id_monitor` (`id_monitor`);

--
-- Índices de tabela `responsavel_monitor`
--
ALTER TABLE `responsavel_monitor`
  ADD PRIMARY KEY (`id_gestor`,`id_monitor`),
  ADD KEY `id_monitor` (`id_monitor`);

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
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `analise`
--
ALTER TABLE `analise`
  MODIFY `id_analise` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `beneficiario`
--
ALTER TABLE `beneficiario`
  MODIFY `id_beneficiario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `doacao`
--
ALTER TABLE `doacao`
  MODIFY `id_doacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `doacao_feedback`
--
ALTER TABLE `doacao_feedback`
  MODIFY `id_feedback` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `doador`
--
ALTER TABLE `doador`
  MODIFY `id_doador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `entrega`
--
ALTER TABLE `entrega`
  MODIFY `id_entrega` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `entrega_feedback`
--
ALTER TABLE `entrega_feedback`
  MODIFY `id_feedback` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `monitor`
--
ALTER TABLE `monitor`
  MODIFY `id_monitor` int(11) NOT NULL AUTO_INCREMENT;

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
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `analise`
--
ALTER TABLE `analise`
  ADD CONSTRAINT `analise_ibfk_1` FOREIGN KEY (`id_monitor`) REFERENCES `monitor` (`id_monitor`),
  ADD CONSTRAINT `analise_ibfk_2` FOREIGN KEY (`id_solicitacao`) REFERENCES `solicitacao` (`id_solicitacao`);

--
-- Restrições para tabelas `beneficiario`
--
ALTER TABLE `beneficiario`
  ADD CONSTRAINT `beneficiario_ibfk_1` FOREIGN KEY (`cnpj`) REFERENCES `organizacao` (`cnpj`);

--
-- Restrições para tabelas `beneficiario_telefones`
--
ALTER TABLE `beneficiario_telefones`
  ADD CONSTRAINT `beneficiario_telefones_ibfk_1` FOREIGN KEY (`id_beneficiario`) REFERENCES `beneficiario` (`id_beneficiario`);

--
-- Restrições para tabelas `doacao`
--
ALTER TABLE `doacao`
  ADD CONSTRAINT `doacao_ibfk_1` FOREIGN KEY (`id_doador`) REFERENCES `doador` (`id_doador`);

--
-- Restrições para tabelas `doacao_feedback`
--
ALTER TABLE `doacao_feedback`
  ADD CONSTRAINT `doacao_feedback_ibfk_1` FOREIGN KEY (`id_doacao`) REFERENCES `doacao` (`id_doacao`);

--
-- Restrições para tabelas `doacao_tintas`
--
ALTER TABLE `doacao_tintas`
  ADD CONSTRAINT `doacao_tintas_ibfk_1` FOREIGN KEY (`id_doacao`) REFERENCES `doacao` (`id_doacao`),
  ADD CONSTRAINT `doacao_tintas_ibfk_2` FOREIGN KEY (`id_tintas`) REFERENCES `tintas` (`id_tintas`);

--
-- Restrições para tabelas `doador`
--
ALTER TABLE `doador`
  ADD CONSTRAINT `doador_ibfk_1` FOREIGN KEY (`cnpj`) REFERENCES `organizacao` (`cnpj`);

--
-- Restrições para tabelas `doador_telefones`
--
ALTER TABLE `doador_telefones`
  ADD CONSTRAINT `doador_telefones_ibfk_1` FOREIGN KEY (`id_doador`) REFERENCES `doador` (`id_doador`);

--
-- Restrições para tabelas `entrega`
--
ALTER TABLE `entrega`
  ADD CONSTRAINT `entrega_ibfk_1` FOREIGN KEY (`id_analise`) REFERENCES `analise` (`id_analise`),
  ADD CONSTRAINT `entrega_ibfk_2` FOREIGN KEY (`id_solicitacao`) REFERENCES `solicitacao` (`id_solicitacao`),
  ADD CONSTRAINT `entrega_ibfk_3` FOREIGN KEY (`id_monitor`) REFERENCES `monitor` (`id_monitor`),
  ADD CONSTRAINT `entrega_ibfk_4` FOREIGN KEY (`id_beneficiario`) REFERENCES `beneficiario` (`id_beneficiario`);

--
-- Restrições para tabelas `entrega_feedback`
--
ALTER TABLE `entrega_feedback`
  ADD CONSTRAINT `entrega_feedback_ibfk_1` FOREIGN KEY (`id_entrega`) REFERENCES `entrega` (`id_entrega`);

--
-- Restrições para tabelas `entrega_tintas`
--
ALTER TABLE `entrega_tintas`
  ADD CONSTRAINT `entrega_tintas_ibfk_1` FOREIGN KEY (`id_entrega`) REFERENCES `entrega` (`id_entrega`),
  ADD CONSTRAINT `entrega_tintas_ibfk_2` FOREIGN KEY (`id_tintas`) REFERENCES `tintas` (`id_tintas`);

--
-- Restrições para tabelas `monitor_telefone`
--
ALTER TABLE `monitor_telefone`
  ADD CONSTRAINT `monitor_telefone_ibfk_1` FOREIGN KEY (`id_monitor`) REFERENCES `monitor` (`id_monitor`);

--
-- Restrições para tabelas `recebe`
--
ALTER TABLE `recebe`
  ADD CONSTRAINT `recebe_ibfk_1` FOREIGN KEY (`id_doacao`) REFERENCES `doacao` (`id_doacao`),
  ADD CONSTRAINT `recebe_ibfk_2` FOREIGN KEY (`id_monitor`) REFERENCES `monitor` (`id_monitor`);

--
-- Restrições para tabelas `responsavel_monitor`
--
ALTER TABLE `responsavel_monitor`
  ADD CONSTRAINT `responsavel_monitor_ibfk_1` FOREIGN KEY (`id_gestor`) REFERENCES `monitor` (`id_monitor`),
  ADD CONSTRAINT `responsavel_monitor_ibfk_2` FOREIGN KEY (`id_monitor`) REFERENCES `monitor` (`id_monitor`);

--
-- Restrições para tabelas `solicitacao`
--
ALTER TABLE `solicitacao`
  ADD CONSTRAINT `solicitacao_ibfk_1` FOREIGN KEY (`id_beneficiario`) REFERENCES `beneficiario` (`id_beneficiario`);

--
-- Restrições para tabelas `solicitacao_tintas`
--
ALTER TABLE `solicitacao_tintas`
  ADD CONSTRAINT `solicitacao_tintas_ibfk_1` FOREIGN KEY (`id_solicitacao`) REFERENCES `solicitacao` (`id_solicitacao`),
  ADD CONSTRAINT `solicitacao_tintas_ibfk_2` FOREIGN KEY (`id_tintas`) REFERENCES `tintas` (`id_tintas`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
