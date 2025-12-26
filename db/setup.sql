-- ceps.parametros definition

CREATE TABLE `parametros` (
  `chave` varchar(100) NOT NULL,
  `valor` varchar(100) NOT NULL,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

insert into parametros (chave, valor) values ('QtdLmtReqAPI', '3');
insert into parametros (chave, valor) values ('URLBrasilApi', 'https://brasilapi.com.br/api/cep/v2/');

-- ceps.distancias definition

CREATE TABLE `distancias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cepOrigem` varchar(8) NOT NULL,
  `cepOrigemJson` json NOT NULL,
  `cepDestino` varchar(8) NOT NULL,
  `cepDestinoJson` json DEFAULT NULL,
  `distancia` float NOT NULL,
  `dataInclusao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- ceps.importacoes definition

CREATE TABLE `importacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomeArquivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status` enum('PENDENTE','PROCESSANDO','FINALIZADA','ERRO') DEFAULT 'PENDENTE',
  `totalItens` int DEFAULT '0',
  `itensProcessados` int DEFAULT '0',
  `dataInclusao` datetime DEFAULT CURRENT_TIMESTAMP,
  `dataFim` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ceps.importacao_itens definition

CREATE TABLE `importacao_itens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `importacaoId` int NOT NULL,
  `cepOrigem` varchar(8) NOT NULL,
  `cepDestino` varchar(8) NOT NULL,
  `status` enum('PENDENTE','PROCESSADO','ERRO') DEFAULT 'PENDENTE',
  `mensagemErro` varchar(255) DEFAULT NULL,
  `dataInclusao` datetime DEFAULT CURRENT_TIMESTAMP,
  `dataFim` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `importacao_itens_FK` (`importacaoId`),
  CONSTRAINT `importacao_itens_FK` FOREIGN KEY (`importacaoId`) REFERENCES `importacoes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=172 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;