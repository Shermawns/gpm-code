-- 1. Tabela de Usuários (para seu index.php)
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL -- Lembre-se de usar password_hash()
);

-- 2. Tabela de Equipes (para o dashboard e formulário)
CREATE TABLE equipes (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cor VARCHAR(20) DEFAULT '#4A90E2'
);

-- 3. Tabela de Serviços (para o add_service.php)
CREATE TABLE servicos (
    id SERIAL PRIMARY KEY,
    equipe_id INT NOT NULL,
    quantidade INT NOT NULL,
    data_registro DATE NOT NULL,
    observacao TEXT,
    -- Cria a "ligação" entre servicos e equipes
    FOREIGN KEY (equipe_id) REFERENCES equipes(id)
);

-- 4. A "View" que estava faltando (vw_produtividade)
-- Esta é a "tabela virtual" que seu dashboard.php consulta
CREATE OR REPLACE VIEW vw_produtividade AS
SELECT
    e.id,
    e.nome,
    e.cor,
    -- COALESCE para mostrar 0 em vez de NULL se a equipe não tiver serviços
    COALESCE(SUM(s.quantidade), 0) AS total_servicos,
    COUNT(s.id) AS registros
FROM
    equipes e
LEFT JOIN
    servicos s ON e.id = s.equipe_id
GROUP BY
    e.id, e.nome, e.cor
ORDER BY
    e.nome;

INSERT INTO equipes (nome, cor) VALUES
('Equipe Alpha', '#E74C3C'),
('Equipe Bravo', '#3498DB'),
('Equipe Charlie', '#2ECC71');