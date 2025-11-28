-- database/update_add_templates.sql
-- Atualização para adicionar suporte a templates de cartões bancários

USE pap;

-- Adicionar colunas à tabela cards
ALTER TABLE cards 
ADD COLUMN template VARCHAR(50) DEFAULT 'custom' AFTER color,
ADD COLUMN bank VARCHAR(50) DEFAULT NULL AFTER template;

-- Criar índice para melhorar performance
CREATE INDEX idx_cards_bank ON cards(bank);
CREATE INDEX idx_cards_template ON cards(template);

-- Atualizar cartões existentes para usar template "custom"
UPDATE cards SET template = 'custom', bank = 'Custom' WHERE template IS NULL;

-- Comentários nas colunas
ALTER TABLE cards 
MODIFY COLUMN template VARCHAR(50) DEFAULT 'custom' COMMENT 'Template/design do cartão (ex: caixa_gold, millennium_classic)',
MODIFY COLUMN bank VARCHAR(50) DEFAULT NULL COMMENT 'Nome do banco (ex: CGD, BCP, Santander)';

-- Verificar estrutura atualizada
DESCRIBE cards;

-- Fim da migração
