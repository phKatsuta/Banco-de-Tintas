-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 27/11/2024 às 13:58
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

-- --------------------------------------------------------

--
-- Estrutura para tabela `analise`
--

CREATE TABLE `analise` (
  `id_analise` int(11) NOT NULL,
  `id_monitor` int(11) DEFAULT NULL,
  `id_solicitacao` int(11) DEFAULT NULL,
  `status_solicitacao` enum('Em analise','Aprovado','Parcialmente aprovado','Negado') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `doacao`
--

CREATE TABLE `doacao` (
  `id_doacao` int(11) NOT NULL,
  `id_doador` int(11) DEFAULT NULL,
  `id_monitor` int(11) DEFAULT NULL,
  `id_tintas` int(11) DEFAULT NULL,
  `data_doacao` date DEFAULT NULL,
  `quantidade_tintas` decimal(5,2) DEFAULT NULL
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
(1, 'Anonimo', NULL, 'anonimo@anonimo', 'anonimo', 0, NULL);

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
  `id_solicitacao` int(11) DEFAULT NULL,
  `id_analise` int(11) DEFAULT NULL,
  `id_monitor` int(11) DEFAULT NULL,
  `id_recebedor` int(11) DEFAULT NULL,
  `id_tintas` int(11) DEFAULT NULL,
  `data_agendamento` date DEFAULT NULL,
  `data_entrega` date DEFAULT NULL,
  `quantidade_tintas` decimal(5,2) DEFAULT NULL,
  `status_entrega` enum('agendada','concluida','cancelada') DEFAULT NULL
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
  `responsavel_por` int(11) DEFAULT NULL
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
-- Estrutura para tabela `recebedor`
--

CREATE TABLE `recebedor` (
  `id_recebedor` int(11) NOT NULL,
  `nome_recebedor` varchar(100) DEFAULT NULL,
  `endereco_recebedor` varchar(100) DEFAULT NULL,
  `email_recebedor` varchar(100) DEFAULT NULL,
  `senha_hash` varchar(255) DEFAULT NULL,
  `eh_empresa` tinyint(4) DEFAULT 0,
  `cnpj` char(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `recebedor_telefones`
--

CREATE TABLE `recebedor_telefones` (
  `id_recebedor` int(11) NOT NULL,
  `telefone` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacao`
--

CREATE TABLE `solicitacao` (
  `id_solicitacao` int(11) NOT NULL,
  `id_recebedor` int(11) DEFAULT NULL,
  `id_tintas` int(11) DEFAULT NULL,
  `data_agendamento` date NOT NULL DEFAULT curdate(),
  `quantidade_tintas` decimal(5,2) DEFAULT NULL,
  `justificativa` varchar(200) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `excluido` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tintas`
--

CREATE TABLE `tintas` (
  `id_tintas` int(11) NOT NULL,
  `nome_tintas` varchar(20) DEFAULT NULL,
  `quantidade_tintas` decimal(5,2) DEFAULT NULL,
  `data_validade_tintas` date NOT NULL,
  `mistura` tinyint(4) NOT NULL DEFAULT 0,
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
-- Índices de tabela `doacao`
--
ALTER TABLE `doacao`
  ADD PRIMARY KEY (`id_doacao`),
  ADD KEY `id_doador` (`id_doador`),
  ADD KEY `id_tintas` (`id_tintas`),
  ADD KEY `id_monitor` (`id_monitor`);

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
  ADD KEY `id_solicitacao` (`id_solicitacao`,`data_agendamento`),
  ADD KEY `id_monitor` (`id_monitor`),
  ADD KEY `id_recebedor` (`id_recebedor`),
  ADD KEY `id_tintas` (`id_tintas`),
  ADD KEY `idx_entrega_data_agendamento` (`data_agendamento`),
  ADD KEY `idx_entrega_data_entrega` (`data_entrega`),
  ADD KEY `idx_entrega_status` (`status_entrega`);

--
-- Índices de tabela `monitor`
--
ALTER TABLE `monitor`
  ADD PRIMARY KEY (`id_monitor`),
  ADD KEY `responsavel_por` (`responsavel_por`);

--
-- Índices de tabela `monitor_telefone`
--
ALTER TABLE `monitor_telefone`
  ADD PRIMARY KEY (`id_monitor`,`telefone`);

--
-- Índices de tabela `organizacao`
--
ALTER TABLE `organizacao`
  ADD PRIMARY KEY (`cnpj`),
  ADD KEY `idx_organizacao_tipo` (`tipo_organizacao`),
  ADD KEY `idx_organizacao_area` (`area_atuacao`);

--
-- Índices de tabela `recebedor`
--
ALTER TABLE `recebedor`
  ADD PRIMARY KEY (`id_recebedor`),
  ADD KEY `cnpj` (`cnpj`);

--
-- Índices de tabela `recebedor_telefones`
--
ALTER TABLE `recebedor_telefones`
  ADD PRIMARY KEY (`id_recebedor`,`telefone`);

--
-- Índices de tabela `solicitacao`
--
ALTER TABLE `solicitacao`
  ADD PRIMARY KEY (`id_solicitacao`,`data_agendamento`),
  ADD KEY `id_recebedor` (`id_recebedor`),
  ADD KEY `id_tintas` (`id_tintas`);

--
-- Índices de tabela `tintas`
--
ALTER TABLE `tintas`
  ADD PRIMARY KEY (`id_tintas`),
  ADD KEY `idx_tintas_data_validade` (`data_validade_tintas`),
  ADD KEY `idx_tintas_nome` (`nome_tintas`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

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
-- AUTO_INCREMENT de tabela `doador`
--
ALTER TABLE `doador`
  MODIFY `id_doador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `entrega`
--
ALTER TABLE `entrega`
  MODIFY `id_entrega` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `monitor`
--
ALTER TABLE `monitor`
  MODIFY `id_monitor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recebedor`
--
ALTER TABLE `recebedor`
  MODIFY `id_recebedor` int(11) NOT NULL AUTO_INCREMENT;

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
-- Restrições para tabelas `doacao`
--
ALTER TABLE `doacao`
  ADD CONSTRAINT `doacao_ibfk_1` FOREIGN KEY (`id_doador`) REFERENCES `doador` (`id_doador`),
  ADD CONSTRAINT `doacao_ibfk_2` FOREIGN KEY (`id_tintas`) REFERENCES `tintas` (`id_tintas`),
  ADD CONSTRAINT `doacao_ibfk_3` FOREIGN KEY (`id_monitor`) REFERENCES `monitor` (`id_monitor`);

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
  ADD CONSTRAINT `entrega_ibfk_1` FOREIGN KEY (`id_solicitacao`,`data_agendamento`) REFERENCES `solicitacao` (`id_solicitacao`, `data_agendamento`),
  ADD CONSTRAINT `entrega_ibfk_2` FOREIGN KEY (`id_monitor`) REFERENCES `monitor` (`id_monitor`),
  ADD CONSTRAINT `entrega_ibfk_3` FOREIGN KEY (`id_recebedor`) REFERENCES `recebedor` (`id_recebedor`),
  ADD CONSTRAINT `entrega_ibfk_4` FOREIGN KEY (`id_tintas`) REFERENCES `tintas` (`id_tintas`);

--
-- Restrições para tabelas `monitor`
--
ALTER TABLE `monitor`
  ADD CONSTRAINT `monitor_ibfk_1` FOREIGN KEY (`responsavel_por`) REFERENCES `monitor` (`id_monitor`);

--
-- Restrições para tabelas `monitor_telefone`
--
ALTER TABLE `monitor_telefone`
  ADD CONSTRAINT `monitor_telefone_ibfk_1` FOREIGN KEY (`id_monitor`) REFERENCES `monitor` (`id_monitor`);

--
-- Restrições para tabelas `recebedor`
--
ALTER TABLE `recebedor`
  ADD CONSTRAINT `recebedor_ibfk_1` FOREIGN KEY (`cnpj`) REFERENCES `organizacao` (`cnpj`);

--
-- Restrições para tabelas `recebedor_telefones`
--
ALTER TABLE `recebedor_telefones`
  ADD CONSTRAINT `recebedor_telefones_ibfk_1` FOREIGN KEY (`id_recebedor`) REFERENCES `recebedor` (`id_recebedor`);

--
-- Restrições para tabelas `solicitacao`
--
ALTER TABLE `solicitacao`
  ADD CONSTRAINT `solicitacao_ibfk_1` FOREIGN KEY (`id_recebedor`) REFERENCES `recebedor` (`id_recebedor`),
  ADD CONSTRAINT `solicitacao_ibfk_2` FOREIGN KEY (`id_tintas`) REFERENCES `tintas` (`id_tintas`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
